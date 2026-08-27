<div>
    @foreach ($articles as $article)
        <div class="card">
            <a href="{{ $article->url() }}">{{ $article->get('title') }}</a> — <span>{{ $article->get('author') }}</span>
        </div>
    @endforeach
    {{ $links }}
</div>
