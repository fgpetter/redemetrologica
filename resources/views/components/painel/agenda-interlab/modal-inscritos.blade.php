@props(['participante', 'agendainterlab', 'pessoas'])
<div class="modal fade" id="{{ 'participanteModal'.$participante->uid }}" 
  tabindex="-1" aria-labelledby="participanteModalLabel">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="participanteModalLabel"> Editar Participante </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>Empresa:</strong> {{ $participante->empresa->nome_razao ?? null }} <br>
                <strong>Email:</strong> {{ $participante->email ?? null }} <br>
                <strong>Telefone:</strong> {{ $participante->telefone ?? null }}
              </div>
              <div class="text-end">
                @if ($participante->empresa->deleted_at !== null)
                  <span class="text-secondary">Empresa excluída, somente leitura</span>
                @else
                  <a href="{{ route('pessoa-insert', $participante->empresa->uid) }}" class="link-primary fw-medium">
                    Editar Empresa 
                    <i class="ri-arrow-right-line align-middle"></i>
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>Inscrito por:</strong> {{ $participante->pessoa->nome_razao }} <br>
                <strong>Email:</strong> {{ $participante->pessoa->email ?? 'N/A' }}
              </div>
              <div class="text-end">
                @if ($participante->pessoa->deleted_at !== null)
                  <span class="text-secondary">Pessoa excluída, somente leitura</span>
                @else
                  {{-- Editar ou Substituir Responsável --}}
                  <div class="d-flex flex-column align-items-end">
                    <a href="{{ route('pessoa-insert', $participante->pessoa->uid) }}" class="link-primary fw-medium">
                      Editar Responsável
                      <i class="ri-arrow-right-line align-middle"></i>
                    </a>
                    <a href="javascript:void(0);" class="link-primary fw-medium mb-1" onclick="$('#{{ 'participanteModal'.$participante->uid }}').modal('hide'); Livewire.dispatch('showSubstituirResponsavelModal', { interlabInscritoId: {{ $participante->id }} })">
                      Substituir Responsável
                      <i class="ri-arrow-right-line align-middle"></i>
                    </a>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <form method="POST" action="{{ route('salvar-inscrito-interlab', $participante->uid) }}" id="form-{{ $participante->uid }}">
            @csrf
            <input type="hidden" name="agenda_interlab_id" value="{{ $agendainterlab->id }}">
            <div class="row">

              <div class="col-4 py-2">
                <label for="valor-{{ $participante->uid }}" class="form-label">Valor</label>
                <input type="text" name="valor"  id="valor-{{ $participante->uid }}"  class="form-control money" value="{{ old('valor') ?? (isset($participante->valor) ? number_format($participante->valor, 2, ',', '') : null) }}">
                @error('valor') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-12 py-2">
                <x-forms.input-textarea name="informacoes_inscricao" label="Informações do inscrito"
                >{{ old('informacoes_inscricao') ?? ($participante->informacoes_inscricao ?? null)}}
              </x-forms.input-textarea>
              </div>

            </div>
            <div class="modal-footer my-2">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

