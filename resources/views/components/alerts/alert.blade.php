
@foreach (['error', 'success', 'warning'] as $key)
    @if (session($key))
        <div class="alert alert-{{ ($key == 'error' ? 'danger' : $key) }} alert-dismissible fade show" role="alert">
            {{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach
