<div class="col-12">
    @forelse ($interlabs->groupBy('agendaInterlab.id') as $agendaGroup)
        <div class="card" id="agenda-{{ $agendaGroup->first()->agendaInterlab->id }}" wire:key="agenda-{{ $agendaGroup->first()->agendaInterlab->id }}">
            <div class="card-header">
                <h6 class="card-title pb-1">
                    {{ $agendaGroup->first()->agendaInterlab->interlab->nome }} - {{ \Carbon\Carbon::parse($agendaGroup->first()->agendaInterlab->data_inicio)->format('Y') }}
                    <span class="badge bg-secondary-subtle text-secondary ms-2">{{ $agendaGroup->first()->agendaInterlab->status }}</span>
                </h6>
            </div>

            <div class="card-body pt-0">
                @foreach ($agendaGroup->groupBy('empresa.id') as $empresaGroup)
                    <h6 class="my-3 text-primary-emphasis">Empresa: &nbsp; {{ $empresaGroup->first()->empresa->nome_razao }}</h6>

                    @foreach ($empresaGroup->groupBy('laboratorio.id') as $labGroup)
                        <div class="row" wire:key="lab-{{ $labGroup->first()->laboratorio->id }}">
                            <div class="col-6">
                                <p class="ps-1">
                                    <strong>Laboratório: &nbsp; </strong>{{ $labGroup->first()->laboratorio->nome }} <br>
                                    <strong>Responsável Técnico:</strong> {{ $labGroup->first()->responsavel_tecnico }} <br>
                                    <strong>Telefone: </strong> {{ $labGroup->first()->telefone }} <br>
                                    <strong>Email: </strong> {{ $labGroup->first()->email }} <br>
                                    <strong>Data da Inscrição:</strong> {{ \Carbon\Carbon::parse($labGroup->first()->created_at)->format('d/m/Y') }} <br>
                                    <strong>Informações de inscrição:</strong> <br>
                                    <span class="ps-1">{!! nl2br($labGroup->first()->informacoes_inscricao) !!}</span>
                                    @if ($labGroup->contains(fn ($inscrito) => $inscrito->analistas->isNotEmpty()))
                                        <br><strong>Analistas Inscritos:</strong> <br>
                                        @foreach ($labGroup as $inscrito)
                                            @foreach ($inscrito->analistas as $analista)
                                                <span class="ps-1"><strong>{{ $analista->nome }}</strong>, {{ $analista->email }}, {{ $analista->telefone }}</span> <br>
                                            @endforeach
                                        @endforeach
                                    @endif
                                </p>
                            </div>
                            <div class="col-6">
                                <strong>Endereço: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->endereco ?? '--' }} <br>
                                <strong>Complemento: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->complemento ?? '--' }} <br>
                                <strong>Bairro: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->bairro ?? '--' }} <br>
                                <strong>CEP: &nbsp; </strong>{{ $labGroup->first()->laboratorio->endereco->cep ?? '--' }} <br>
                                <strong>Cidade: &nbsp; </strong>
                                {{
                                    (
                                        $labGroup->first()->laboratorio->endereco->cidade
                                        ?? '--'
                                    )
                                    . ' / ' .
                                    (
                                        $labGroup->first()->laboratorio->endereco->uf
                                        ?? '--'
                                    )
                                }} <br>
                            </div>
                            <hr>
                        </div>
                        @foreach ($labGroup as $inscrito)
                            @php($documentos = $documentosPorInscricao[$inscrito->id] ?? [])
                            <div class="mb-3" wire:key="documentos-inscricao-{{ $inscrito->id }}">
                                <span class="small fw-semibold text-muted">Documentos da inscrição:</span>
                                <ul class="list-unstyled ms-3 mt-2 mb-0">
                                @if (!empty($inscrito->agendaInterlab->protocolo))
                                    <li class="mb-1"><strong>Protocolo:</strong> <a href="{{ asset('PROTOCOLO_INTERLAB/' . $inscrito->agendaInterlab->protocolo) }}" target="_blank" rel="noopener" class="link-primary">Acessar protocolo</a></li>
                                @endif
                                @if (!empty($documentos['carta']) || collect($documentos['analistas'] ?? [])->contains(fn ($analista) => !empty($analista['carta'])))
                                    <li class="mb-1"><strong>Senha:</strong>
                                        <ul class="list-unstyled ms-3">
                                            @if (!empty($documentos['carta']))
                                                <li><a href="{{ $documentos['carta'] }}" target="_blank" rel="noopener" class="link-primary">Carta-senha do laboratório</a></li>
                                            @endif
                                            @foreach (($documentos['analistas'] ?? []) as $analista)
                                                @if (!empty($analista['carta']))
                                                    <li><a href="{{ $analista['carta'] }}" target="_blank" rel="noopener" class="link-primary">Carta-senha — {{ $analista['nome'] }}</a></li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                                @if (!empty($documentos['certificado']) || collect($documentos['analistas'] ?? [])->contains(fn ($analista) => !empty($analista['certificado'])))
                                    <li class="mb-1"><strong>Certificado:</strong>
                                        <ul class="list-unstyled ms-3">
                                            @if (!empty($documentos['certificado']))
                                                <li><a href="{{ $documentos['certificado'] }}" target="_blank" rel="noopener" class="link-primary">Certificado do laboratório</a></li>
                                            @endif
                                            @foreach (($documentos['analistas'] ?? []) as $analista)
                                                @if (!empty($analista['certificado']))
                                                    <li><a href="{{ $analista['certificado'] }}" target="_blank" rel="noopener" class="link-primary">Certificado — {{ $analista['nome'] }}</a></li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                                @if ($agendaGroup->first()->agendaInterlab->materiais->count() > 0)
                                    <li class="mb-1"><strong>Materiais do Interlab:</strong>
                                        <ul class="list-unstyled ms-3">
                                            @foreach ($agendaGroup->first()->agendaInterlab->materiais as $material)
                                                <li><a href="{{ asset('interlab-material/' . $material->arquivo) }}" target="_blank" rel="noopener" class="link-primary">{{ $material->descricao ?: 'Material ' . $loop->iteration }}</a></li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                                </ul>
                            </div>
                        @endforeach
                    @endforeach
                @endforeach
            </div>

            <div class="card-footer bg-light">
                Para acessar mais informações sobre o interlab,
                <a href="{{ route('site-single-interlaboratorial', $agendaGroup->first()->agendaInterlab->uid) }}" class="link-primary"> clique aqui</a>
                <br>
                Caso queira voltar a tela de inscrições,
                <a href="{{ route('interlab-inscricao', ['target' => $agendaGroup->first()->agendaInterlab->uid]) }}" class="link-primary"> clique aqui</a>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                Você ainda não está inscrito em nenhum PEP ou Interlaboratorial.
            </div>
        </div>
    @endforelse
</div>
