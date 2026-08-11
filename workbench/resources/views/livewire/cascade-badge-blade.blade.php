{{-- #[Cascade] only exposes cascade data to Antlers views — it's what this --}}
{{-- addon adds. Blade never gets it this way, cascade or title/site below --}}
{{-- are simply undefined here. --}}
<div class="card">
    <span>site: {{ $site ?? 'not available in blade — #[Cascade] is antlers-only' }}</span>
    <span>title: {{ $title ?? 'n/a' }}</span>
    <button wire:click="$refresh">Refresh</button>
</div>
