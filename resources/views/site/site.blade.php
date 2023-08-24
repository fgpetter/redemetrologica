@extends('layouts.master-without-nav')
@extends('site.layouts.site-navbar')


<!-- {{-- banner inicial --}} -->
<div class="card text-bg-dark">
    <img src="{{ asset('build\images\site\BANNER-HOME-TOPO.png') }}" class="card-img" alt="...">
    <div class="card-img-overlay d-flex justify-content-center">
        <div class="align-self-center">
            <p class="card-title text-warning h1">REDE METROLÓGICA RS</p>
            <p class="card-text">Certificada ABNT NBR ISO 9001 pela DNV</p>
            <p class="card-text">Acreditada ABNT NBR ISO/IEC 17043 pela Cgcre - PEP 0002</p>
        </div>
    </div>
</div>
<!-- {{-- banner inicial --}} -->


<!-- {{-- cards iniciais --}} -->
<div class="d-flex justfy-content-center " style="margin-top: -150px;">

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\DESTAQUES-HOME-REDE-600-x-600-px.png') }}" class="card-img" alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">A Rede</h1>
            </div>
        </div>
    </div>

    <div class="div a"></div>
    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\DESTAQUES-HOME-ASSOCIE-SE-600-x-600-px.png') }}" class="card-img" alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">Associe-se</h1>
            </div>
        </div>
    </div>

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\DESTAQUES-HOME-CURSOS-600-x-600-px.png') }}" class="card-img" alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">Cursos e Eventos</h1>
            </div>
        </div>
    </div>

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\HOME-DESTAQUES-PEP-600-x-600-px (1).png') }}" class="card-img" alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">Ensaios de Proficiência</h1>
            </div>
        </div>
    </div>

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\DESTAQUES-HOME-LABORATÓRIO-600-x-600-px.png') }}" class="card-img" alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">Laboratórios</h1>
            </div>
        </div>
    </div>


</div>
<!-- {{-- cads iniciais --}} -->

<!-- {{-- bem vindo --}} -->
<div class="d-flex justify-content-center m-5">
    <div class="col-6">
        <div class="text-center">
            <h1>BEM-VINDO À REDE METROLÓGICA RS</h1>
            <p>Somos uma associação técnica, de cunho técnico-científico, sem fins lucrativos e atuamos como
                articuladora na prestação de serviços qualificados de metrologia para o aprimoramento tecnológico das
                empresas.

                Pioneira entre as demais Redes estaduais existentes no país, desde 1992 articulamos parcerias
                indispensáveis para viabilizar a execução de suas metas.</p>
            <button type="button" class="btn btn-primary">Saiba Mais</button>
        </div>
    </div>


    <div class="col-6">
        <img src="{{ asset('build\images\site\HOME-BEM-VINDO-700-x-462.png') }}" class="card-img" alt="...">
    </div>

</div>
<!-- {{-- bem vindo --}} -->

<!-- {{-- pq associar --}} -->
<div class="d-flex justify-content-between m-5">
    <p>ASSOCIADO</p>
    <p>POR QUE SER UM ASSOCIADO</p>
</div>

<div class="container-fluid align-items-center p-5 m-5">
    <div class="row align-items-start h5">
        <div class="col">
            <p> <i class="fa-solid fa-circle-check"></i> Valores diferenciados nas inscrições de eventos, treinamentos abertos e in company</p>
            <p> <i class="fa-solid fa-circle-check"></i> Valores diferenciados nas inscrições em Programa de Ensaios de Proficiência (PEP).</p>
            <p> <i class="fa-solid fa-circle-check"></i> Poder divulgar sua condição de membro da RMRS conforme regras vigentes.</p>
            <p> <i class="fa-solid fa-circle-check"></i> Para laboratórios reconhecidos, divulgação do escopo de serviços no site da Rede Metrológica RS e disponibilização do Bônus Metrologia para seus clientes.</p>
        </div>
        <div class="col">
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
        <div class="col">

            <div class="d-flex justify-content-center">
                <p class="h2 text-bold">CURSOS</p>
            </div>
            <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-BANNER-CURSOS.jpg') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>
        <div class="col">

            <div class="d-flex justify-content-center">
                <p class="h2 text-bold">AVALIAÇÃO LABORATÓRIOS</p>
            </div>
            <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/LAB-SOLICITAR-RECONHECIMENTO-1349-x-443.png') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>
    </div>
    <div class="row mb-5">
        <div class="col">

            <div class="d-flex justify-content-center">
                <p class="h2 text-bold">PROGRAMAS DE ENSAIOS DE PROFICIÊNCIA</p>
            </div>
            <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-BANNER-PEPS-1349-x-443_200722062833.jpg') }}');height:100%; width:100%;">

                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                    <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                </div>
            </div>

        </div>
        <div class="col">
            <div class="d-flex justify-content-center">
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



<!-- card carrousel -->

<div id="carouselNoticiasControls" class="carousel carousel-dark slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="card-wrapper container-sm d-flex  justify-content-around ">
                <div class="card hover-shadow " style="width: 18rem;">
                    <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                        <p class="opacity-50">ago 1st, 2023</p>
                        <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que estão utilizando uniformes, crachás e documentos falsificados, alegando serem fiscais do Instituto.</p>
                        <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                    </div>
                </div>
                <div class="card hover-shadow " style="width: 18rem;">
                    <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                        <p class="opacity-50">jul 26th, 2023</p>
                        <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta quarta-feira (3/5), uma nova norma que trata dos requisitos técnico-sanitários  para o funcionamento.</p>
                        <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                    </div>
                </div>
                <div class="card hover-shadow " style="width: 18rem;">
                    <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class=" bold h5"> O que é biotecnologia e...</p>
                        <p class="opacity-50">jul 25<span>th</span>, 2023</p>
                        <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um planeta em constante crescimento. Segundo a Organização das Nações Unidas (ONU)... </p>
                        <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                    </div>
                </div>
                <div class="card hover-shadow" style="width: 18rem;">
                    <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class=" bold h5">Em três meses, operação do...</p>
                        <p class="opacity-50">jul 21<span>st</span>, 2023</p>
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
                        <p class="opacity-50">ago 22<span>nd</span>, 2023</p>
                        <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                        <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                    </div>
                </div>
                <div class="card hover-shadow " style="width: 18rem;">
                    <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                        <p class="opacity-50">ago 16<span>th</span>, 2023 </p>
                        <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8) a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.</p>
                        <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                    </div>
                </div>
                <div class="card hover-shadow " style="width: 18rem;">
                    <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <p class=" bold h5">Lei Geral de Proteção de...</p>
                        <p class="opacity-50">ago 10<span>th</span>, 2023 </p>
                        <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD, tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                        <a href="" class=" text-black">Ler Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                    </div>
                </div>
                <div class="card hover-shadow " style="width: 18rem;">
                    <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}" class="card-img-top mb-5 pb-2" alt="...">
                    <div class="card-body">
                        <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                        <p class="opacity-50">ago 1<span>st</span>, 2023 /p>
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
<!-- card carrousel -->
<!-- noticias -->

<!-- galera de fotos -->
<div class="d-flex justify-content-between m-5">
    <p>NOTÍCIAS</p>
    <p>NOTÍCIAS</p>
</div>


<div id="carouselGaleriaControls" class="carousel carousel-dark slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class=" container-sm d-flex  justify-content-around">

                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}'); height:100%; width:100%;">
                        <div class="position-absolute bottom-0 start-0 mb-3 border text-start">
                            <p class="border">teste</p>
                            <p class="border">teste</p>
                            <a href="" class="border">Visualizar<i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}'); height:100%; width:100%;">
                        <div class="position-absolute bottom-0 start-10 translate-middle-x mb-3">
                            <p class="a">teste</p>
                            <p class="a">teste</p>
                            <a href="" class="a">Visualizar<i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}'); height:100%; width:100%;">
                        <div class="position-absolute bottom-0 start-10 translate-middle-x mb-3">
                            <p class="a">teste</p>
                            <p class="a">teste</p>
                            <a href="" class="a">Visualizar<i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_FMEA-Análise-de-Modo-e-Efeitos-de-Falha-Potencial-370x225.png') }}'); height:100%; width:100%;">
                        <div class="position-absolute bottom-0 start-10 translate-middle-x mb-3">
                            <p class="a">teste</p>
                            <p class="a">teste</p>
                            <a href="" class="a">Visualizar<i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        <div class="carousel-item">
            <div class="container-sm d-flex   justify-content-around">

                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_CEP-Controle-Estatístico-de-Processo-O-uso-das-Cartas-de-Controle-370x225.png') }}'); height:100%; width:100%;">
                        <div class="position-absolute bottom-0 start-10 translate-middle-x mb-3">
                            <p class="a">teste</p>
                            <p class="a">teste</p>
                            <a href="" class="a">Visualizar<i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/PESSOAS-FOTOS.jpg') }}'); height:100%; width:100%;">
                        <div class="position-absolute bottom-0 start-10 translate-middle-x mb-3">
                            <p class="a">teste</p>
                            <p class="a">teste</p>
                            <a href="" class="a">Visualizar<i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Técnicas-de-Coleta-e-Preservação-370x225.png') }}'); height:100%; width:100%;">
                        <div class="position-absolute bottom-0 start-10 translate-middle-x mb-3">
                            <p class="a">teste</p>
                            <p class="a">teste</p>
                            <a href="" class="a">Visualizar<i class="fa-solid fa-circle-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="  " style="width: 18rem; height: 13rem;">
                    <div class="bg-image p-5 text-center mb-5 text-white position-relative" style="background-size: cover; background-image: url('{{ asset('build/images/site/HOME-CURSOS-1920-X-1080px_Sistema-de-Gestão-da-Qualidade-para-Laboratórios-370x225.png') }}'); height:100%; width:100%;">
                        <div class="position-absolute bottom-0 start-10 translate-middle-x mb-3">
                            <p class="a">teste</p>
                            <p class="a">teste</p>
                            <a href="" class="a">Visualizar<i class="fa-solid fa-circle-chevron-right"></i></a>
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