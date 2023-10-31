@extends('site.layouts.layout-site')
@section('content')
    {{-- carousel --}}
    <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('build\images\site\BANNER-CURSOS-DESTAQUE-2_B.png') }}" class="card-img" alt="...">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('build\images\site\BANNER-CURSOS-DESTAQUE-3_B-1.png') }}" class="card-img" alt="...">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('build\images\site\BANNER-CURSOS-DESTAQUE-1_B.png') }}" class="card-img" alt="...">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    {{-- carousel --}}

    {{-- pesquisa --}}
    <div class="container my-5">
        <div class="btn-toolbar justify-content-start" role="toolbar" aria-label="Toolbar with button groups">
            <div class="input-group border">
                <input type="text" class="form-control" placeholder="PESQUISAR POR" aria-label="Input group example"
                    aria-describedby="btnGroupAddon2">
                <div class="input-group-text" id="btnGroupAddon2"><i class="bi bi-search"></i></div>
            </div>
            <div class="btn-group mx-3" role="group" aria-label="First group">
                <button type="button" class="btn btn-primary">Cursos Agendados</button>
            </div>
        </div>

    </div>
    {{-- pesquisa --}}

    {{-- cardCursos --}}
    <div class="container-fluid">
        <!-- Tela SM e XS-->
        <div id="carouselGaleriaControlsSMXS"
            class="carousel carousel-dark slide d-block d-md-none d-xl-none d-lg-none d-xxl-none" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class=" container-sm d-flex  justify-content-around">

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container-sm d-flex   justify-content-around">

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container-sm d-flex   justify-content-around">
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">CEP – Controle</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container-sm d-flex   justify-content-around">
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">FMEA – Análise</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container-sm d-flex   justify-content-around">
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>



            <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControlsSMXS"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControlsSMXS"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <!-- tela SM e XS -->

        <!-- Tela MD-->
        <div id="carouselGaleriaControlsMD"
            class="carousel carousel-dark slide d-none d-md-block d-xl-none d-lg-none d-xxl-none" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class=" container-sm d-flex  justify-content-around">


                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container-sm d-flex   justify-content-around">

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">CEP – Controle</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">FMEA – Análise</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container-sm d-flex   justify-content-around">
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>



            <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControlsMD"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControlsMD"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <!-- tela MD -->

        <!-- Tela XL e LG-->
        <div id="carouselGaleriaControlsXLLG" class="carousel carousel-dark slide d-none d-xl-block d-lg-block d-xxl-none"
            data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class=" container-sm d-flex  justify-content-around">


                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">FMEA – Análise</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container-sm d-flex   justify-content-around">
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">CEP – Controle</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControlsXLLG"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControlsXLLG"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <!-- tela XL e LG -->

        <!-- Telas XLL -->
        <div id="carouselGaleriaControlsXXL" class="carousel carousel-dark slide d-none d-xxl-block"
            data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class=" container-sm d-flex  justify-content-around">


                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">FMEA – Análise</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">CEP – Controle</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container-sm d-flex   justify-content-around">

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="SiteCards__bgimage  text-white d-grid"
                                style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                                <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">FMEA – Análise</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span
                                            style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i
                                            class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControlsXXL"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControlsXXL"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <!-- Telas XLL -->
    </div>
    {{-- cardCursos --}}
@endsection
