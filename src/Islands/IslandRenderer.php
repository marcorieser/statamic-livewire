<?php

namespace MarcoRieser\Livewire\Islands;

use Livewire\Component;
use MarcoRieser\Livewire\Hooks\CascadeVariablesAutoloader;
use MarcoRieser\Livewire\Hooks\ComputedPropertiesAutoloader;
use Statamic\Facades\Antlers;

use function Livewire\store;

class IslandRenderer
{
    protected const EXCLUDED_SCOPE_VARIABLES = ['__env', 'app', '__path', '__data', 'obLevel', '__placeholder', '__runtimeWith'];

    /**
     * @param  array<string, mixed>  $scope
     */
    public function render(array $scope, string $template, string $placeholder = '', string $token = ''): string
    {
        if (array_key_exists('__placeholder', $scope)) {
            if (trim($placeholder) === '') {
                return (string) $scope['__placeholder'];
            }

            $template = $placeholder;
        }

        $component = $scope['__livewire'] ?? null;

        $manager = $component instanceof Component && $token !== '' ? app(IslandManager::class) : null;

        $manager?->pushContext($component, $token);

        try {
            return (string) Antlers::parse($template, $this->buildContext($scope, $token), true)->withoutExtractions();
        } finally {
            $manager?->popContext($component);
        }
    }

    /**
     * Mirror the context the addon provides to full Antlers component views.
     *
     * @param  array<string, mixed>  $scope
     * @return array<string, mixed>
     */
    protected function buildContext(array $scope, string $token = ''): array
    {
        $component = $scope['__livewire'] ?? null;

        $runtimeWith = $scope['__runtimeWith'] ?? [];

        $scope = array_diff_key($scope, array_flip(self::EXCLUDED_SCOPE_VARIABLES));

        return array_merge(
            $component instanceof Component ? CascadeVariablesAutoloader::cascadeVariables($component) : [],
            $scope,
            $component instanceof Component ? ComputedPropertiesAutoloader::computedProperties($component) : [],
            $component instanceof Component ? $this->withVariables($component, $token) : [],
            is_array($runtimeWith) ? $runtimeWith : [],
        );
    }

    /**
     * The memo entry is not filled yet while the island is first mounting,
     * so fall back to the component's request-scoped store.
     *
     * @return array<string, mixed>
     */
    protected function withVariables(Component $component, string $token): array
    {
        if ($token === '') {
            return [];
        }

        $island = collect($component->getIslands())
            ->first(fn (array $island) => ($island['token'] ?? null) === $token);

        $snapshot = $island['with']
            ?? store($component)->get(IslandManager::WITH_SNAPSHOTS_STORE_KEY, [])[$token]
            ?? [];

        return $snapshot === [] ? [] : app(WithSnapshot::class)->resurrect($snapshot);
    }
}
