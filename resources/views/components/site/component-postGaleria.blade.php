<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-10 shadow-lg border-1">


            {{-- slide --}}
            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="true">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
                        aria-current="true" aria-label="Slide 1"></button>
                    @foreach ($postMedia as $media)
                        <button type="button" data-bs-target="#carouselExampleIndicators"
                            data-bs-slide-to="{{ $loop->index + 1 }}"
                            aria-label="Slide {{ $loop->index + 1 }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="{{ asset('post-media/' . $post->thumb) }}" class="d-block w-100"
                            style="height: 600px; object-fit: cover;" alt="...">
                    </div>
                    @foreach ($postMedia as $media)
                        <div class="carousel-item">
                            <img src="{{ asset('post-media/' . $media->caminho_media) }}" class="d-block w-100"
                                style="height: 600px; object-fit: cover;" alt="...">
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
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
</div>
<script>
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
    }
</script>
