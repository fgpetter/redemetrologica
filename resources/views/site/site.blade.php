@extends('layouts.master-without-nav')

<!-- navbar -->
<div class="container" id="Navbar">

    <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom ">
        <div class="container-fluid">
            <div>
                <a class="navbar-brand  ps-5" href="#">
                    <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" width="238" height="115">
                </a>
            </div>
            <div class="d-flex justify-content-end">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse  m-5 pe-5 	text--bs-primary-text-emphasis" id="navbarSupportedContent">
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
        </div>
    </nav>
</div>
<!-- navbar -->

<!-- {{-- banner inicial --}} -->
<!-- <div class="container-fluid">
    <div class="row">
        <div class="col">

            <div class="bg-image  text-white d-grid card-img" style="background-image: url('{{ asset('build/images/site/BANNER-HOME-TOPO.png') }}');">
                <div class="align-self-center text-center">
                    <p class="text-warning h1">REDE METROLÓGICA RS</p>
                    <p class="h2 text-white">Certificada ABNT NBR ISO 9001 pela DNV</p>
                    <p class="h2 text-white">Acreditada ABNT NBR ISO/IEC 17043 pela Cgcre - PEP 0002</p>
                </div>
            </div>
        </div>
    </div>
</div> -->
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
<div class="container-fluid">

    <div class="row" style="margin-top: 0;">

        <div class="card  col-sm-6 col-md">
            <img src="{{ asset('build\images\site\DESTAQUES-HOME-REDE-600-x-600-px.png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">A Rede</h1>
                </div>
            </div>
        </div>

        <!-- teste bg-image
    <div class="card  col-sm-6 col-md">
        <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/DESTAQUES-HOME-REDE-600-x-600-px.png') }}');">
            <div class="d-grid align-items-center p-3">
                <p class="text-center h2 text-white">A Rede</p>
            </div>
        </div>
    </div> -->

        <div class="card  col-sm-6 col-md">
            <img src="{{ asset('build\images\site\DESTAQUES-HOME-ASSOCIE-SE-600-x-600-px.png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">Associe-se</h1>
                </div>
            </div>
        </div>

        <div class="card  col-sm-6 col-md">
            <img src="{{ asset('build\images\site\DESTAQUES-HOME-CURSOS-600-x-600-px.png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">Cursos e Eventos</h1>
                </div>
            </div>
        </div>

        <div class="card  col-sm-6 col-md">
            <img src="{{ asset('build\images\site\HOME-DESTAQUES-PEP-600-x-600-px (1).png') }}" class="card-img" alt="...">
            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center">
                    <h1 class="h4 text-white">Ensaios de Proficiência</h1>
                </div>
            </div>
        </div>

        <div class="card  col-sm-6 col-md">
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
<div class="row m-5">
    <div class="col-12 col-md-6">
        <div class="text-center">
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
<!-- {{-- bem vindo --}} -->

<!-- {{-- pq associar --}} -->
<div class="container titulo text-center">
    <h1 class=" ">POR QUE SER UM ASSOCIADO</h1>


    <h1 class="titulo__sombra">ASSOCIADO</h1>
</div>
<div class="container-fluid align-items-center p-5 m-5">
    <div class="row align-items-start h5">
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
    <div class="d-flex justify-content-center ">
        <button type="button" class="btn btn-warning btn-lg">Quero ser Associado</button>
    </div>
</div>
<!-- {{-- pq associar --}} -->

<!-- destaques -->

<div class="container">
    <div class="row mb-5">
        <div class="col-12 col-lg-6 mb-5 pb-4">

            <div class="text-center">
                <p class="h2 text-bold">CURSOS</p>
            </div>
            <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-BANNER-CURSOS.jpg') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>
        <div class="col-12 col-lg-6 mb-5 pb-4">

            <div class="text-center">
                <p class="h2 text-bold">AVALIAÇÃO LABORATÓRIOS</p>
            </div>
            <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/LAB-SOLICITAR-RECONHECIMENTO-1349-x-443.png') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>

        <div class="col-12 col-lg-6 mb-5 pb-4">

            <div class="text-center">
                <p class="h2 text-bold">PROGRAMAS DE ENSAIOS DE PROFICIÊNCIA</p>
            </div>
            <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-BANNER-PEPS-1349-x-443_200722062833.jpg') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>
        <div class="col-12 col-lg-6 mb-5 pb-4">

            <div class="text-center">
                <p class="h2 text-bold">LABORATÓRIOS RECONHECIDOS</p>
            </div>
            <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/LAB-RECONHECIDO-1349-x-443.png') }}'); height:100%; width:100%;">
                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>

    </div>
</div>
<!-- destaques -->

<!-- noticias -->
<div class="d-flex justify-content-between m-5">
    <p>NOTÍCIAS</p>
    <p>NOTÍCIAS</p>
</div>


<div id="carouselNoticiasControls" class="carousel carousel-dark slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="card-wrapper container-sm d-flex  justify-content-around ">
                <div class="card hover-shadow " style="width: 18rem;">
                    <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                        <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que estão utilizando uniformes, crachás e documentos falsificados, alegando serem fiscais do Instituto.</p>
                        <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                    </div>
                </div>
                <div class="card hover-shadow " style="width: 18rem;">
                    <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                        <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta quarta-feira (3/5), uma nova norma que trata dos requisitos técnico-sanitários  para o funcionamento.</p>
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

    <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticiasControls" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticiasControls" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- noticias -->

<!-- galera de fotos -->
<div class="d-flex justify-content-between m-5">
    <p>GALERIA</p>
    <p>GALERIA FOTOS</p>
</div>


<div id="carouselGaleriaControls" class="carousel carousel-dark slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class=" container-sm d-flex  justify-content-around">


                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                        <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                            <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                            <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                            <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                        <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                            <p class="text-center h3 text-white">Sistema de Gestão da</p>
                            <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                            <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                        <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                            <p class="text-center h3 text-white">FMEA – Análise</p>
                            <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                            <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                        <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
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
                    <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                        <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                            <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                            <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                            <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                        <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                            <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                            <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                            <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                        <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                            <p class="text-center h3 text-white">Sistema de Gestão da</p>
                            <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                            <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                        <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                            <p class="text-center h3 text-white">FMEA – Análise</p>
                            <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                            <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaControls" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-tarGet="#carouselGaleriaControls" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- galera de fotos -->





<hr>

<!-- testes -->
<div class="container">
    <div class="container-fluid">
        <div class="glide">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    <li class="glide__slide">

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}');">
                                <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Técnicas de Coleta e</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="glide__slide justify-content-center">

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}');">
                                <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Sistema de Gestão da</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="glide__slide justify-content-center">

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}');">
                                <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">FMEA – Análise</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="glide__slide justify-content-center">

                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}');">
                                <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">CEP – Controle</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> setembro 12<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2016</p>
                                    <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="glide__slide justify-content-center">
                        <div class="  " style="width: 18rem; height: 13rem;">
                            <div class="bg-image  text-white d-grid" style="background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}');">
                                <div class="card-galeriaf d-grid align-self-end align-items-end p-3">
                                    <p class="text-center h3 text-white">Curso de Lead Assesso</p>
                                    <p class="text-end "><i class="bi bi-calendar2-event"></i> dezembro 11<span style="font-family: &quot;Archivo Black&quot;;">th</span>, 2020 </p>
                                    <a href="" class="text-start text-white bold">Visualizar <i class="fa-solid fa-circle-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>

            <div class="glide__arrows m-5 p-5" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<">prev</button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">">next</button>
            </div>
        </div>
    </div>
</div>

<!-- colar navbar -->
<script>
    var offset = document.getElementById('Navbar').offsetTop;
    var navbar = document.getElementById('Navbar');

    window.addEventListener('scroll', function() {
        if (offset <= window.scrollY) {
            navbar.classList.add('fixar');
        } else {
            navbar.classList.remove('fixar');
        }
    });
</script>
<!-- colar navbar -->

<!-- GlideJS -->
<script src="https://cdn.jsdelivr.net/npm/@glidejs/glide"> </script>
<script>
    const config = {
        type: "carousel",
        startAt: 2,
        gap: 10,
        perView: 4,
        focusAt: 0,
        peek: {
            before: 2,
            after: 2
        },
        breakpoints: {
            1200: {
                perView: 4
            },
            992: {
                perView: 3
            },
            768: {
                perView: 2
            },
            576: {
                perView: 1
            }
        }
    };
    new Glide('.glide', config).mount()
</script>

<!-- testes -->