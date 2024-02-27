@extends('site.layouts.layout-site')
@section('title')
    Cursos
@endsection
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

    {{-- cardCursos agendados --}}
    <div class="container row justify-content-center">
        @foreach ($agendacursos as $agendacurso)
            <div class="col-3 mt-4" style="width: 18rem; height: 20rem;">
                <div class="SiteCards__bgimage text-white d-grid"
                    style="background-image: url('{{ $agendacurso->curso->thumb ? asset($agendacurso->curso->thumb) : asset('build/images/site/cursos-placeholder.jpg') }}');">
                    <div class="SiteCards--efeito  align-self-end d-grid align-self-end align-items-end p-3">
                        <a href="{{ route('curso-agendados-show', $agendacurso->uid) }}"
                            class=" align-self-center text-center h5 text-white SiteCards__descricao" style="height: 100%;">
                            {{ $agendacurso->curso->descricao }}
                        </a>
                        <a href="{{ route('curso-agendados-show', $agendacurso->uid) }}" class="text-start text-white bold">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            {{ Carbon\Carbon::parse($agendacurso->data_inicio)->format('d/m/Y') }}
                        </a>
                    </div>

                </div>
            </div>
        @endforeach
    </div>




    {{-- cardCursos --}}
@endsection
