{{-- #[Computed] properties are native Livewire — no addon feature needed here, --}}
{{-- $this->entry is called the same way Blade always allows. --}}
<div class="card">
    <h3>{{ $this->entry?->get('title') }}</h3>
    <p>by {{ $this->entry?->get('author') }}</p>
</div>
