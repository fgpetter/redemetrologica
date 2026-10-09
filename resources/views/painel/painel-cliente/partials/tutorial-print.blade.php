@php
    $imageUrl = asset('build/images/'.$image);
@endphp

<figure class="card border-0 shadow-sm mb-4">
    <figcaption class="card-body small text-muted">
        <span class="d-block fw-semibold text-body mb-1">{{ $caption }}</span>
        {{ $legend }}
    </figcaption>
    <div class="tutorial-image-viewport">
        <div class="tutorial-image-frame">
            <a href="{{ $imageUrl }}" target="_blank" rel="noopener" aria-label="Ampliar captura de tela: {{ $alt }}">
                <img src="{{ $imageUrl }}" alt="{{ $alt }}" loading="lazy" class="img-fluid">
            </a>
            @foreach ($markers as $marker)
                <span class="tutorial-image-marker" style="left: {{ $marker['x'] * 100 }}%; top: {{ $marker['y'] * 100 }}%;" aria-hidden="true">
                    {{ $marker['number'] }}
                </span>
            @endforeach
        </div>
    </div>
</figure>
