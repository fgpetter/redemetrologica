@php
    $errorBag = isset($avaliacao) ? 'avaliacao_'.$avaliacao->uid : 'avaliacao_nova';
    $hasErrors = $errors->getBag($errorBag)->any();
    $situacao = $hasErrors ? old('situacao') : ($avaliacao->situacao ?? '');
    $situacao = $situacao === 'AVALIADOR LIDER' ? 'AVALIADOR LÍDER' : $situacao;
@endphp
{{-- modal --}}
<div class="modal fade" id="{{ isset($avaliacao) ? 'avaliacaoModal' . $avaliacao->uid : 'avaliacaoModal' }}" tabindex="-1"
    aria-labelledby="avaliacaoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avaliacaoModalLabel">Adicionar Avaliacao</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST"
                    action="{{ isset($avaliacao) ? route('avaliador-update-avaliacao', $avaliacao->uid) : route('avaliador-create-avaliacao', $avaliador->uid) }}">
                    @csrf
                    <div class="row gy-3 mb-3">
                        <div class="col-12">
                            <label for="empresa" class="form-label">Empresa</label>
                            <select  name="empresa"  id="empresa" class="form-select"  required>
                                <option value="">Selecione uma empresa</option>
                                @if (isset($empresas))
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->id }}"
                                            @selected(($hasErrors ? old('empresa') : ($avaliacao->empresa ?? null)) == $empresa->id)>
                                            {{ $empresa->cpf_cnpj }} | {{ $empresa->nome_razao }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('empresa', $errorBag)
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-8">
                            <label for="situacao" class="form-label">Situação</label>
                            <select id="situacao" name="situacao" class="form-select" required>
                                <option value="">Selecione uma situação</option>

                                @foreach (['AVALIADOR','AVALIADOR EM TREINAMENTO','AVALIADOR LÍDER','ESPECIALISTA'] as $sit)
                                    <option value="{{ $sit }}" @selected($situacao === $sit)>
                                        {{ $sit }}
                                    </option>
                                @endforeach
                            </select>

                            @error('situacao', $errorBag)
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-4">
                            <label for="data" class="form-label">Data</label>
                            <input type="date" class="form-control" name="data" id="data"
                                value="{{ $hasErrors ? old('data') : ($avaliacao->data ?? null) }}" required>
                            @error('data', $errorBag)
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        @if (isset($avaliacao))
                            @if (isset($avaliacao->agenda_avaliacao_id))
                                <div class="col-12">
                                    <p>
                                        <strong>Avaliação realizada:</strong><br>
                                        {{ $empresas->firstWhere('id', $avaliacao->empresa)->nome_razao ?? 'N/A' }} -
                                        {{ $avaliacao->data ? Carbon\Carbon::parse($avaliacao->data)->format('d/m/Y') : '' }}
                                        <br>
                                        <a href="{{ route('avaliacao-insert', $avaliacao->agendaAvaliacao->uid ) }}">
                                            Mais informações </a>
                                    </p>
                                </div>
                            @else
                                <div class="col-12">
                                    <p>
                                        <strong>Adicionado manualmente por:</strong><br>
                                        {{ $avaliacao->inserido_por ?? 'Não informado' }} -
                                        {{ \Carbon\Carbon::parse($avaliacao->updated_at)->format('d/m/Y') }} <br>
                                    </p>
                                </div>
                            @endif
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        @if (! isset($avaliacao->agenda_avaliacao_id))
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@if ($hasErrors)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            bootstrap.Tab.getOrCreateInstance(document.querySelector('a[href="#avaliacoes"]')).show();
            bootstrap.Modal.getOrCreateInstance(document.getElementById(@js(isset($avaliacao) ? 'avaliacaoModal'.$avaliacao->uid : 'avaliacaoModal'))).show();
        });
    </script>
@endif
{{-- endmodal --}}
