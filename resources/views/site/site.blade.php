<x-layout-site>

    <x-slot name="content">

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

                <div class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito">
                    <img src="{{ asset('build\images\site\DESTAQUES-HOME-REDE-600-x-600-px.png') }}" class="card-img "
                        alt="...">
                    <div class="card-img-overlay d-flex justify-content-center">
                        <div class="align-self-center ">
                            <h1 class="h4 ">A Rede</h1>
                        </div>
                    </div>
                </div>


                <div class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito">
                    <img src="{{ asset('build\images\site\DESTAQUES-HOME-ASSOCIE-SE-600-x-600-px.png') }}"
                        class="card-img" alt="...">
                    <div class="card-img-overlay d-flex justify-content-center">
                        <div class="align-self-center ">
                            <h1 class="h4 ">Associe-se</h1>
                        </div>
                    </div>
                </div>

                <div class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito">
                    <img src="{{ asset('build\images\site\DESTAQUES-HOME-CURSOS-600-x-600-px.png') }}" class="card-img"
                        alt="...">
                    <div class="card-img-overlay d-flex justify-content-center">
                        <div class="align-self-center ">
                            <h1 class="h4 ">Cursos e Eventos</h1>
                        </div>
                    </div>
                </div>

                <div class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito ">
                    <img src="{{ asset('build\images\site\HOME-DESTAQUES-PEP-600-x-600-px (1).png') }}" class="card-img"
                        alt="...">
                    <div class="card-img-overlay d-flex justify-content-center">
                        <div class="align-self-center ">
                            <h1 class="h4 ">Ensaios de Proficiência</h1>
                        </div>
                    </div>
                </div>

                <div class="card bg-transparent border-0  col-sm-6 col-md SiteCardsINI--efeito">
                    <img src="{{ asset('build\images\site\DESTAQUES-HOME-LABORATÓRIO-600-x-600-px.png') }}"
                        class="card-img" alt="...">
                    <div class="card-img-overlay d-flex justify-content-center">
                        <div class="align-self-center ">
                            <h1 class="h4 ">Laboratórios</h1>
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
                            articuladora na prestação de serviços qualificados de metrologia para o aprimoramento
                            tecnológico das
                            empresas.

                            Pioneira entre as demais Redes estaduais existentes no país, desde 1992 articulamos
                            parcerias
                            indispensáveis para viabilizar a execução de suas metas.</p>
                        <button type="button" class=" mb-3 btn btn-primary">Saiba Mais</button>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <img src="{{ asset('build\images\site\HOME-BEM-VINDO-700-x-462.png') }}"
                        class="card-img img-fluid rounded " alt="...">
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
                    <button type="button" class="btn btn-warning btn-lg">Quero ser Associado</button>
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
                            <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
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
                            <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
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
                            <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
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
                            <button type="button" class="btn btn-warning btn-lg">Ver mais</button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <!-- destaques -->

        <!-- noticias -->
        <div class="container-fluid mt-5 pt-5">
            <div class=" position-relative container-fluid text-center row py-5">
                <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
                    <h1 class=" ">NOTÍCIAS</h1>
                </div>
                <div class="position-absolute top-50 start-50 translate-middle col-12 SiteTitulo--sombra ">
                    <p class="">NOTÍCIAS</p>
                </div>
            </div>

            <div class="container-fluid pb-5 mb-5" style=" height: 300px">
                <!-- Tela SM e XS -->
                <div id="carouselNoticiasControlsSMXS"
                    class="carousel carousel-dark slide d-block d-md-none d-xl-none d-lg-none d-xxl-none"
                    data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class=" container-sm d-flex  justify-content-around ">
                                <div class="card hover-shadow" style="width: 18rem; ">
                                    <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                                        <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que
                                            estão utilizando uniformes, crachás e documentos falsificados.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">
                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                                        <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta
                                            quarta-feira (3/5), uma nova norma que trata dos
                                            requisitos técnico-sanitários.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">
                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5"> O que é biotecnologia e...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul
                                            25<span>th</span>, 2023</p>
                                        <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um
                                            planeta em constante crescimento. Segundo a Organização das Nações Unidas
                                            (ONU)... </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">
                                <div class="card hover-shadow" style=" width: 18rem;">
                                    <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Em três meses, operação do...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul
                                            21<span>st</span>, 2023</p>
                                        <p class="opacity-50"> Durante 13 semanas, fiscais do Instituto Nacional de
                                            Metrologia, Qualidade e Tecnologia (Inmetro) foram às ruas em todo. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">

                                <div class="card hover-shadow" style=" width: 18rem;">
                                    <img src="{{ asset('build\images\site\CONFIRA-OS-PROXIMOS-32-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5"> CONHEÇA OS BENEFÍCIOS DOS TREINAMENTOS...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            22<span>nd</span>, 2023</p>
                                        <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na
                                            própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">

                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            16<span>th</span>, 2023 </p>
                                        <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8)
                                            a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.
                                        </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">

                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Lei Geral de Proteção de...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            10<span>th</span>, 2023 </p>
                                        <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD,
                                            tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">

                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}"
                                        class="card-img-top mb-5 pb-2" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            1<span>st</span>, 2023 /p>
                                        <p class="opacity-50"> Boletim Eletrônico - Agosto </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <button class="carousel-control-prev" type="button"
                        data-bs-target="#carouselNoticiasControlsSMXS" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button"
                        data-bs-target="#carouselNoticiasControlsSMXS" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <!-- Tela SM e XS -->

                <!-- Tela MD-->
                <div id="carouselNoticiasControlsMD"
                    class="carousel carousel-dark slide  d-none d-md-block d-xl-none d-lg-none d-xxl-none"
                    data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="card-wrapper container-sm d-flex  justify-content-around ">
                                <div class="card hover-shadow" style="width: 288px;">
                                    <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                                        <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que
                                            estão utilizando uniformes, crachás e documentos falsificados.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>


                                <div class="card hover-shadow" style="width: 288px;">
                                    <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                                        <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta
                                            quarta-feira (3/5), uma nova norma que trata dos
                                            requisitos técnico-sanitários.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="card-wrapper container-sm d-flex   justify-content-around">
                                <div class="card hover-shadow" style="width: 288px;">
                                    <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5"> O que é biotecnologia e...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul
                                            25<span>th</span>, 2023</p>
                                        <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um
                                            planeta em constante crescimento. Segundo a Organização das Nações Unidas
                                            (ONU)... </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>


                                <div class="card hover-shadow" style=" width: 288px;">
                                    <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Em três meses, operação do...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul
                                            21<span>st</span>, 2023</p>
                                        <p class="opacity-50"> Durante 13 semanas, fiscais do Instituto Nacional de
                                            Metrologia, Qualidade e Tecnologia (Inmetro) foram às ruas em todo. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="card-wrapper container-sm d-flex   justify-content-around">

                                <div class="card hover-shadow" style=" width: 288px;">
                                    <img src="{{ asset('build\images\site\CONFIRA-OS-PROXIMOS-32-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5"> CONHEÇA OS BENEFÍCIOS DOS TREINAMENTOS...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            22<span>nd</span>, 2023</p>
                                        <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na
                                            própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>


                                <div class="card hover-shadow" style="width: 288px;">
                                    <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            16<span>th</span>, 2023 </p>
                                        <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8)
                                            a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.
                                        </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="card-wrapper container-sm d-flex   justify-content-around">

                                <div class="card hover-shadow" style="width: 288px;">
                                    <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Lei Geral de Proteção de...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            10<span>th</span>, 2023 </p>
                                        <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD,
                                            tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>



                                <div class="card hover-shadow" style="width: 288px;">
                                    <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}"
                                        class="card-img-top mb-5 pb-2" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            1<span>st</span>, 2023 /p>
                                        <p class="opacity-50"> Boletim Eletrônico - Agosto </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticiasControlsMD"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticiasControlsMD"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <!-- Tela MD-->

                <!-- Tela XL e LG-->
                <div id="carouselNoticiasControlsXLLG"
                    class="carousel carousel-dark slide d-none d-xl-block d-lg-block d-xxl-none"
                    data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class=" container-sm d-flex  justify-content-around ">
                                <div class="card hover-shadow" style="width: 18rem; ">
                                    <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                                        <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que
                                            estão utilizando uniformes, crachás e documentos falsificados.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>


                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                                        <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta
                                            quarta-feira (3/5), uma nova norma que trata dos
                                            requisitos técnico-sanitários.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>


                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5"> O que é biotecnologia e...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul
                                            25<span>th</span>, 2023</p>
                                        <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um
                                            planeta em constante crescimento. Segundo a Organização das Nações Unidas
                                            (ONU)... </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">
                                <div class="card hover-shadow" style=" width: 18rem;">
                                    <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Em três meses, operação do...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul
                                            21<span>st</span>, 2023</p>
                                        <p class="opacity-50"> Durante 13 semanas, fiscais do Instituto Nacional de
                                            Metrologia, Qualidade e Tecnologia (Inmetro) foram às ruas em todo. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>



                                <div class="card hover-shadow" style=" width: 18rem;">
                                    <img src="{{ asset('build\images\site\CONFIRA-OS-PROXIMOS-32-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5"> CONHEÇA OS BENEFÍCIOS DOS TREINAMENTOS...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            22<span>nd</span>, 2023</p>
                                        <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na
                                            própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>


                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            16<span>th</span>, 2023 </p>
                                        <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8)
                                            a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.
                                        </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class=" container-sm d-flex   justify-content-around">

                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Lei Geral de Proteção de...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            10<span>th</span>, 2023 </p>
                                        <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD,
                                            tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>



                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}"
                                        class="card-img-top mb-5 pb-2" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            1<span>st</span>, 2023 /p>
                                        <p class="opacity-50"> Boletim Eletrônico - Agosto </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                                <div class="card hover-shadow" style="width: 18rem; ">
                                    <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                                        <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que
                                            estão utilizando uniformes, crachás e documentos falsificados.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <button class="carousel-control-prev" type="button"
                        data-bs-target="#carouselNoticiasControlsXLLG" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button"
                        data-bs-target="#carouselNoticiasControlsXLLG" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <!-- Tela XL e LG-->

                <!-- Telas XLL -->
                <div id="carouselNoticiasControlsXLL" class="carousel carousel-dark slide d-none d-xxl-block"
                    data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="card-wrapper container-sm d-flex  justify-content-around ">
                                <div class="card hover-shadow " style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\Novo-Projeto-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Inmetro alerta: cuidado com os...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago 1st, 2023</p>
                                        <p class="opacity-50">Recebemos informações sobre pessoas mal-intencionadas que
                                            estão utilizando uniformes, crachás e documentos falsificados.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>
                                <div class="card hover-shadow " style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\pexels-diego-romero-17515220-1-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Anvisa atualiza norma que disciplina...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul 26th, 2023</p>
                                        <p class="opacity-50"> A Diretoria Colegiada (Dicol) da Anvisa aprovou, nesta
                                            quarta-feira (3/5), uma nova norma que trata dos
                                            requisitos técnico-sanitários.</p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>
                                <div class="card hover-shadow " style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\metrologia-3-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5"> O que é biotecnologia e...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul
                                            25<span>th</span>, 2023</p>
                                        <p class="opacity-50">A agricultura tem um grande desafio:&nbsp;alimentar um
                                            planeta em constante crescimento. Segundo a Organização das Nações Unidas
                                            (ONU)... </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>
                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpeg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Em três meses, operação do...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> jul
                                            21<span>st</span>, 2023</p>
                                        <p class="opacity-50"> Durante 13 semanas, fiscais do Instituto Nacional de
                                            Metrologia, Qualidade e Tecnologia (Inmetro) foram às ruas em todo. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="card-wrapper container-sm d-flex   justify-content-around">

                                <div class="card hover-shadow" style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\CONFIRA-OS-PROXIMOS-32-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5"> CONHEÇA OS BENEFÍCIOS DOS TREINAMENTOS...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            22<span>nd</span>, 2023</p>
                                        <p class="opacity-50">Foco nos desafios internos: Ao realizar o treinamento na
                                            própria empresa, os colaboradores têm a oportunidade de abordar. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>
                                <div class="card hover-shadow " style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\medications-1851178_1280-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Anvisa cria a Câmara Técnica...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            16<span>th</span>, 2023 </p>
                                        <p class="opacity-50"> Foi&nbsp;publicada nesta segunda-feira (14/8)
                                            a&nbsp;Portaria n. 875, de 10 de agosto de 2023, que cria a Câmara Técnica.
                                        </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>
                                <div class="card hover-shadow " style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\cyber-security-3374252_1280-320x175.jpg') }}"
                                        class="card-img-top" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Lei Geral de Proteção de...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            10<span>th</span>, 2023 </p>
                                        <p class="opacity-50"> Fundamentos O tema proteção de dados pessoais, na LGPD,
                                            tem como fundamentos&nbsp;(art. 2º, LGPD): respeito à privacidade, ao. </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>
                                <div class="card hover-shadow " style="width: 18rem;">
                                    <img src="{{ asset('build\images\site\Jornal-da-Metrologia.jpg') }}"
                                        class="card-img-top mb-5 pb-2" alt="...">
                                    <div class="card-body">
                                        <p class=" bold h5">Jornal da Metrologia – Edição...</p>
                                        <p class="opacity-50"><i class="bi bi-calendar-event"></i> ago
                                            1<span>st</span>, 2023 /p>
                                        <p class="opacity-50"> Boletim Eletrônico - Agosto </p>
                                        <a href="" class=" text-black">Ler Mais <i
                                                class="fa-solid fa-circle-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="carousel-control-prev" type="button"
                        data-bs-target="#carouselNoticiasControlsXLL" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button"
                        data-bs-target="#carouselNoticiasControlsXLL" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <!-- Telas XLL -->
            </div>
        </div>
        <!-- noticias -->

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

            <!-- Tela SM e XS-->
            <div id="carouselGaleriaControlsSMXS"
                class="carousel carousel-dark slide d-block d-md-none d-xl-none d-lg-none d-xxl-none"
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
                class="carousel carousel-dark slide d-none d-md-block d-xl-none d-lg-none d-xxl-none"
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
            <div id="carouselGaleriaControlsXLLG"
                class="carousel carousel-dark slide d-none d-xl-block d-lg-block d-xxl-none" data-bs-ride="carousel">
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
        <!-- galeria de fotos -->
    </x-slot>

</x-layout-site>
