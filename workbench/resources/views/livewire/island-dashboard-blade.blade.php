{{-- Same islands as the antlers view, native @island/@endisland/@placeholder --}}
{{-- directives — the addon's {{ livewire:island }} tag piggybacks these. --}}
<div class="card">
    <p>outside count: {{ $count }}</p>

    @island('stats')
        <p>inside island: {{ $count }}</p>
    @endisland

    <button wire:click="increment">Increment (outside)</button>

    @island('always-stats', always: true)
        <p>always re-renders: {{ $count }}</p>
    @endisland

    @island('lazy-stats', lazy: true)
        @placeholder
            <p>island loading…</p>
        @endplaceholder
        <p>lazily mounted: {{ $count }}</p>
    @endisland

    {{-- No looped island here: Blade's @island `with:` expression is re-evaluated
         inside the island's own isolated compiled file, which has no access to the
         enclosing @foreach's loop variable — it throws "Undefined variable". The
         addon's {{ livewire:island }} tag captures the surrounding Antlers scope
         instead (persisted through the component memo), so the loop in the
         Antlers view above works and this one can't. --}}
</div>
