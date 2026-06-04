<?php

namespace MarcoRieser\Livewire\Islands;

use Illuminate\Support\Facades\File;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;

class IslandManager
{
    /**
     * @param  array<string, mixed>  $with
     */
    public function ensureIslandCacheFile(string $name, string $content, array $with = []): string
    {
        [$template, $placeholder] = $this->extractPlaceholder($content);

        $token = $this->token($name, $template, $placeholder, $with);

        $path = IslandCompiler::getCachedPathFromToken($token);

        if (! file_exists($path)) {
            $this->writeIslandCacheFile($path, $name, $template, $placeholder, $with);
        }

        return $token;
    }

    /**
     * Split the placeholder region off the island template, mirroring the
     * behavior of Blade's placeholder directives. Placeholder tags inside
     * Antlers comments or nested {{ livewire:island }} pairs are left untouched.
     *
     * @return array{0: string, 1: string}
     */
    protected function extractPlaceholder(string $content): array
    {
        if (! preg_match_all('/\{\{\s*placeholder\s*\}\}(.*?)\{\{\s*\/placeholder\s*\}\}/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
            return [$content, ''];
        }

        $protectedRegions = $this->protectedRegions($content);

        $template = $content;
        $placeholder = '';
        $removals = [];

        foreach ($matches[0] as $index => [$block, $offset]) {
            if ($this->insideRegions($offset, $protectedRegions)) {
                continue;
            }

            $placeholder .= $matches[1][$index][0];
            $removals[] = [$offset, strlen($block)];
        }

        foreach (array_reverse($removals) as [$offset, $length]) {
            $template = substr_replace($template, '', $offset, $length);
        }

        return [$template, $placeholder];
    }

    /**
     * Regions of the island content that belong to Antlers comments or nested
     * islands and should be ignored when extracting the placeholder.
     *
     * @return array<int, array{0: int, 1: int}>
     */
    protected function protectedRegions(string $content): array
    {
        $regions = [];

        preg_match_all('/\{\{#.*?#\}\}/s', $content, $comments, PREG_OFFSET_CAPTURE);

        foreach ($comments[0] as [$comment, $offset]) {
            $regions[] = [$offset, $offset + strlen($comment)];
        }

        preg_match_all('/\{\{\s*\/?\s*(?:livewire|lw|wire):island\b.*?\}\}/s', $content, $islandTags, PREG_OFFSET_CAPTURE);

        $depth = 0;
        $regionStart = null;

        foreach ($islandTags[0] as [$tag, $offset]) {
            if ($this->insideRegions($offset, $regions)) {
                continue;
            }

            if (! preg_match('/^\{\{\s*\//', $tag)) {
                if ($depth === 0) {
                    $regionStart = $offset;
                }

                $depth++;

                continue;
            }

            if (--$depth === 0 && $regionStart !== null) {
                $regions[] = [$regionStart, $offset + strlen($tag)];
                $regionStart = null;
            }
        }

        if ($depth > 0 && $regionStart !== null) {
            $regions[] = [$regionStart, strlen($content)];
        }

        return $regions;
    }

    /**
     * @param  array<int, array{0: int, 1: int}>  $regions
     */
    protected function insideRegions(int $offset, array $regions): bool
    {
        foreach ($regions as [$start, $end]) {
            if ($offset >= $start && $offset < $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tokens are content-addressed so they stay stable across requests and
     * resolve to the same cache file as long as the island is unchanged.
     *
     * @param  array<string, mixed>  $with
     */
    protected function token(string $name, string $template, string $placeholder, array $with): string
    {
        return 'antlers-'.md5($name.'|'.$template.'|'.$placeholder.'|'.json_encode($with));
    }

    /**
     * @param  array<string, mixed>  $with
     */
    protected function writeIslandCacheFile(string $path, string $name, string $template, string $placeholder, array $with): void
    {
        File::ensureDirectoryExists(dirname($path));

        $temporaryPath = $path.'.'.bin2hex(random_bytes(8)).'.tmp';

        file_put_contents($temporaryPath, $this->buildIslandCacheFileContents($name, $template, $placeholder, $with));

        rename($temporaryPath, $path);

        app('livewire.compiler')->cacheManager->prepareGeneratedFileForCompilation($path);
    }

    /**
     * The cache file is a Blade-compilable PHP shim that delegates rendering
     * back to Antlers. The island sources are base64-encoded so Blade's
     * compiler never sees (and mangles) the Antlers syntax.
     *
     * @param  array<string, mixed>  $with
     */
    protected function buildIslandCacheFileContents(string $name, string $template, string $placeholder, array $with): string
    {
        $template = base64_encode($template);
        $placeholder = base64_encode($placeholder);
        $with = base64_encode(json_encode($with));

        return <<<PHP
<?php /** Antlers island "{$name}" extracted by marcorieser/statamic-livewire. */
echo app(\MarcoRieser\Livewire\Islands\IslandRenderer::class)->render(
    get_defined_vars(),
    base64_decode('{$template}'),
    base64_decode('{$placeholder}'),
    json_decode(base64_decode('{$with}'), true),
);
PHP;
    }
}
