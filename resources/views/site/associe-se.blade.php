<x-layout-site>

    <x-slot name="content">

        {{-- banner inicial --}}
        <div class="card text-bg-dark">
            <img src="{{ asset('build\images\site\banner-associe-se.png') }}" class="card-img" alt="...">
            <div class="container card-img-overlay d-flex row justify-content-around">
                <div class="row align-items-end mx-5 mb-5">

                    <div class="col-12 col-md-6 ">
                        <div class=" container  d-flex justify-content-center ">

                            <div class="align-self-end text-start    ">
                                <p class="SiteBanner--titulo"><strong>Associe-se</strong></p>
                                <button type="button" class="btn btn-warning">VANTAGENS</button>
                                <button type="button" class="btn btn-warning">VALORES</button>
                            </div>

                        </div>

                    </div>
                    <div class="col-12 col-md-6">

                    </div>

                </div>
            </div>

        </div>
        {{-- banner inicial --}}

        {{-- cards --}}
        <div class="container">
            <div class="container d-flex justify-content-center my-5">
                <h2>Qualquer laboratório de calibração, ensaios e/ou de análises clínicas pode ser associado</h2>
            </div>
            <div class="container my-5  ">
                <div class="row d-flex justify-content-center  align-items-start">
                    <div class="col-12 col-md-6 col-lg-3 text-center">
                        <img src="{{ asset('build\images\site\icon21.png') }}" class="card-img SiteMaxW35 mb-3 "
                            alt="...">
                        <h4>QUALIDADE</h4>
                        <p>Desde 1997 desenvolve um trabalho extremamente sério e responsável. A Rede conquistou a
                            confiança
                            dos setores público e empresarial, sendo consolidada como referência para a qualidade, o que
                            pode ser atestado pelo grande número de clientes e parceiros, além da participação de
                            instituições de 9 países em nossos projetos (Alemanha, Canadá, Estados Unidos, Inglaterra,
                            Argentina, Portugal, Uruguai, México e Paraguai).</p>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 text-center ">
                        <img src="{{ asset('build\images\site\video.png') }}" class="card-img SiteMaxW35 mb-3 "
                            alt="...">
                        <h4>CERTIFICAÇÃO E ACREDITAÇÃO</h4>
                        <p>Com intuito de garantir a confiabilidade dos serviços ofertados ao mercado, desde 1997
                            contamos a
                            certificação ABNT NBR ISO9001 em todos os nossos processos. Em 2011, ingressamos na
                            acreditação
                            de provedor de Ensaios de Proficiência e hoje somos um dos maiores provedores acreditados
                            pela
                            Cgcre do Inmetro na norma ABNT NBR ISO/IEC17043.</p>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 text-center ">
                        <img src="{{ asset('build\images\site\box2-55x55.png') }}" class="card-img SiteMaxW35 mb-3 "
                            alt="...">
                        <h4>ENSAIOS DE PROFICIÊNCIA</h4>
                        <p>Ação fundamental para a qualificação e garantia da qualidade dos laboratórios são os Ensaios
                            de
                            Proficiência por comparações interlaboratoriais. A Rede Metrológica RS é atualmente
                            reconhecida
                            pela seriedade de seus programas e pela sua abrangência, sendo acreditada para esses
                            serviços.
                            Além disso, a Rede possui um amplo cadastro de programas no sistema internacional do EPTIS
                            (European Proficiency Testing Information System).</p>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 text-center ">
                        <img src="{{ asset('build\images\site\icon322.png') }}" class="card-img SiteMaxW35 mb-3 "
                            alt="...">
                        <h4>CURSOS</h4>
                        <p>Oferecemos cursos com programação anual, disponível no site, e também podemos formatar
                            treinamentos in company, conforme interesse ou necessidade. Os valores são diferenciados
                            para
                            associados nas inscrições.</p>
                    </div>
                </div>
            </div>
        </div>
        {{-- cards --}}


        {{-- avaliações --}}
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-6 text-center my-3">
                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Valores diferenciados
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    Valores diferenciados nas inscrições em Programa de Ensaios de Proficiência (PEP).
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Divulgação
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    Para laboratórios reconhecidos, divulgação do escopo de serviços no site da Rede
                                    Metrológica RS e disponibilização do Bônus Metrologia para seus clientes.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Atendimento diferenciado
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    Receber via e-mail divulgação de PEPs, cursos e eventos realizados pela Rede
                                    Metrológica RS.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    Reputação
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    Maior credibilidade junto a clientes por seguir critérios de qualidade.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-12 col-md-12 col-lg-6 text-center my-3">
                    <h1>AVALIAÇÃO DE LABORATÓRIOS</h1>
                    <p>Quando o laboratório se considerar preparado, poderá solicitar sua avaliação por uma equipe de
                        profissionais da Rede Metrológica RS. A avaliação é rigorosa e aborda cada um dos requisitos da
                        norma NBR ISO/IEC 17025 ou NBR ISO15189 e, dessa forma, proporciona ao laboratório uma visão
                        completa da sua situação atual quanto à conformidade com a norma, bem como das ações corretivas
                        necessárias.</p>
                </div>
            </div>
        </div>
        {{-- avaliações --}}

        {{-- associe-se --}}
        <div class="container">
            <div class=" position-relative container-fluid text-center row py-5">
                <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
                    <h1 class=" ">ASSOCIE-SE</h1>
                </div>
                <div class="position-absolute top-50 start-50 translate-middle col-12 SiteTitulo--sombra ">
                    <p class="">ASSOCIE-SE</p>
                </div>
            </div>
            <div class="text-center">
                <p>Confira os critérios para laboratórios da mesma entidade.</p>
            </div>
        </div>
        {{-- associe-se --}}

        {{-- vaalores --}}

        <div class="row d-flex justify-content-center text-center align-items-center SiteValores--bg">
            <div class="col my-5">
                <div class="border">
                    <h1>R$100</h1>
                    <p>Lorem, ipsum.</p>
                </div>
            </div>
            <div class="col my-5">
                <div class="border">
                    <h1>R$100</h1>
                    <p>Lorem, ipsum.</p>
                </div>
            </div>
            <div class="col my-5">
                <div class="border">
                    <h1>R$100</h1>
                    <p>Lorem, ipsum.</p>
                </div>
            </div>

        </div>


        {{-- vaalores --}}


    </x-slot>

</x-layout-site>
