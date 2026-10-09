<div class="alert alert-secondary bg-body-secondary d-flex flex-wrap align-items-center gap-3" role="note">
    @foreach ($items as $item)
        <a href="{{ route('painel-tutorial-pep') }}#{{ $item['section'] }}" target="_blank" rel="noopener" class="alert-link">
            {{ $item['title'] }}
        </a>
    @endforeach
</div>