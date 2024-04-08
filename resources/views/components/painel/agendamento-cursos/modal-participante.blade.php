@props([
  'inscrito' => null,
  'data_confirmacao' => isset($inscrito->data_confirmacao) ? \Carbon\Carbon::parse($inscrito->data_confirmacao)->format('Y-m-d') : null,
  'certificado_emitido' => isset($inscrito->certificado_emitido) ? \Carbon\Carbon::parse($inscrito->certificado_emitido)->format('Y-m-d') : null,
  'resposta_pesquisa' => isset($inscrito->resposta_pesquisa) ? \Carbon\Carbon::parse($inscrito->resposta_pesquisa)->format('Y-m-d') : null,
  'inscrito_uid' => isset($inscrito->uid) ? $inscrito->uid : null
])
{{-- modal --}}
<div class="modal fade" id="{{ isset($inscrito) ? 'inscritoModal'.$inscrito->uid : 'inscritoModal'}}" tabindex="-1" aria-labelledby="inscritoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="inscritoModalLabel">{{ isset($inscrito) ? 'Editar Inscrito' : 'Adicionar Inscrito'}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      <div class="row gy-3">
        <form method="POST" action="{{route('salvar-inscrito', $inscrito_uid)}}">
          @csrf
          @if( isset($inscrito) )
            <input type="hidden" name="inscrito_uid" value="{{ $inscrito->uid }}">
            <input type="hidden" name="data_inscricao" value="{{ $inscrito->data_inscricao }}">
            <input type="hidden" name="pessoa_uid" value="{{ $inscrito->pessoa->uid}}">
          @endif
          <div class="row">
            <div class="col col-sm-6 my-1">
              <x-forms.input-field :value="$inscrito->pessoa->nome_razao ?? null" name="nome" label="Nome" required="true" />
            </div>
            @if( !isset($inscrito) )
              <div class="col col-sm-6 my-1">
              <x-forms.input-field :value="$inscrito->pessoa->cpf_cnpj ?? null" name="cpf_cnpj" label="CPF" class="cpf_cnpj" required="true" id="input-cpf"/>
            </div>
            @endif
          </div>
          <div class="row">
            <div class="col col-sm-4 my-1">
              <x-forms.input-field :value="$inscrito->pessoa->telefone ?? null" name="telefone" label="Telefone" class="telefone" required="true"/>
            </div>
            <div class="col col-sm-4 my-1">
              <x-forms.input-field :value="$inscrito->pessoa->email ?? null" name="email" label="Email" type="email" required="true"/>
            </div>
            <div class="col col-sm-4 my-1">
              <x-forms.input-field :value="$inscrito->valor ?? null" name="valor" label="Valor" class="money" />
            </div>
            <div class="col col-sm-4 my-1">            
              <x-forms.input-field :value="$data_confirmacao ?? null" type="date" name="data_confirmacao" label="Data Confirmação" />
            </div>
            <div class="col col-sm-4 my-1">
              <x-forms.input-field :value="$certificado_emitido ?? null" type="date" name="certificado_emitido" label="Certificado Enviado Em" />
            </div>
            <div class="col col-sm-4 my-1">
              <x-forms.input-field :value="$Presposta_pesquisa ?? null" type="date" name="resposta_pesquisa" label="Pesqisa Respondida Em" />
            </div>
            <span><i class="ri-information-line"></i>
              <small style="vertical-align: text-top;" > Ao editar os dados do inscrito, os dados da pessoa no sistema também serão atualziados.</small>
            </span>
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
  