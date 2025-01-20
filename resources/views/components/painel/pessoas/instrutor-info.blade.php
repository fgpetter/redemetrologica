<div class="card">
    <div class="card-header d-flex align-items-center gap-3">
        <div class="avatar-sm">
            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                <i class="ph-graduation-cap-light"></i>
            </span>
        </div>
        <h4 class="card-title mb-0">Dados de Instrutor</h4>
    </div><!-- end card header -->
    <div class="card-body">
        <div>
            <strong>Status: </strong>{{ $pessoa->instrutor->situacao ? 'Ativo' : 'Inativo' }}
        </div>

        <h6 class="h6 mt-3">Cursos habilitados:</h6>
        @foreach ($pessoa->instrutor->cursosHabilitados as $curso)
            <div class="mb-3 border border-dashed rounded px-2 pt-3">
                <h6 class="h6 mb-1">{{ $curso->curso->descricao }}</h6>
                <ul class="list-unstyled">
                    <li> <strong>Conhecimento: </strong> {{ $curso->conhecimento ? 'Sim' : 'Não' }}</li>
                    <li> <strong>Experiencia: </strong> {{ $curso->experiencia ? 'Sim' : 'Não' }}</li>
                    <li> <strong>Habilitado: </strong> {{ $curso->habilitado ? 'Sim' : 'Não' }}</li>
                </ul>
            </div>
        @endforeach
    </div>
</div>