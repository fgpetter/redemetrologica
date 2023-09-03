@extends('layouts.master-without-nav')

@section('content')

<!-- navbar -->
<nav class="navbar navbar-expand-lg sticky-top py-3 SiteHeader border-bottom">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" height="95">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav nav-underline h5">
                <li class="nav-item">
                    <a class="nav-link" href="#">Notícias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Associe-se</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Cursos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Interlaboratoriais</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Laboratórios
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Avaliação de Laboratórios</a></li>
                        <li><a class="dropdown-item" href="#">Laboratórios Reconhecidos</a></li>
                        <li><a class="dropdown-item" href="#">Bônus Metrologia</a></li>
                        <li><a class="dropdown-item" href="#">Downloads</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Fale Conosco</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- navbar -->

<div class="card text-bg-dark">
    <img src="{{ asset('build\images\site\BANNER-HOME-TOPO.png') }}" class="card-img" alt="...">
    <div class="card-img-overlay d-flex justify-content-center">
        <div class="align-self-center text-center">
            <h1 class="text-warning ">REDE METROLÓGICA RS</h1>
            <h2 class="h2 text-white">Certificada ABNT NBR ISO 9001 pela DNV</h2>
            <h2 class="h2 text-white">Acreditada ABNT NBR ISO/IEC 17043 pela Cgcre - PEP 0002</h2>
        </div>
    </div>
</div>
<!-- {{-- banner inicial --}} -->

<!-- {{-- cards iniciais --}} -->
<div class="container">

    <div class="row my-5" style="margin-top: 0;">

        <div class="card bg-transparent border-0 shadow-none col-sm-6 col-md">
            <img src="{{ asset('build\images\site\DESTAQUES-HOME-REDE-600-x-600-px.png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">A Rede</h1>
                </div>
            </div>
        </div>


        <div class="card bg-transparent border-0 shadow-none col-sm-6 col-md">
            <img src="{{ asset('build\images\site\DESTAQUES-HOME-ASSOCIE-SE-600-x-600-px.png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">Associe-se</h1>
                </div>
            </div>
        </div>

        <div class="card bg-transparent border-0 shadow-none col-sm-6 col-md">
            <img src="{{ asset('build\images\site\DESTAQUES-HOME-CURSOS-600-x-600-px.png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">Cursos e Eventos</h1>
                </div>
            </div>
        </div>

        <div class="card bg-transparent border-0 shadow-none col-sm-6 col-md">
            <img src="{{ asset('build\images\site\HOME-DESTAQUES-PEP-600-x-600-px (1).png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">Ensaios de Proficiência</h1>
                </div>
            </div>
        </div>

        <div class="card bg-transparent border-0 shadow-none col-sm-6 col-md">
            <img src="{{ asset('build\images\site\DESTAQUES-HOME-LABORATÓRIO-600-x-600-px.png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">Laboratórios</h1>
                </div>
            </div>
        </div>


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
                    articuladora na prestação de serviços qualificados de metrologia para o aprimoramento tecnológico das
                    empresas.

                    Pioneira entre as demais Redes estaduais existentes no país, desde 1992 articulamos parcerias
                    indispensáveis para viabilizar a execução de suas metas.</p>
                <button type="button" class=" mb-3 btn btn-primary">Saiba Mais</button>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <img src="{{ asset('build\images\site\HOME-BEM-VINDO-700-x-462.png') }}" class="card-img img-fluid rounded " alt="...">
        </div>
    </div>
</div>
<!-- {{-- bem vindo --}} -->

<!-- {{-- pq associar --}} -->
<div class="container-fluid text-center row py-5">
    <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
        <h1 class=" ">POR QUE SER UM ASSOCIADO</h1>
    </div>
    <div class="col-12 SiteTitulo--sombra ">
        <p class="">ASSOCIADO</p>
    </div>
</div>

<div class="container my-5">
    <div class="row m-auto h5">
        <div class="col-12 col-lg-6">
            <p> <i class="fa-solid fa-circle-check"></i> Valores diferenciados nas inscrições de eventos, treinamentos abertos e in company</p>
            <p> <i class="fa-solid fa-circle-check"></i> Valores diferenciados nas inscrições em Programa de Ensaios de Proficiência (PEP).</p>
            <p> <i class="fa-solid fa-circle-check"></i> Poder divulgar sua condição de membro da RMRS conforme regras vigentes.</p>
            <p> <i class="fa-solid fa-circle-check"></i> Para laboratórios reconhecidos, divulgação do escopo de serviços no site da Rede Metrológica RS e disponibilização do Bônus Metrologia para seus clientes.</p>
        </div>
        <div class="col-12 col-lg-6">
            <p> <i class="fa-solid fa-circle-check"></i> Espaço para publicação de matérias sobre metrologia e qualidade.</p>
            <p> <i class="fa-solid fa-circle-check"></i> Atendimento de dúvidas técnicas.</p>
            <p> <i class="fa-solid fa-circle-check"></i> Maior credibilidade junto a clientes por seguir critérios de qualidade.</p>
            <p> <i class="fa-solid fa-circle-check"></i> Receber via e-mail divulgação de PEPs, cursos e eventos realizados pela Rede Metrológica RS.</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
        <button type="button" class="btn btn-warning btn-lg">Quero ser Associado</button>
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
            <div class="SiteCards__bgimage p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-BANNER-CURSOS.jpg') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>
        <div class="col-12 col-lg-6 mb-5 pb-4">

            <div class="text-center">
                <p class="h2 text-bold">AVALIAÇÃO LABORATÓRIOS</p>
            </div>
            <div class="SiteCards__bgimage p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/LAB-SOLICITAR-RECONHECIMENTO-1349-x-443.png') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>

        <div class="col-12 col-lg-6 mb-5 pb-4">

            <div class="text-center">
                <p class="h2 text-bold">PROGRAMAS DE ENSAIOS DE PROFICIÊNCIA</p>
            </div>
            <div class="SiteCards__bgimage p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-BANNER-PEPS-1349-x-443_200722062833.jpg') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>
        <div class="col-12 col-lg-6 mb-5 pb-4">

            <div class="text-center">
                <p class="h2 text-bold">LABORATÓRIOS RECONHECIDOS</p>
            </div>
            <div class="SiteCards__bgimage p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/LAB-RECONHECIDO-1349-x-443.png') }}'); height:100%; width:100%;">
                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>

    </div>
</div>
<!-- destaques -->

<!-- noticias -->
<div class="container-fluid text-center row py-5">
    <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
        <h1 class=" ">NOTÍCIAS</h1>
    </div>
    <div class="col-12 SiteTitulo--sombra ">
        <p class="">NOTÍCIAS</p>
    </div>
</div>

<div class="container-fluid pb-5 mb-5" style=" height: 300px">
    <!-- Tela SM e XS -->
    <div id="carouselNoticiasControlsSMXS" class="carousel carousel-dark slide d-block d-md-none d-xl-none d-lg-none d-xxl-none" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class=" container-sm d-flex  justify-content-around ">
                    <div class="card hover-shadow" style="width: 18rem; ">
                        <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                            <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que estão utilizando uniformes, crachás e documentos falsificados.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">
                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                            <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta quarta-feira (3/5), uma nova norma que trata dos requisitos técnico-sanitários.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">
                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5"> O que é biotecnologia e...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 25<span>th</span>, 2023</p>
                            <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um planeta em constante crescimento. Segundo a Organização das Nações Unidas (ONU)... </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">
                    <div class="card hover-shadow" style=" width: 18rem;">
                        <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Em três meses, operação do...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 21<span>st</span>, 2023</p>
                            <p class="opacity-50"> Durante 13 semanas, fiscais do Instituto Nacional de Metrologia, Qualidade e Tecnologia (Inmetro) foram às ruas em todo. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">

                    <div class="card hover-shadow" style=" width: 18rem;">
                        <img src="{{ asset('build\images\site\CONFIRA-OS-PROXIMOS-32-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5"> CONHEÇA OS BENEFÍCIOS DOS TREINAMENTOS...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 22<span>nd</span>, 2023</p>
                            <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">

                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 16<span>th</span>, 2023 </p>
                            <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8) a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">

                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Lei Geral de Proteção de...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 10<span>th</span>, 2023 </p>
                            <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD, tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">

                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}" class="card-img-top mb-5 pb-2" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1<span>st</span>, 2023 /p>
                            <p class="opacity-50"> Boletim Eletrônico - Agosto </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticiasControlsSMXS" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticiasControlsSMXS" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- Tela SM e XS -->

    <!-- Tela MD-->
    <div id="carouselNoticiasControlsMD" class="carousel carousel-dark slide  d-none d-md-block d-xl-none d-lg-none d-xxl-none" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="card-wrapper container-sm d-flex  justify-content-around ">
                    <div class="card hover-shadow" style="width: 288px;">
                        <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                            <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que estão utilizando uniformes, crachás e documentos falsificados.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>


                    <div class="card hover-shadow" style="width: 288px;">
                        <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                            <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta quarta-feira (3/5), uma nova norma que trata dos requisitos técnico-sanitários.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class="card-wrapper container-sm d-flex   justify-content-around">
                    <div class="card hover-shadow" style="width: 288px;">
                        <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5"> O que é biotecnologia e...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 25<span>th</span>, 2023</p>
                            <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um planeta em constante crescimento. Segundo a Organização das Nações Unidas (ONU)... </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>


                    <div class="card hover-shadow" style=" width: 288px;">
                        <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Em três meses, operação do...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 21<span>st</span>, 2023</p>
                            <p class="opacity-50"> Durante 13 semanas, fiscais do Instituto Nacional de Metrologia, Qualidade e Tecnologia (Inmetro) foram às ruas em todo. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class="card-wrapper container-sm d-flex   justify-content-around">

                    <div class="card hover-shadow" style=" width: 288px;">
                        <img src="{{ asset('build\images\site\CONFIRA-OS-PROXIMOS-32-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5"> CONHEÇA OS BENEFÍCIOS DOS TREINAMENTOS...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 22<span>nd</span>, 2023</p>
                            <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>


                    <div class="card hover-shadow" style="width: 288px;">
                        <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 16<span>th</span>, 2023 </p>
                            <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8) a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class="card-wrapper container-sm d-flex   justify-content-around">

                    <div class="card hover-shadow" style="width: 288px;">
                        <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Lei Geral de Proteção de...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 10<span>th</span>, 2023 </p>
                            <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD, tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>



                    <div class="card hover-shadow" style="width: 288px;">
                        <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}" class="card-img-top mb-5 pb-2" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1<span>st</span>, 2023 /p>
                            <p class="opacity-50"> Boletim Eletrônico - Agosto </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticiasControlsMD" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticiasControlsMD" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- Tela MD-->

    <!-- Tela XL e LG-->
    <div id="carouselNoticiasControlsXLLG" class="carousel carousel-dark slide d-none d-xl-block d-lg-block d-xxl-none" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class=" container-sm d-flex  justify-content-around ">
                    <div class="card hover-shadow" style="width: 18rem; ">
                        <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                            <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que estão utilizando uniformes, crachás e documentos falsificados.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>


                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                            <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta quarta-feira (3/5), uma nova norma que trata dos requisitos técnico-sanitários.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>


                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5"> O que é biotecnologia e...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 25<span>th</span>, 2023</p>
                            <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um planeta em constante crescimento. Segundo a Organização das Nações Unidas (ONU)... </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">
                    <div class="card hover-shadow" style=" width: 18rem;">
                        <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Em três meses, operação do...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 21<span>st</span>, 2023</p>
                            <p class="opacity-50"> Durante 13 semanas, fiscais do Instituto Nacional de Metrologia, Qualidade e Tecnologia (Inmetro) foram às ruas em todo. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>



                    <div class="card hover-shadow" style=" width: 18rem;">
                        <img src="{{ asset('build\images\site\CONFIRA-OS-PROXIMOS-32-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5"> CONHEÇA OS BENEFÍCIOS DOS TREINAMENTOS...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 22<span>nd</span>, 2023</p>
                            <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>


                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 16<span>th</span>, 2023 </p>
                            <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8) a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class=" container-sm d-flex   justify-content-around">

                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Lei Geral de Proteção de...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 10<span>th</span>, 2023 </p>
                            <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD, tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>



                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}" class="card-img-top mb-5 pb-2" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1<span>st</span>, 2023 /p>
                            <p class="opacity-50"> Boletim Eletrônico - Agosto </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                    <div class="card hover-shadow" style="width: 18rem; ">
                        <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                            <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que estão utilizando uniformes, crachás e documentos falsificados.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticiasControlsXLLG" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticiasControlsXLLG" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- Tela XL e LG-->

    <!-- Telas XLL -->
    <div id="carouselNoticiasControlsXLL" class="carousel carousel-dark slide d-none d-xxl-block" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="card-wrapper container-sm d-flex  justify-content-around ">
                    <div class="card hover-shadow " style="width: 18rem;">
                        <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                            <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que estão utilizando uniformes, crachás e documentos falsificados.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                    <div class="card hover-shadow " style="width: 18rem;">
                        <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                            <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta quarta-feira (3/5), uma nova norma que trata dos requisitos técnico-sanitários.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                    <div class="card hover-shadow " style="width: 18rem;">
                        <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5"> O que é biotecnologia e...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 25<span>th</span>, 2023</p>
                            <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um planeta em constante crescimento. Segundo a Organização das Nações Unidas (ONU)... </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Em três meses, operação do...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 21<span>st</span>, 2023</p>
                            <p class="opacity-50"> Durante 13 semanas, fiscais do Instituto Nacional de Metrologia, Qualidade e Tecnologia (Inmetro) foram às ruas em todo. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class="card-wrapper container-sm d-flex   justify-content-around">

                    <div class="card hover-shadow" style="width: 18rem;">
                        <img src="{{ asset('build\images\site\CONFIRA-OS-PROXIMOS-32-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5"> CONHEÇA OS BENEFÍCIOS DOS TREINAMENTOS...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 22<span>nd</span>, 2023</p>
                            <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                    <div class="card hover-shadow " style="width: 18rem;">
                        <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 16<span>th</span>, 2023 </p>
                            <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8) a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.</p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                    <div class="card hover-shadow " style="width: 18rem;">
                        <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Lei Geral de Proteção de...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 10<span>th</span>, 2023 </p>
                            <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD, tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                    <div class="card hover-shadow " style="width: 18rem;">
                        <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}" class="card-img-top mb-5 pb-2" alt="...">
                        <div class="card-body">
                            <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                            <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1<span>st</span>, 2023 /p>
                            <p class="opacity-50"> Boletim Eletrônico - Agosto </p>
                            <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticiasControlsXLL" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticiasControlsXLL" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- Telas XLL -->
</div>
<!-- noticias -->

<!-- galera de fotos -->
<div class="container-fluid mt-5 pt-5">
    <div class="container-fluid pt-5 mt-5  text-center row">
        <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
            <h1 class=" ">GALERIA FOTOS</h1>
        </div>
        <div class="col-12 SiteTitulo--sombra ">
            <p class="">GALERIA</p>
        </div>
    </div>

    <!-- Tela SM e XS-->
    <div id="carouselGaleriaControlsSMXS" class="carousel carousel-dark slide d-block d-md-none d-xl-none d-lg-none d-xxl-none" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class=" container-sm d-flex  justify-content-around">

                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
            <div class="carousel-item">
                <div class="container-sm d-flex   justify-content-around">

                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
            <div class="carousel-item">
                <div class="container-sm d-flex   justify-content-around">
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">CEP – Controle</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class="container-sm d-flex   justify-content-around">
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">FMEA – Análise</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class="container-sm d-flex   justify-content-around">
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>



        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControlsSMXS" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControlsSMXS" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- tela SM e XS -->

    <!-- Tela MD-->
    <div id="carouselGaleriaControlsMD" class="carousel carousel-dark slide d-none d-md-block d-xl-none d-lg-none d-xxl-none" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class=" container-sm d-flex  justify-content-around">


                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <div class="carousel-item">
                <div class="container-sm d-flex   justify-content-around">

                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">CEP – Controle</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">FMEA – Análise</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
            <div class="carousel-item">
                <div class="container-sm d-flex   justify-content-around">


                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>



        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControlsMD" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControlsMD" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- tela MD -->

    <!-- Tela XL e LG-->
    <div id="carouselGaleriaControlsXLLG" class="carousel carousel-dark slide d-none d-xl-block d-lg-block d-xxl-none" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class=" container-sm d-flex  justify-content-around">


                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">FMEA – Análise</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="carousel-item">
                <div class="container-sm d-flex   justify-content-around">
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">CEP – Controle</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControlsXLLG" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControlsXLLG" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- tela XL e LG -->

    <!-- Telas XLL -->
    <div id="carouselGaleriaControlsXXL" class="carousel carousel-dark slide d-none d-xxl-block" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class=" container-sm d-flex  justify-content-around">


                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">FMEA – Análise</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">CEP – Controle</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="container-sm d-flex   justify-content-around">

                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="  " style="width: 18rem; height: 13rem;">
                        <div class="SiteCards__bgimage  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                            <div class="SiteCards--efeito d-grid align-self-end align-items-end p-3">
                                <p class="text-center h3 text-white">FMEA – Análise</p>
                                <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControlsXXL" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControlsXXL" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- Telas XLL -->
</div>
<!-- galeria de fotos -->

<!-- footer -->
<footer>
    <div class=" mt-5 pt5 mb-5 pb-5 SiteCards__bgimage  text-white position relative" style="background-image: url('{{ asset('build/images/site/banner-footer.png') }}');">
        <div class="d-flex justify-content-center">
            <div class="container m-5 p-5">
                <div class="row">
                    <div class="col-sm-4 col-xs-12">
                        <div class="">
                            <img src="{{ asset('build\images\site\LOGO_REDE_BRANCO.png') }}" class="card-img w-max-350 SiteFooter__imagem">
                            <div class="mt-3">
                                Associação Rede de Metrologia e Ensaios do RS<br>
                                CNPJ 97.130.207/0001-12<br>
                                Certificada ISO 9001 pela DNV<br>
                                Acreditada ISO/IEC 17043 pela CGCRE<br>
                                Soluções em Metrologia para <br>
                                Qualidade e Sustentabilidade
                                <br>
                                <br>
                                <a class="text-white" href="https://redemetrologica.com.br/politica-de-privacidade/">Política de Privacidade</a>
                                <br>
                                <a class="text-white" href="https://redemetrologica.com.br/politica-de-cookies/">Política de Cookies</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-xs-12">
                        <h5 class="text-white">Contato</h5>
                        <div class="">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-telephone-fill"></i><a class="text-white" title="Telefone" href="tel:+55 51 2200-3988 ">+55 51 2200-3988 </a></li>
                                <li><i class="bi bi-envelope-fill"></i><a class="text-white" title="E-mail" href="mailto:contato@redemetrologica.com.br">contato@redemetrologica.com.br</a></li>
                            </ul>
                            <i class="bi bi-geo-alt-fill"></i>Santa Catarina, nº 40 - Salas 801/802 <br>
                            Porto Algre - RS <br>
                            Bairro Santa Maria Goretti <br>
                            Cep 91030-330


                        </div>
                    </div>
                    <div class="col-sm-4 col-xs-12">
                        <h5 class="text-white">Acesso Rápido</h5>
                        <div class="">
                            <ul class="list-unstyled">
                                <li class=""><a class="text-white" href="https://redemetrologica.com.br/noticias-2/">Notícias</a></li>
                                <li class=""><a class="text-white" href="https://redemetrologica.com.br/associe-se-2/">Associe-se</a></li>
                                <li class=""><a class="text-white" href="https://redemetrologica.com.br/cursos/">Cursos</a></li>
                                <li class=""><a class="text-white" href="https://redemetrologica.com.br/interlaboratoriais/">Interlaboratoriais</a></li>
                                <li class=""><a class="text-white" href="#">Laboratórios</a>
                                    <ul class=" list-unstyled " list-unstyled"">
                                        <li class=""><a class="text-white" href="https://redemetrologica.com.br/laboratorios-avaliacao/"> Avaliação de Laboratórios</a></li>
                                        <li class=""><a class="text-white" href="https://redemetrologica.com.br/laboratorios-reconhecidos/"> Laboratórios Reconhecidos</a></li>
                                        <li class=""><a class="text-white" href="https://redemetrologica.com.br/bonus-metrologia/"> Bônus Metrologia</a></li>
                                        <li class=""><a class="text-white" href="https://redemetrologica.com.br/downloads/"> Downloads</a></li>
                                    </ul>
                                </li>
                                <li class=""><a class="text-white" href="https://redemetrologica.com.br/fale-conosco/">Fale Conosco</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center SiteFooter__rodape position-absolute bottom-0 ">
            <div class="container m-1">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-center d-flex justify-content-center align-items-center ">
                        <h2 class=" text-white"> Rede Metrológica RS © </h2>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-center d-flex justify-content-center align-items-center ">
                        <ul class="d-flex align-items-center mt-1">
                            <li><a class="text-white" href="https://www.facebook.com/Rede-Metrol%C3%B3gica-RS-788822964529119/"><i class="bi bi-facebook"></i></a></li>
                            <li><a class="text-white" href="https://www.instagram.com/redemetrologicars01/"><i class="bi bi-instagram"></i></a></li>
                            <li><a class="text-white" href="https://www.linkedin.com/company/redemetrologicars/"><i class="bi bi-linkedin"></i></span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</footer>
<!-- footer -->

<!-- efeito navbar -->
<script>
    window.addEventListener("scroll", function() {
        let header = document.querySelector('.SiteHeader')
        header.classList.toggle('SiteHeader--efeito', window.scrollY > 0)
    })
</script>
<!-- efeito navbar -->

@endsection