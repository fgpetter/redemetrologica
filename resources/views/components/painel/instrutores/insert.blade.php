@props([
    'instrutor' => null,
    'cursos' => null,
    'cursoshabilitados' => null
])

@if (session('instrutor-error'))
    <div class="alert alert-danger"> {{ session('error') }} </div>
@endif

<div class="card">
    <div class="card-body">

        <!-- Nav tabs -->
        <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab" aria-selected="true">
                    Dados Principais
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#documentos" role="tab" aria-selected="false">
                    Cursos Habilitados
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#endereco" role="tab" aria-selected="false">
                    Cursos Realizados
                </a>
            </li>

        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Dados principais -->
                <x-painel.instrutores.form-principal :instrutor="$instrutor" />
            </div>

            <div class="tab-pane" id="documentos" role="tabpanel"> <!-- Cursos habilitados -->
                <div class="row gy-3">                    
                    <x-painel.instrutores.list-cursos-habilitados :instrutor="$instrutor" :cursoshabilitados="$cursoshabilitados" :cursos="$cursos"/>
                </div>
            </div>

            <div class="tab-pane" id="endereco" role="tabpanel"> <!-- Cursos Realizado -->
                <div class="row gy-3">

                </div>
            </div>

        </div>
    </div>
</div>
