@props(['eyebrow', 'title', 'description', 'actionRoute' => null, 'actionLabel' => null])

<section class="page-header">
    <div>
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $title }}</h1>
        <p class="page-description">{{ $description }}</p>
    </div>
    @if ($actionRoute)
        <a class="primary-button" href="{{ $actionRoute }}">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 5v14M5 12h14" />
            </svg>
            {{ $actionLabel }}
        </a>
    @endif
</section>
