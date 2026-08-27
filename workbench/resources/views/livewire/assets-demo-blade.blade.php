{{-- Native Livewire @assets/@script directives — {{ livewire:assets }}/{{ livewire:script }} --}}
{{-- just proxy to these for Antlers, so Blade behaves identically. --}}
<div class="card">
    <span>Custom assets/script pair (Blade) — mounted twice on this page but only injected once.</span>
    @assets
        <script>window.customAssetLoadedBlade = true;</script>
    @endassets
    @script
        console.log('AssetsDemoBlade script executed once per component instance');
    @endscript
</div>
