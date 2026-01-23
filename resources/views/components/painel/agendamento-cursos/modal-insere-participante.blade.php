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
              <label for="empresa_id" class="form-label">Empresa para cobrança</label>
              <select class="form-control" data-choices name="empresa_id" id="empresa">
                <option value="">Sem empresa vinculada</option>
                @foreach($empresas as $empresa)
                  <option value="{{ $empresa->id }}">{{ $empresa->cpf_cnpj }} | {{ $empresa->nome_razao }}</option>
                @endforeach
              </select>
            </div>
            
            <div class="col-md-6 my-1">
              <x-forms.input-field :value="null" name="nome" label="Nome" required="true" />
            </div>
            <div class="col-md-6 my-1">
              <x-forms.input-field :value="null" type="email" name="email" label="E-mail" required="true" />
            </div>

            <div class="col-md-6 my-1">
              <x-forms.input-field :value="null" name="telefone" label="Telefone" />
            </div>
            <div class="col-md-6 my-1">
              <x-forms.input-field :value="null" name="valor" label="Valor" class="money" />
            </div>

            <div class="col-md-6 my-1">
              <x-forms.input-field :value="null" type="date" name="certificado_emitido" label="Certificado Enviado Em" />
            </div>
            <div class="col-md-6 my-1">
              <x-forms.input-field :value="null" type="date" name="resposta_pesquisa" label="Pesquisa Respondida Em" />
            </div>
          </div>
          <div class="modal-footer mt-3">
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
  