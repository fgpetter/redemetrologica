@props([
  'inscrito' => null,
  'certificado_emitido' => isset($inscrito->certificado_emitido) ? \Carbon\Carbon::parse($inscrito->certificado_emitido)->format('Y-m-d') : null,
  'resposta_pesquisa' => isset($inscrito->resposta_pesquisa) ? \Carbon\Carbon::parse($inscrito->resposta_pesquisa)->format('Y-m-d') : null,
])
{{-- modal --}}
<div class="modal fade" id="{{ 'inscritoModal'.$inscrito->uid }}" tabindex="-1" aria-labelledby="inscritoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="inscritoModalLabel">{{ 'Editar Inscrito'}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body" x-data="{ editMode: false }">
      <div class="row gy-3">
        <form method="POST" action="{{route('salvar-inscrito', $inscrito->uid)}}">
          @csrf
          <div class="row">
            <div class="col-8">
              <div x-show="!editMode">
                <h6 class="card-title mb-0">{{ $inscrito->nome ?? null }}</h6>
                <p class="card-text">
                  <strong>Email:</strong>  {{ $inscrito->email ?? null }} <br>
                  <strong>Telefone:</strong>  {{ $inscrito->telefone ?? null }}
                  @if( $inscrito->empresa_id ) <br> <strong>Empresa:</strong> {{ $inscrito->empresa->nome_razao ?? null }} @endif
                </p>
              </div>
              
              <div x-show="editMode">
                <div class="mb-2">
                  <label class="form-label">Nome</label>
                  <input type="text" class="form-control" name="nome" value="{{ $inscrito->nome ?? null }}">
                </div>
                <div class="mb-2">
                  <label class="form-label">E-mail</label>
                  <input type="email" class="form-control" name="email" value="{{ $inscrito->email ?? null }}">
                </div>
                <div class="mb-2">
                  <label class="form-label">Telefone</label>
                  <input type="text" class="form-control" name="telefone" value="{{ $inscrito->telefone ?? null }}" x-mask="(99) 99999-9999">
                </div>
                @if( $inscrito->empresa_id )
                  <p class="mb-0"><strong>Empresa:</strong> {{ $inscrito->empresa->nome_razao ?? null }}</p>
                @endif
              </div>
            </div>
            <div class="col-4">
              <div class="text-end">
                <button type="button" class="btn btn-sm btn-outline-primary" @click="editMode = !editMode" x-text="editMode ? 'Cancelar' : 'Editar Inscrito'"></button>
              </div>
            </div>
          </div>
          <hr class="mb-2">
          <div class="row">
            <div class="col col-sm-4 my-1">
              <x-forms.input-field :value="$inscrito->valor ?? null" name="valor" label="Valor" class="money" />
            </div>
            <div class="col col-sm-4 my-1">
              <x-forms.input-field :value="$certificado_emitido ?? null" type="date" name="certificado_emitido" label="Certificado Enviado Em" />
            </div>
            <div class="col col-sm-4 my-1">
              <x-forms.input-field :value="$resposta_pesquisa ?? null" type="date" name="resposta_pesquisa" label="Pesqisa Respondida Em" />
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
  {{-- endmodal --}}
  