@props(['cursos'])

@php
    // Separar inscrições individuais (CPF) e por empresa (CNPJ)
    $inscricoesIndividuais = $cursos->whereNull('empresa_id');
    $inscricoesEmpresa = $cursos->whereNotNull('empresa_id');
    
    // Agrupar inscrições por empresa por agenda_curso_id e empresa_id
    $inscricoesAgrupadasPorCurso = $inscricoesEmpresa->groupBy(function($inscricao) {
        return $inscricao->agenda_curso_id . '_' . $inscricao->empresa_id;
    });
@endphp

{{-- Inscrições Individuais (CPF) --}}
@if ($inscricoesIndividuais->isNotEmpty())
    <div class="col-12 col-xxl-6 col-xl-8">
        <div class="card">
            <div class="card-body lh-lg">
                <h5 class="h5 mb-3">Você está inscrito nos seguintes cursos:</h5>
                <ul class="list-group">
                    @foreach ($inscricoesIndividuais as $inscricao)
                        <li class="list-group-item">
                            <strong>Nome:</strong>
                            {{ $inscricao->agendaCurso->curso->descricao }} <br>

                            <strong>Inscrito:</strong>
                            {{ $inscricao->nome }} ({{ $inscricao->email }}) <br>

                            <strong>Período:</strong>
                            de
                            @if ($inscricao->agendaCurso->data_inicio)
                                {{ \Carbon\Carbon::parse($inscricao->agendaCurso->data_inicio)->format('d/m/Y') }}
                            @endif
                            até
                            @if ($inscricao->agendaCurso->data_fim)
                                {{ \Carbon\Carbon::parse($inscricao->agendaCurso->data_fim)->format('d/m/Y') }}
                            @endif
                            {{ $inscricao->agendaCurso->horario }} <br>

                            <strong>Status do agendamento:</strong>
                            {{ $inscricao->agendaCurso->status }} <br>

                            <strong>Local:</strong>
                            {{ $inscricao->agendaCurso->endereco_local }} <br>

                            @if ($inscricao->agendaCurso->cursoMateriais->count() > 0 && $inscricao->agendaCurso->status === 'CONFIRMADO')
                                <strong>Materiais do curso:</strong>
                                <ul class="list-unstyled ms-3 mt-2">
                                    @foreach ($inscricao->agendaCurso->cursoMateriais as $material)
                                        <li class="mb-1">
                                            <i class="bx bx-file me-1"></i>
                                            <a href="{{ asset('storage/' . $material->arquivo) }}"
                                                target="_blank" class="text-primary">
                                                {{ $material->descricao ?: 'Material ' . $loop->iteration }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

{{-- Inscrições por Empresa (CNPJ) --}}
@if ($inscricoesAgrupadasPorCurso->isNotEmpty())
    <div class="col-12 col-xxl-6 col-xl-8 mt-4">
        <div class="card">
            <div class="card-body lh-lg">
                <h5 class="h5 mb-3">Cursos inscritos por você:</h5>
                <ul class="list-group">
                    @foreach ($inscricoesAgrupadasPorCurso as $grupo)
                        @php
                            $primeiraInscricao = $grupo->first();
                        @endphp
                        <li class="list-group-item">
                            <strong>Nome:</strong>
                            {{ $primeiraInscricao->agendaCurso->curso->descricao }} <br>

                            <strong>Período:</strong>
                            de
                            @if ($primeiraInscricao->agendaCurso->data_inicio)
                                {{ \Carbon\Carbon::parse($primeiraInscricao->agendaCurso->data_inicio)->format('d/m/Y') }}
                            @endif
                            até
                            @if ($primeiraInscricao->agendaCurso->data_fim)
                                {{ \Carbon\Carbon::parse($primeiraInscricao->agendaCurso->data_fim)->format('d/m/Y') }}
                            @endif
                            {{ $primeiraInscricao->agendaCurso->horario }} <br>

                            <strong>Status do agendamento:</strong>
                            {{ $primeiraInscricao->agendaCurso->status }} <br>

                            <strong>Local:</strong>
                            {{ $primeiraInscricao->agendaCurso->endereco_local }} <br>

                            <strong>Empresa vinculada:</strong>
                            {{ $primeiraInscricao->empresa->nome_razao }} <br>

                            @if ($primeiraInscricao->agendaCurso->cursoMateriais->count() > 0 && $primeiraInscricao->agendaCurso->status === 'CONFIRMADO')
                                <strong>Materiais do curso:</strong>
                                <ul class="list-unstyled ms-3 mt-2">
                                    @foreach ($primeiraInscricao->agendaCurso->cursoMateriais as $material)
                                        <li class="mb-1">
                                            <i class="bx bx-file me-1"></i>
                                            <a href="{{ asset('storage/' . $material->arquivo) }}"
                                                target="_blank" class="text-primary">
                                                {{ $material->descricao ?: 'Material ' . $loop->iteration }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <strong>Inscritos:</strong>
                            <ul class="list-unstyled ms-3 mt-2">
                                @foreach ($grupo as $inscricao)
                                    <li>
                                        <i class="bx bx-user me-1"></i>
                                        {{ $inscricao->nome }} ({{ $inscricao->email }})
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

