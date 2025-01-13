{{-- modal --}}
<div class="modal fade" id="adicionaInscritoModal" tabindex="-1" aria-labelledby="adicionaInscritoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="adicionaInscritoModalLabel">{{ 'Adicionar Inscrito'}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      <div class="row gy-3">
        <form method="POST" action="{{route('salvar-inscrito')}}">
          @csrf
          <input type="hidden" name="agenda_curso_id" value="{{ $agendacurso->id }}">
          <div class="row">
            <div class="col-12 my-1">
              <label for="pessoa_id" class="form-label">Participante<span class="text-danger-emphasis"> * </span></label>
              <select class="form-control" data-choices name="pessoa_id" id="pessoa">
                <option value="">Selecione na lista</option>
                @foreach($pessoas as $pessoa)
                  <option value="{{ $pessoa->id }}">{{ $pessoa->cpf_cnpj }} | {{ $pessoa->nome_razao }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-12 my-1">
              <label for="pessoa_id" class="form-label">Empresa para cobrança</label>
              <select class="form-control" data-choices name="empresa_id" id="empresa">
                <option value="">Selecione na lista</option>
                @foreach($empresas as $empresa)
                  <option value="{{ $empresa->id }}">{{ $empresa->cpf_cnpj }} | {{ $empresa->nome_razao }}</option>
                @endforeach
              </select>
            </div>
            
            <div class="col col-sm-4 my-3">
              <x-forms.input-field :value="null" name="valor" label="Valor" class="money" />
            </div>
            <div class="col col-sm-4 my-3">
              <x-forms.input-field :value="null" type="date" name="certificado_emitido" label="Certificado Enviado Em" />
            </div>
            <div class="col col-sm-4 my-3">
              <x-forms.input-field :value="null" type="date" name="resposta_pesquisa" label="Pesqisa Respondida Em" />
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
  