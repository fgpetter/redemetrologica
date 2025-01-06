@extends('site.layouts.layout-site')
@section('title') Home @endsection
@section('content')
    <!-- {{-- banner inicial --}} -->
    <div class="card text-bg-dark">
        <img src="{{ asset('build\images\site\BANNER-HOME-TOPO.png') }}" class="card-img" alt="...">

        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center text-center ">
                <p class="SiteBanner--titulo"><strong>REDE METROLÓGICA RS</strong></p>
                <p class="SiteBanner--text">Certificada ABNT NBR ISO 9001 pela DNV</p>
                <p class="SiteBanner--text">Acreditada ABNT NBR ISO/IEC 17043 pela Cgcre - PEP 0002</p>
            </div>
        </div>

    </div>
    <!-- {{-- banner inicial --}} -->

    <!-- {{-- cards iniciais --}} -->
    <div class="container">


        <div class="row my-5" style="margin-top: 0;">

            <a href="/sobre" class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito">
                <img src="{{ asset('build\images\site\DESTAQUES-HOME-REDE-600-x-600-px.png') }}" class="card-img "
                    alt="...">
                <div class="card-img-overlay d-flex justify-content-center">
                    <div class="align-self-center ">
                        <h1 class="h4 ">A Rede</h1>
                    </div>
                </div>
            </a>


            <a href="/associe-se" class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito">
                <img src="{{ asset('build\images\site\DESTAQUES-HOME-ASSOCIE-SE-600-x-600-px.png') }}" class="card-img"
                    alt="...">
                <div class="card-img-overlay d-flex justify-content-center">
                    <div class="align-self-center ">
                        <h1 class="h4 ">Associe-se</h1>
                    </div>
                </div>
            </a>

            <a href="/cursos" class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito">
                <img src="{{ asset('build\images\site\DESTAQUES-HOME-CURSOS-600-x-600-px.png') }}" class="card-img"
                    alt="...">
                <div class="card-img-overlay d-flex justify-content-center">
                    <div class="align-self-center ">
                        <h1 class="h4 ">Cursos e Eventos</h1>
                    </div>
                </div>
            </a>

            <a href="/interlaboratoriais" class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito ">
                <img src="{{ asset('build\images\site\HOME-DESTAQUES-PEP-600-x-600-px (1).png') }}" class="card-img"
                    alt="...">
                <div class="card-img-overlay d-flex justify-content-center">
                    <div class="align-self-center ">
                        <h1 class="h4 ">Ensaios de Proficiência</h1>
                    </div>
                </div>
            </a>

            <a href="laboratorios-reconhecidos" class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito">
                <img src="{{ asset('build\images\site\DESTAQUES-HOME-LABORATÓRIO-600-x-600-px.png') }}" class="card-img"
                    alt="...">
                <div class="card-img-overlay d-flex justify-content-center">
                    <div class="align-self-center ">
                        <h1 class="h4 ">Laboratórios</h1>
                    </div>
                </div>
            </a>


        </div>
    </div>
    <!-- {{-- cards iniciais --}} -->

    <!-- {{-- bem vindo --}} -->
    <div class="container">
        <div class="row my-5 d-flex align-items-center">
            <div class="col-12 col-md-6">
                <div class="text-center px-3">
                    <h1>BEM-VINDO À REDE METROLÓGICA RS</h1>
                    <p>Somos uma associação técnica, de cunho técnico-científico, sem fins lucrativos e atuamos como
                        articuladora na prestação de serviços qualificados de metrologia para o aprimoramento
                        tecnológico das
                        empresas.

                        Pioneira entre as demais Redes estaduais existentes no país, desde 1992 articulamos
                        parcerias
                        indispensáveis para viabilizar a execução de suas metas.</p>
                    <a href="/sobre" type="button" class=" mb-3 btn btn-primary">Saiba Mais</a>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <img src="{{ asset('build\images\site\HOME-BEM-VINDO-700-x-462.png') }}" class="card-img img-fluid rounded "
                    alt="...">
            </div>
        </div>
    </div>
    <!-- {{-- bem vindo --}} -->

    <!-- {{-- pq associar --}} -->
    <div class="container-fluid mt-5 pt-5">
        <div class=" position-relative container-fluid text-center row py-5">
            <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
                <h1 class=" ">POR QUE SER UM ASSOCIADO</h1>
            </div>
            <div class="position-absolute top-50 start-50 translate-middle col-12 SiteTitulo--sombra ">
                <p class="">ASSOCIADO</p>
            </div>
        </div>

        <div class="container my-5">
            <div class="row m-auto h5">
                <div class="col-12 col-lg-6">
                    <p> <i class="fa-solid fa-circle-check"></i> Valores diferenciados nas inscrições de eventos,
                        treinamentos abertos e in company</p>
                    <p> <i class="fa-solid fa-circle-check"></i> Valores diferenciados nas inscrições em Programa de
                        Ensaios de Proficiência (PEP).</p>
                    <p> <i class="fa-solid fa-circle-check"></i> Poder divulgar sua condição de membro da RMRS
                        conforme regras vigentes.</p>
                    <p> <i class="fa-solid fa-circle-check"></i> Para laboratórios reconhecidos, divulgação do
                        escopo de serviços no site da Rede Metrológica RS e disponibilização do Bônus Metrologia
                        para seus clientes.</p>
                </div>
                <div class="col-12 col-lg-6">
                    <p> <i class="fa-solid fa-circle-check"></i> Espaço para publicação de matérias sobre metrologia
                        e qualidade.</p>
                    <p> <i class="fa-solid fa-circle-check"></i> Atendimento de dúvidas técnicas.</p>
                    <p> <i class="fa-solid fa-circle-check"></i> Maior credibilidade junto a clientes por seguir
                        critérios de qualidade.</p>
                    <p> <i class="fa-solid fa-circle-check"></i> Receber via e-mail divulgação de PEPs, cursos e
                        eventos realizados pela Rede Metrológica RS.</p>
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <a href="/associe-se" type="button" class="btn btn-warning btn-lg">Quero ser Associado</a>
            </div>
        </div>
    </div>
    <!-- {{-- pq associar --}} -->

    <!-- destaques -->
    <div class="container my-5">
        <div class="row mb-5">
            <div class="col-12 col-lg-6 mb-5 pb-4">

                <div class="text-center">
                    <p class="h2 text-bold">CURSOS</p>
                </div>
                <div class="SiteCards__bgimage p-5 text-center mb-5 text-white position-relative"
                    style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-BANNER-CURSOS.jpg') }}');height:100%; width:100%;">

                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                        <a href="/cursos" type="button" class="btn btn-warning btn-lg">Ver mais</a>
                    </div>
                </div>

            </div>
            <div class="col-12 col-lg-6 mb-5 pb-4">

                <div class="text-center">
                    <p class="h2 text-bold">AVALIAÇÃO LABORATÓRIOS</p>
                </div>
                <div class="SiteCards__bgimage p-5 text-center mb-5 text-white position-relative"
                    style="background-size: cover; background-image: url('{{ asset('build/images/site/LAB-SOLICITAR-RECONHECIMENTO-1349-x-443.png') }}');height:100%; width:100%;">

                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                        <a href="/laboratorios-avaliacao" type="button" class="btn btn-warning btn-lg">Ver mais</a>
                    </div>
                </div>

            </div>

            <div class="col-12 col-lg-6 mb-5 pb-4">

                <div class="text-center">
                    <p class="h2 text-bold">PROGRAMAS DE ENSAIOS DE PROFICIÊNCIA</p>
                </div>
                <div class="SiteCards__bgimage p-5 text-center mb-5 text-white position-relative"
                    style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-BANNER-PEPS-1349-x-443_200722062833.jpg') }}');height:100%; width:100%;">

                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                        <a href="/interlaboratoriais" type="button" class="btn btn-warning btn-lg">Ver mais</a>
                    </div>
                </div>

            </div>
            <div class="col-12 col-lg-6 mb-5 pb-4">

                <div class="text-center">
                    <p class="h2 text-bold">LABORATÓRIOS RECONHECIDOS</p>
                </div>
                <div class="SiteCards__bgimage p-5 text-center mb-5 text-white position-relative"
                    style="background-size: cover; background-image: url('{{ asset('build/images/site/LAB-RECONHECIDO-1349-x-443.png') }}'); height:100%; width:100%;">
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                        <a href="/laboratorios-reconhecidos" type="button" class="btn btn-warning btn-lg">Ver mais</a>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <!-- destaques -->
    @if(!$noticias->isEmpty())
    <x-site.component-listanoticias :noticias="$noticias" :galerias="$galerias" />
    @endif
    @if(!$galerias->isEmpty())
    <x-site.component-listagalerias :noticias="$noticias" :galerias="$galerias" />
    @endif
@endsection
