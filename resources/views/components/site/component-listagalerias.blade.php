<!-- galera de fotos -->
<div class="container-fluid mt-5 pt-5">
    <div class=" position-relative container-fluid  text-center row  py-5">
        <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
            <h1 class=" ">GALERIA FOTOS</h1>
        </div>
        <div class="position-absolute top-50 start-50 translate-middle col-12 SiteTitulo--sombra ">
            <p class="">GALERIA</p>
        </div>
    </div>

    <div class=" container-sm d-flex  justify-content-around">
        <div class="container-sm">
            <div class="row justify-content-around">
                @foreach ($galerias as $galeria)
                    <div class="col-sm-12 col-md-6 col-lg-3 mb-3">
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('post-media/' . $galeria->thumb) }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <a href="{{ route('noticia-show', ['slug' => $galeria->slug]) }}">
                                        <p class="text-center h3 text-white">{{ $galeria->titulo }}</p>
                                    </a>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i>
                                        {{ $galeria->data_publicacao }}<span
                                            style="font-family: &quot;Archivo Black&quot;;"></span> </p>
                                    <a id="invisivel" href="{{ route('noticia-show', ['slug' => $galeria->slug]) }}"
                                        class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- galeria de fotos -->
