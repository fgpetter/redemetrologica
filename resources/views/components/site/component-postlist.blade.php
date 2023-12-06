@foreach ($posts as $post)
    {{-- main --}}
    <div class="container my-3">
        <div class="row">
            <div class="col-md  border shadow-lg">
                {{-- noticias --}}
                <div>
                    <div class="container my-5">
                        <div class="card mb-3" style="max-width: 940px;">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="{{ asset($post->thumb) }}" class="card-img" alt="...">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body h-100">
                                        <h3 class="card-title">{{ $post->titulo }}</h3>
                                        @if ($post->tipo != 'galeria')
                                            <p class="card-text">{!! $post->conteudo !!}</p>
                                        @endif
                                        <p class="card-title"><small>{{ $post->data_publicacao }}</small></p>

                                        <div class="d-flex">
                                            <a href="{{ route('noticia-show', ['slug' => $post->slug]) }}"
                                                class="card-text align-self-end"><small class="text-muted">Ver
                                                    mais</small> <i class="bi bi-arrow-right-circle-fill"></i></a>
                                        </div>
                                        <div class="d-flex justify-content-end position-absolute bottom-0 end-0 pe-3">
                                            <p class="card-text mx-1 "><small class="text-muted">Share</small>
                                                <i class="bi bi-facebook"></i>
                                            </p>
                                            <p class="card-text "><small class="text-muted">Tweet</small>
                                                <i class="bi bi-twitter"></i>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
