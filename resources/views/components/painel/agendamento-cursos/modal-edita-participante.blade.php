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
    <div class="modal-body">
      <div class="row gy-3">
        <form method="POST" action="{{route('salvar-inscrito', $inscrito->uid)}}">
          @csrf
          <div class="row">
            <div class="col-8">
              <h6 class="card-title mb-0">{{ $inscrito->pessoa->nome_razao ?? null }}</h6>
              <p class="card-text">
                <strong>Email:</strong>  {{ $inscrito->pessoa->email ?? null }} <br>
                <strong>Telefone:</strong>  {{ $inscrito->pessoa->telefone ?? null }}
                @if( $inscrito->empresa_id ) <br> <strong>Empresa:</strong> {{ $inscrito->empresa->nome_razao ?? null }} @endif
              </p>
            </div>
            <div class="col-4">
              <div class="text-end">
                @if ($inscrito?->pessoa->deleted_at !== null)
                  <span class="text-secondary">Pessoa excluida, somente leitura</span>
                @else
                  <a href="{{ route('pessoa-insert', $inscrito->pessoa->uid) }}" class="link-primary fw-medium">
                    Editar Pessoa 
                    <i class="ri-arrow-right-line align-middle"></i>
                  </a>
                @endif
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
            @if ($inscrito?->pessoa->deleted_at == null)
              <button type="submit" class="btn btn-primary">Salvar</button>
            @endif
          </div>
        </form>

      </div>

    </div>
    </div>
  </div>
  </div>
  {{-- endmodal --}}
  