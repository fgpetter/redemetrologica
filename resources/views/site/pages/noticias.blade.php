@extends('site.layouts.layout-site')
@section('content')
    {{-- busca --}}
    <div class="container text-start my-5">
        <div class="row">
            <div class="col-md-10">
                <h5>Posts populares</h5>
                <h5>Últimos posts</h5>
            </div>
            <div class="col-md-2 ">
                <div class="btn-toolbar d-flex justify-content-end  " role="toolbar" aria-label="Toolbar with button groups">
                    <div class="input-group border-bottom pb-4">
                        <input type="text" class="form-control" placeholder="PESQUISAR POR"
                            aria-label="Input group example" aria-describedby="btnGroupAddon2">
                        <div class="input-group-text" id="btnGroupAddon2"><i class="bi bi-search"></i></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- busca --}}

    {{-- main --}}
    <div class="container my-3">
        <div class="row">
            <div class="col-md-10  border">
                {{-- noticias --}}
                <div>
                    <div class="container my-5">
                        <div class="card mb-3" style="max-width: 940px;">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="{{ asset('build\images\site\metrologia-300x280.jpg') }}" class="card-img"
                                        alt="...">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body h-100">
                                        <h3 class="card-title">Novo relatório revela diminuição global…</h3>
                                        <p class="card-text">A utilização global de antimicrobianos em animais
                                            diminuiu
                                            13%
                                            em 3 anos, marcando novamente uma mudança significativa nos esforços
                                            contínuos...</p>
                                        <div class="d-flex   ">
                                            <p class="card-text align-self-end"><small class="text-muted">Ver
                                                    mais</small> <i class="bi bi-arrow-right-circle-fill"></i></p>
                                        </div>
                                    </div>
                                    <div class="  d-flex justify-content-end ">
                                        <p class="card-text mx-1 "><small class="text-muted">Share</small>
                                            <i class="bi bi-facebook"></i>
                                        </p>
                                        <p class="card-text "><small class="text-muted">Tweet</small>
                                            <i class="bi bi-twitter"></i>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container my-5">
                        <div class="card mb-3" style="max-width: 940px;">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="{{ asset('build\images\site\METROLOGIA-1-320x175.jpg') }}" class="card-img"
                                        alt="...">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body h-100">
                                        <h3 class="card-title">Inmetro recebe Instituto de Metrolog…</h3>
                                        <p class="card-text">Entre os dias 14 e 18 de agosto, o Inmetro recebeu a
                                            visita do Instituto de Metrologia da Alemanha, PTB...</p>
                                        <div class="d-flex   ">
                                            <p class="card-text align-self-end"><small class="text-muted">Ver
                                                    mais</small> <i class="bi bi-arrow-right-circle-fill"></i></p>
                                        </div>
                                    </div>
                                    <div class="  d-flex justify-content-end ">
                                        <p class="card-text mx-1 "><small class="text-muted">Share</small>
                                            <i class="bi bi-facebook"></i>
                                        </p>
                                        <p class="card-text "><small class="text-muted">Tweet</small>
                                            <i class="bi bi-twitter"></i>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container my-5">
                        <div class="card mb-3" style="max-width: 940px;">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="{{ asset('build\images\site\horarioaberturaexpointer-300x280.jpg') }}"
                                        class="card-img" alt="...">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body h-100">
                                        <h3 class="card-title">Soluções para disseminar tecnologia entre…</h3>
                                        <p class="card-text">Oportunidades para foodtechs e utilização de
                                            inteligência artificial no agronegócio foram temas debatidos no
                                            palco do RS Innovation Agro nessa...</p>
                                        <div class="d-flex   ">
                                            <p class="card-text align-self-end"><small class="text-muted">Ver
                                                    mais</small> <i class="bi bi-arrow-right-circle-fill"></i>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="  d-flex justify-content-end">
                                        <p class="card-text mx-1 "><small class="text-muted">Share</small>
                                            <i class="bi bi-facebook"></i>
                                        </p>
                                        <p class="card-text "><small class="text-muted">Tweet</small>
                                            <i class="bi bi-twitter"></i>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="container ">
                        <div class=" d-flex justify-content-end">
                            <nav aria-label="Page navigation example" class=" align-items-end">
                                <ul class="pagination">
                                    <li class="page-item">
                                        <a class="page-link" href="#" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                {{-- noticias --}}
            </div>
            {{-- menu lateral --}}
            <div class="col-md-2 ">

                <div class="row border-bottom py-2">
                    <div class="col ">
                        <h3>Posts recentes</h3>
                        <a class="d-inline-block" href="">Lorem, ipsum .</a>
                        <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                        <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                        <a class="d-inline-block" href="">Lorem, .</a>
                        <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                        <a class="d-inline-block" href="">Lorem, dolor.</a>
                        <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                    </div>
                </div>
                <div class="row border-bottom py-2">
                    <div class="col">
                        <h3>Categorias</h3>
                        <a class="d-inline-block" href="">Lorem, ipsum .</a>
                        <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                        <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                        <a class="d-inline-block" href="">Lorem, .</a>
                        <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                        <a class="d-inline-block" href="">Lorem, dolor.</a>
                        <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                    </div>
                </div>
                <div class="col border-bottom py-2">
                    <h3>TAGS</h3>
                    <span class="my-1 badge bg-primary">lorem</span>
                    <span class="my-1 badge bg-primary ">Lorem, ipsum.</span>
                    <span class="my-1 badge bg-primary ">Lorem.</span>
                    <span class="my-1 badge bg-primary ">Lorem ipsum dolor sit.</span>
                    <span class="my-1 badge bg-primary ">Lorem, ipsum dolor.</span>
                    <span class="my-1 badge bg-primary ">Primary</span>
                    <span class="my-1 badge bg-primary ">Lorem ipsum dolor sit amet.</span>
                </div>


            </div>
            {{-- menu lateral --}}
        </div>
    </div>
    {{-- main --}}
@endsection
