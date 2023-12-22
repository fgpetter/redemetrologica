<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-10 shadow-lg border-1">
            <!-- imagem principal -->
            <div class="container d-flex justify-content-center align-items-center my-3">
                <img id="mainImage" src="{{ asset('post-media/' . $post->thumb) }}" class="img-fluid rounded"
                    alt="...">
            </div>
            <div class="container">
                <div class="row">
                    @foreach ($postMedia as $media)
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card border m-2" style="width: 11rem; height: 11rem"
                                id="card-{{ $media->id }}">
                                <div class="card-body"
                                    style="background-image: url('{{ asset('post-media/' . $media->caminho_media) }}'); background-size: cover; cursor: pointer;"
                                    onclick="changeImage('{{ asset('post-media/' . $media->caminho_media) }}')">
                                </div>
                            </div>
                        </div>
                    @endforeach
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
                    <div class="col-md-6 ">
                        <div class="  d-flex justify-content-end ">
                            <a href="#" class="card-text mx-3 "><small class="text-muted">Share</small>
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="card-text "><small class="text-muted">Tweet</small>
                                <i class="bi bi-twitter"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
    }
</script>
