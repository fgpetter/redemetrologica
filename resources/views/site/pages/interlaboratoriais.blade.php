@extends('site.layouts.layout-site')
@section('content')
    {{-- banner inicial --}}
    <div class="card text-bg-dark">
        <img src="{{ asset('build\images\site\PEP-BANNER-DE-TOPO-1920-x-575-px_B.png') }}" class="card-img" alt="...">

        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center text-center ">
                <p class="SiteBanner--interlab"><strong>INTERLABORATORIAIS</strong></p>
            </div>
        </div>

    </div>
    {{-- banner inicial --}}

    {{-- baixar --}}
    <div class="container my-5">
        <div class="row  d-flex align-items-center justify-content-center text-center">
            <div class="col">
                <h2>CLIQUE AQUI PARA BAIXAR A PROGRAMAÇÃO ANUAL</h2>
            </div>
        </div>
    </div>
    {{-- baixar --}}

    {{-- interlab --}}
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
    {{-- interlab --}}
@endsection
