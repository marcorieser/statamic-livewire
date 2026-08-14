{{-- Same selection + default as the antlers #[Cascade(...)] attribute, --}}
{{-- native to Blade via @cascade. --}}
@cascade(['title', 'author' => 'Anonymous'])
<div class="card">
    <span>title: {{ $title }}</span>
    <span>author: {{ $author }}</span>
</div>
