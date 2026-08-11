{{-- Same #[Cascade(...)] selection as the antlers view, but it doesn't apply --}}
{{-- to Blade — nothing below is defined without doing it manually. --}}
<div class="card">
    <span>title: {{ $title ?? 'n/a' }}</span>
    <span>author: {{ $author ?? 'n/a' }}</span>
</div>
