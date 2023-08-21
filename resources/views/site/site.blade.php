@extends('layouts.master-without-nav')
@extends('site.layouts.site-navbar')

<hr style="padding-top: 150px;">

{{-- banner inicial --}}
<div class="card text-bg-dark">
    <img src="{{ asset('build\images\site\BANNER-HOME-TOPO.png') }}" class="card-img" alt="...">
    <div class="card-img-overlay d-flex justify-content-center">
        <div class="align-self-center">
            <h1 class="card-title text-warning">REDE METROLÓGICA RS</h1>
            <p class="card-text">Certificada ABNT NBR ISO 9001 pela DNV</p>
            <p class="card-text">Acreditada ABNT NBR ISO/IEC 17043 pela Cgcre - PEP 0002</p>
        </div>
    </div>
</div>
{{-- banner inicial --}}

{{-- cards iniciais --}}
<div class="d-flex justfy-content-center " style="margin-top: -150px;">

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\DESTAQUES-HOME-REDE-600-x-600-px.png') }}" class="card-img"
            alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">A Rede</h1>
            </div>
        </div>
    </div>

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\DESTAQUES-HOME-ASSOCIE-SE-600-x-600-px.png') }}" class="card-img"
            alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">Associe-se</h1>
            </div>
        </div>
    </div>

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\DESTAQUES-HOME-CURSOS-600-x-600-px.png') }}" class="card-img"
            alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">Cursos e Eventos</h1>
            </div>
        </div>
    </div>

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\HOME-DESTAQUES-PEP-600-x-600-px (1).png') }}" class="card-img"
            alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">Ensaios de Proficiência</h1>
            </div>
        </div>
    </div>

    <div class="card text-bg-dark m-5">
        <img src="{{ asset('build\images\site\DESTAQUES-HOME-LABORATÓRIO-600-x-600-px.png') }}" class="card-img"
            alt="...">
        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center">
                <h1 class="card-title">Laboratórios</h1>
            </div>
        </div>
    </div>


</div>
{{-- cads iniciais --}}

{{-- bem vindo --}}
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
{{-- bem vindo --}}

{{-- pq associar --}}
<div class="d-flex justify-content-between m-5">
    <p>ASSOCIADO</p>
    <p>POR QUE SER UM ASSOCIADO</p>
</div>



<div>
    <div>

    </div>
    <div>

    </div>

</div>

{{-- pq associar --}}
<hr>
