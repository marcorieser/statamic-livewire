{{-- Statamic ships this natively for Blade — @cascade populates the current --}}
{{-- scope with cascade data. The addon's #[Cascade] attribute is the --}}
{{-- Antlers equivalent, since Antlers has no directive/compiler step to --}}
{{-- hook into. --}}
@cascade
<div class="card">
    <span>site: {{ $site->handle() }}</span>
    <span>title: {{ $title }}</span>
    <button wire:click="$refresh">Refresh</button>
</div>
