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
                <strong>Email:</strong> {{ $participante->laboratorio->email ?? null }} <br>
                <strong>Telefone:</strong> {{ $participante->laboratorio->telefone ?? null }}
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
                {{-- Ao clicar em Editar ou Substituir, perguntar ao usuário a ação desejada --}}
                <a href="#" class="link-primary fw-medium" onclick="event.preventDefault(); showResponsavelActionModal('{{ $participante->uid }}');">
                  Editar / Substituir Responsável
                  <i class="ri-arrow-right-line align-middle"></i>
                </a>
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

<!-- Modal de ação para Editar/Substituir Responsável -->
<div class="modal fade" id="responsavelActionModal-{{ $participante->uid }}" tabindex="-1" aria-labelledby="responsavelActionModalLabel-{{ $participante->uid }}" aria-hidden="true">
  <div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
    <h5 class="modal-title" id="responsavelActionModalLabel-{{ $participante->uid }}">Escolha a ação</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
    </div>
    <div class="modal-body">
    O que deseja fazer com o responsável?
    </div>
    <div class="modal-footer">
    <a href="{{ route('pessoa-insert', $participante->pessoa->uid) }}" class="btn btn-primary">
      Editar Responsável
    </a>
    <button type="button" class="btn btn-warning" onclick="substituirResponsavel('{{ $participante->uid }}')">
      Substituir Responsável
    </button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    </div>
  </div>
  </div>
</div>

<!-- Troca Resp Modal -->
<div class="modal fade" id="trocaRespModal-{{ $participante->uid }}" tabindex="-1" aria-labelledby="trocaRespModalLabel-{{ $participante->uid }}" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <form wire:submit.prevent="atualizarResponsavel('{{ $participante->id }}', document.getElementById('novo_responsavel_id-{{ $participante->uid }}').value)">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="trocaRespModalLabel-{{ $participante->uid }}">Substituir Responsável</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-12">
              <p>Selecione o novo responsável para a inscrição de <strong>{{ $participante->laboratorio->nome }}</strong>.</p>
              <label for="novo_responsavel_id-{{ $participante->uid }}" class="form-label">Novo Responsável</label>
              <select class="form-select" id="novo_responsavel_id-{{ $participante->uid }}" name="novo_responsavel_id" required data-choices>
                <option value="">Selecione...</option>
                @foreach($pessoas as $pessoa)
                <option value="{{ $pessoa->id }}">{{ $pessoa->nome_razao }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
      
    </div>
  </div>
</div>

<script>
  function showResponsavelActionModal(uid) {
    // Hide the main participanteModal
    var participanteModal = bootstrap.Modal.getInstance(document.getElementById('participanteModal' + uid));
    if (participanteModal) {
      participanteModal.hide();
    }

    var modal = new bootstrap.Modal(document.getElementById('responsavelActionModal-' + uid));
    modal.show();
  }

  function substituirResponsavel(uid) {
    // Hide the responsavelActionModal
    var responsavelActionModal = bootstrap.Modal.getInstance(document.getElementById('responsavelActionModal-' + uid));
    if (responsavelActionModal) {
      responsavelActionModal.hide();
    }

    // Hide the main participanteModal
    var participanteModal = bootstrap.Modal.getInstance(document.getElementById('participanteModal' + uid));
    if (participanteModal) {
      participanteModal.hide();
    }

    // Show the target modal (trocaRespModal)
    var trocaRespModal = new bootstrap.Modal(document.getElementById('trocaRespModal-' + uid));
    trocaRespModal.show();
  }

  // Listen for when trocaRespModal is hidden to re-show participanteModal
  document.getElementById('trocaRespModal-{{ $participante->uid }}').addEventListener('hidden.bs.modal', function () {
    var participanteModal = new bootstrap.Modal(document.getElementById('participanteModal' + '{{ $participante->uid }}'));
    participanteModal.show();
  });
</script>
