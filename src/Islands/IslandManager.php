<?php

namespace MarcoRieser\Livewire\Islands;

use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;

use function Livewire\store;

class IslandManager
{
    public const WITH_SNAPSHOTS_STORE_KEY = 'antlersIslandsWithSnapshots';

    protected const CONTEXT_STACK_STORE_KEY = 'antlersIslandsContextStack';

    protected const OCCURRENCES_STORE_KEY = 'antlersIslandsOccurrences';

    protected const ROOT_CONTEXT = 'root';

    /**
     * @param  array<string, mixed>  $with
     */
    public function ensureIslandCacheFile(Component $component, string $name, string $content, array $with = []): string
    {
        [$template, $placeholder] = $this->extractPlaceholder($content);

        $token = $this->token($component, $name);

        $path = IslandCompiler::getCachedPathFromToken($token);

        $contents = $this->buildIslandCacheFileContents($name, $template, $placeholder, $token);

        if (! file_exists($path) || file_get_contents($path) !== $contents) {
            $this->writeIslandCacheFile($path, $contents);
        }

        if ($component->islandIsMounting() && $with !== []) {
            store($component)->push(static::WITH_SNAPSHOTS_STORE_KEY, app(WithSnapshot::class)->snapshot($with), $token);
        }

        return $token;
    }

    /**
     * Islands rendered inside another island count their occurrences within
     * that island, mirroring how core scopes them to the containing file.
     */
    public function pushContext(Component $component, string $token): void
    {
        $stack = store($component)->get(static::CONTEXT_STACK_STORE_KEY, []);

        $stack[] = $token;

        store($component)->set(static::CONTEXT_STACK_STORE_KEY, $stack);

        $this->resetOccurrences($component, $token);
    }

    public function popContext(Component $component): void
    {
        $stack = store($component)->get(static::CONTEXT_STACK_STORE_KEY, []);

        array_pop($stack);

        store($component)->set(static::CONTEXT_STACK_STORE_KEY, $stack);
    }

    /**
     * Called at the start of every render pass so re-renders count
     * occurrences from the top again.
     */
    public function resetRootOccurrences(Component $component): void
    {
        $this->resetOccurrences($component, static::ROOT_CONTEXT);
    }

    /**
     * Move the "with" snapshots captured while mounting onto the memoized
     * island entries; entries that already carry one keep it (frozen at mount).
     */
    public function persistWithSnapshots(Component $component): void
    {
        if (! ($pending = store($component)->get(static::WITH_SNAPSHOTS_STORE_KEY, []))) {
            return;
        }

        $component->setIslands(collect($component->getIslands())
            ->map(function (array $island) use ($pending) {
                if (! isset($island['with']) && isset($pending[$island['token'] ?? null])) {
                    $island['with'] = $pending[$island['token']];
                }

                return $island;
            })
            ->all());
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
     * Tokens mirror core's path+occurrence scheme: component name, containing
     * island and island name plus a render-order occurrence. Independent of
     * template contents and "with" data, they stay stable across template
     * edits (the cache file is refreshed in place) and data changes.
     */
    protected function token(Component $component, string $name): string
    {
        $context = $this->currentContext($component);

        $occurrences = store($component)->get(static::OCCURRENCES_STORE_KEY, []);

        $occurrence = $occurrences[$context][$name] = ($occurrences[$context][$name] ?? 0) + 1;

        store($component)->set(static::OCCURRENCES_STORE_KEY, $occurrences);

        return 'antlers-'.md5($component->getName().'|'.$context.'|'.$name).'-'.$occurrence;
    }

    protected function currentContext(Component $component): string
    {
        $stack = store($component)->get(static::CONTEXT_STACK_STORE_KEY, []);

        return $stack === [] ? static::ROOT_CONTEXT : end($stack);
    }

    protected function resetOccurrences(Component $component, string $context): void
    {
        $occurrences = store($component)->get(static::OCCURRENCES_STORE_KEY, []);

        unset($occurrences[$context]);

        store($component)->set(static::OCCURRENCES_STORE_KEY, $occurrences);
    }

    protected function writeIslandCacheFile(string $path, string $contents): void
    {
        File::ensureDirectoryExists(dirname($path));

        $temporaryPath = $path.'.'.bin2hex(random_bytes(8)).'.tmp';

        file_put_contents($temporaryPath, $contents);

        rename($temporaryPath, $path);

        app('livewire.compiler')->cacheManager->prepareGeneratedFileForCompilation($path);
    }

    /**
     * The cache file is a Blade-compilable PHP shim that delegates rendering
     * back to Antlers. The island sources are base64-encoded so Blade's
     * compiler never sees (and mangles) the Antlers syntax.
     */
    protected function buildIslandCacheFileContents(string $name, string $template, string $placeholder, string $token): string
    {
        $template = base64_encode($template);
        $placeholder = base64_encode($placeholder);

        return <<<PHP
<?php /** Antlers island "{$name}" extracted by marcorieser/statamic-livewire. */
echo app(\MarcoRieser\Livewire\Islands\IslandRenderer::class)->render(
    get_defined_vars(),
    base64_decode('{$template}'),
    base64_decode('{$placeholder}'),
    '{$token}',
);
PHP;
    }
}
