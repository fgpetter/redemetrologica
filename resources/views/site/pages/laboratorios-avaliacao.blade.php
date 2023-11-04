@extends('site.layouts.layout-site')
@section('content')
    <div class="container my-5 ">
        <div class="row justify-content-center align-items-center text-center my-5">
            <div class="col-md-6  ">

                <img src="{{ asset('build\images\site\capa_avaliacao_lab.png') }}" class="img-fluid" alt="...">

            </div>
            <div class="col-md-6">
                <h2>AVALIAÇÃO DE LABORATÓRIOS</h2>
                <p></p>O reconhecimento formal da competência técnica de um laboratório lhe qualifica para realizar
                ensaios ou calibrações, tendo seu sistema de gestão da qualidade estruturado de acordo com os
                requisitos da norma NBR ISO/IEC 17025 ou NBR ISO 15189 (laboratórios clínicos).
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row justify-content-center align-items-center text-center">
            <h1>PRÉ-REQUISITOS PARA SOLICITAR O RECONHECIMENTO</h1>
            <p>O laboratório interessado em obter reconhecimento pela Rede Metrológica RS nas áreas de calibração
                e/ou
                ensaios, além de já estar associado à Rede Metrológica (conforme RM 49), deve preencher um dos
                pré-requisitos:

                a) Atender aos requisitos de reconhecimento/manutenção do reconhecimento descritos no documento
                RM02.
                b) Possuir acreditação (reconhecimento formal) por outro organismo aceito pela Rede Metrológica RS
                (critérios para o reconhecimento estão descritos no documento RM 44).</p>
        </div>
    </div>

    <div class="SiteCards__bgimage p-5 text-center mb-5 text-white "
        style="background-size: cover; background-image: url('{{ asset('build/images/site/lab-banner-iso.png') }}');height:100%; width:100%;">
        <div class="container">
            <h1 class="text-white">O processo de avaliação de laboratórios é realizado com base na norma NBR ISO/IEC
                17025 ou NBR ISO 15189.</h1>
            <div class="my-2">
                <button type="button" class="btn btn-warning btn-lg">SOLICITAR</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="lc-block">

                    <h1 class=" mb-0"><b>TIPOS DE AVALIAÇÃO</b></h1>
                </div>

            </div>
        </div>
        <div class="row pt-4 pb-4 g-0">
            <div class="col-md-4 text-center">
                <div class="lc-block p-4">
                    <div>
                        <div class="SiteCards__titulo ">
                            <h3 class="mb-4 mt-3">
                                <strong>VISITA PRELIMINAR</strong>
                            </h3>
                        </div>
                        <img src="{{ asset('build\images\site\visita-preliminar.png') }}" class="card-img  mb-3 "
                            alt="...">
                    </div>
                    <div class="d-flex text-justify">
                        <p>A visita preliminar de avaliação tem como objetivo estabelecer o primeiro contato da Rede
                            Metrológica RS com o laboratório associado interessado no reconhecimento e é realizada
                            por um representante da secretaria executiva ou por um avaliador designado. Não possui
                            caráter formal de auditoria. O laboratório associado poderá solicitar visitas
                            preliminares para adaptar-se aos critérios exigidos pela Rede Metrológica RS. As mesmas
                            são realizadas SEM levantamento de evidências objetivas. Para tanto, basta contatar a
                            Secretaria Executiva, que, de comum acordo com o laboratório, agendará datas para a
                            realização da mesma.</p>
                    </div>
                </div>

            </div>
            <div class="col-md-4 text-center shadow">
                <div class="lc-block p-4">
                    <div>
                        <div class="SiteCards__titulo  ">
                            <h3 class="mb-4 mt-3">
                                <strong>AVALIAÇÃO PRELIMINAR | AUDITORIA INTERNA</strong>
                            </h3>
                        </div>
                        <img src="{{ asset('build\images\site\auditoria-interna.png') }}" class="card-img  mb-3 "
                            alt="...">
                    </div>
                    <div>
                        <p>O Laboratório pode solicitar uma avaliação preliminar de seu sistema da qualidade para a
                            Rede Metrológica RS. Esta avaliação é realizada com o levantamento de evidências
                            objetivas de atendimento a NBR ISO/IEC 17025 ou NBR ISO 15189. Neste momento é elaborado
                            um relatório com as Não-Conformidades detectadas, oportunidades de melhoria e
                            observações. Esta etapa não é válida como avaliação de Reconhecimento de Competências.
                            Muitos laboratórios acreditados solicitam este serviço para utilizarem como uma
                            auditoria interna de seu sistema de gestão.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="lc-block p-4">
                    <div>
                        <div class="SiteCards__titulo ">
                            <h3 class="mb-4 mt-3">
                                <strong>AVALIAÇÃO PARA RECONHECIMENTO DE COMPETÊNCIA TÉCNICA</strong>
                            </h3>
                        </div>
                        <img src="{{ asset('build\images\site\competencia-tecnica.png') }}" class="card-img  mb-3 "
                            alt="...">
                    </div>
                    <div class=" text-justify">
                        <p class=" text-justify">O processo de reconhecimento e manutenção do reconhecimento de
                            laboratórios subdivide-se
                            em duas etapas: avaliação de adequação e avaliação de conformidade. A avaliação de
                            adequação é basicamente documental. Neste momento são avaliados alguns documentos e
                            registros do laboratório para verificar se o mesmo está apto a receber a avaliação
                            incial (de conformidade). A avaliação inicial é realizada na sede do laboratório, quando
                            serão acompanhados os ensaios e/ou calibrações do laboratórios postulante ao
                            reconhecimento. Caso sejam atendidos os critérios estipulados pela Rede, o laboratórios
                            é reconhecido.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="SiteCards__bgimage p-5 text-center mb-5 text-white "
        style="background-size: cover; background-image: url('{{ asset('build/images/site/lab-reconhecido-02.png') }}');height:100%; width:100%;">
        <div class="container d-flex  justify-content-center">
            <div class="row align-items-center">

                <div class="col">


                    <div class="row text-center mb-3   ">
                        <h3 class="text-white"><strong>PARA MAIS INFORMAÇÕES OU PARA SE
                                ASSOCIAR</strong></h3>
                    </div>
                    <div class="row justify-content-center text-center">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning">Formulário</button>
                        </div>
                        <div class="col-md-4">
                            <p>ou</p>
                        </div>
                        <div class="col-md-4 text-nowrap">
                            <p>Ligue(51) 2200-3988<br> Email avaliacao@redemetrologica.com.br</p>

                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    <div class="container my-5">
        <div class="row text-center">
            <div class="col">
                <h1 class=" mb-0"><b>Para Download de Procedimentos dessa área Clique Aqui</b></h1>
            </div>
        </div>
    </div>

    <div class="SiteCards__bgimage p-5 text-center mb-5 text-white "
        style="background-size: cover; background-image: url('{{ asset('build/images/site/lab-reconhecido-01.png') }}');height:100%; width:100%;">
        <div class="container">
            <h1 class="text-white"><b>LABORATÓRIOS RECONHECIDOS</b></h1>
            <div class="my-2">
                <button type="button" class="btn btn-warning btn-lg">CONFIRA</button>
            </div>
        </div>
    </div>
@endsection
