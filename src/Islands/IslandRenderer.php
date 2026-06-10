<?php

namespace MarcoRieser\Livewire\Islands;

use Livewire\Component;
use MarcoRieser\Livewire\Hooks\CascadeVariablesAutoloader;
use MarcoRieser\Livewire\Hooks\ComputedPropertiesAutoloader;
use Statamic\Facades\Antlers;

class IslandRenderer
{
    protected const EXCLUDED_SCOPE_VARIABLES = ['__env', 'app', '__path', '__data', 'obLevel', '__placeholder', '__runtimeWith'];

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $withSnapshot
     */
    public function render(array $scope, string $template, string $placeholder = '', array $withSnapshot = []): string
    {
        if (array_key_exists('__placeholder', $scope)) {
            if (trim($placeholder) === '') {
                return (string) $scope['__placeholder'];
            }

            $template = $placeholder;
        }

        return (string) Antlers::parse($template, $this->buildContext($scope, $withSnapshot), true)->withoutExtractions();
    }

    /**
     * Mirror the context the addon provides to full Antlers component views.
     *
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $withSnapshot
     * @return array<string, mixed>
     */
    protected function buildContext(array $scope, array $withSnapshot = []): array
    {
        $component = $scope['__livewire'] ?? null;

        $runtimeWith = $scope['__runtimeWith'] ?? [];

        $scope = array_diff_key($scope, array_flip(self::EXCLUDED_SCOPE_VARIABLES));

        return array_merge(
            $component instanceof Component ? CascadeVariablesAutoloader::cascadeVariables($component) : [],
            $scope,
            $component instanceof Component ? ComputedPropertiesAutoloader::computedProperties($component) : [],
            $withSnapshot === [] ? [] : app(WithSnapshot::class)->resurrect($withSnapshot),
            is_array($runtimeWith) ? $runtimeWith : [],
        );
    }
}
