# Upgrade from 3 to 4

In January 2025, Jonas Siewertsen transferred the ownership of this addon to Marco Rieser. 
In comparison to version 3, all the namespaces were updated.
Besides that, there are no breaking changes.

## Upgrade steps
1. Replace `"jonassiewertsen/statamic-livewire": "^3.0"` with `"marcorieser/statamic-livewire": "^4.0"` in your `composer.json`
2. Replace the namespace `Jonassiewertsen\Livewire` with `MarcoRieser\Livewire` in your code
3. Run `composer update marcorieser/statamic-livewire`
