<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-10 shadow-lg border-1 bg-white px-0">
            {{-- noticia --}}
            <div class="container" 
                style="height: 400px; background: url({{ asset('post-media/' . $post->thumb) }}) center/cover no-repeat; ">
            </div>
            <div class="container">
                <div class="row d-flex justify-content-center align-items-center px-2">
                    <div class="col">
                        <h1>{{ $post->titulo }}</h1>
                        @if ($post->tipo == 'noticia')
                            <div>{!! $post->conteudo !!}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-10">
            <div class="container py-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-start">
                            @if ($post->tipo == 'noticia')
                                <a href="/noticias">- Notícias</a>
                            @elseif($post->tipo == 'galeria')
                                <a href="/galerias">- Galeria</a>
                            @endif
                        </div>
                    </div>
                    {{-- <div class="col-md-6 ">
                        <div class="  d-flex justify-content-end ">
                            <a href="#" class="card-text mx-3 "><small class="text-muted">Share</small>
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="card-text "><small class="text-muted">Tweet</small>
                                <i class="bi bi-twitter"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
{{-- noticia --}}
