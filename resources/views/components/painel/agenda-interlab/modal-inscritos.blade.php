<div class="modal fade" id="{{ 'participanteModal'.$participante->uid }}" 
  tabindex="-1" aria-labelledby="participanteModalLabel" >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="participanteModalLabel"> Editar Participante </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-8">
            <h6 class="card-title mb-0">{{ $participante->laboratorio->nome ?? null }}</h6>
            <p class="card-text">
              <strong>Empresa:</strong> {{ $participante->empresa->nome_razao ?? null }}
              <stron>Responsável:</stron> {{ $participante->laboratorio->responsavel_tecnico ?? null }} <brg>
              <stron>cargo:</stron> {{ $participante->laboratorio->cargo ?? null }} <brg>
              <strong>Email:</strong> {{ $participante->laboratorio->email ?? null }} <br>
              <strong>Telefone:</strong> {{ $participante->laboratorio->telefone ?? null }}
            </p>
          </div>
          <div class="col-4">
            <div class="text-end">
              @if ($participante->pessoa->deleted_at !== null)
                <span class="text-secondary">Pessoa excluida, somente leitura</span>
              @else
                <a href="{{ route('pessoa-insert', $participante->empresa->uid) }}" class="link-primary fw-medium">
                  Editar Empresa 
                  <i class="ri-arrow-right-line align-middle"></i>
                </a>
              @endif
            </div>
          </div>
        </div>

        <div class="row">
          <form method="POST" action="{{ route('salvar-inscrito-interlab', $participante->uid) }}">
            @csrf
            <input type="hidden" name="agenda_interlab_id" value="{{ $agendainterlab->id }}">
            <div class="row">

              <div class="col-4 py-2">
                <x-forms.input-field name="valor" label="Valor" class="money" :value="old('valor') ?? ($participante->valor ?? null)"/>
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
