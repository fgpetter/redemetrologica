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
        <form method="POST" action="{{route('salvar-inscrito')}}" x-data="{ tipoInscricao: 'cnpj' }">
          @csrf
          <input type="hidden" name="agenda_curso_id" value="{{ $agendacurso->id }}">
          <div class="col-12 mb-3">
            <label class="form-label d-block mb-1">Tipo de Inscrição</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="tipo_inscricao" id="tipo_cnpj" value="cnpj" x-model="tipoInscricao">
              <label class="form-check-label" for="tipo_cnpj">CNPJ (Empresa)</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="tipo_inscricao" id="tipo_cpf" value="cpf" x-model="tipoInscricao">
              <label class="form-check-label" for="tipo_cpf">CPF (Pessoa Física)</label>
            </div>
          </div>
          <div class="row">
            <div class="col-12 mb-2" x-show="tipoInscricao === 'cnpj'">
              <label for="empresa" class="form-label mb-1">Empresa para cobrança <span class="text-danger">*</span></label>
              <select class="form-control" name="empresa_id" id="empresa">
                <option value="">Selecione uma empresa</option>
                @foreach($empresas as $empresa)
                  <option value="{{ $empresa->id }}">{{ $empresa->cpf_cnpj }} | {{ $empresa->nome_razao }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 mb-2">
              <x-forms.input-field :value="null" name="nome" label="Nome" required="true" />
            </div>
            <div class="col-md-6 mb-2">
              <x-forms.input-field :value="null" type="email" name="email" label="E-mail" required="true" />
            </div>
            <div class="col-md-6 mb-2">
              <x-forms.input-field :value="null" name="telefone" label="Telefone" mask="telefone" maxlength="15" />
            </div>



            <template x-if="tipoInscricao === 'cpf'">
              <div class="row m-0 p-0">
                <div class="col-md-4 mb-2">
                  <x-forms.input-field :value="null" name="cpf" label="CPF" class="cpf" required="true" mask="cpf" />
                </div>
                <div class="col-md-3 mb-2">
                  <x-forms.input-field :value="null" name="cep" label="CEP" class="cep" required="true" />
                </div>
                <div class="col-md-2 mb-2">
                  <x-forms.input-field :value="null" name="uf" label="UF" required="true" maxlength="2" uppercase="true" pattern="[A-Z]{2}" title="Duas letras maiúsculas" />
                </div>
                <div class="col-md-3 mb-2">
                  <x-forms.input-field :value="null" name="cidade" label="Cidade" required="true" />
                </div>
                <div class="col-md-5 mb-2">
                  <x-forms.input-field :value="null" name="bairro" label="Bairro" required="true" />
                </div>
                <div class="col-md-7 mb-2">
                  <x-forms.input-field :value="null" name="endereco" label="Endereço" required="true" />
                </div>
                <div class="col-12 mb-2">
                  <x-forms.input-field :value="null" name="complemento" label="Complemento" />
                </div>
              </div>
            </template>
            
            <div class="col-md-4 mb-2">
              <x-forms.input-field :value="null" name="valor" label="Valor" class="money" />
            </div>

            <div class="col-md-4 mb-2">
              <x-forms.input-field :value="null" type="date" name="certificado_emitido" label="Certificado Enviado Em" />
            </div>
            <div class="col-md-4 mb-2">
              <x-forms.input-field :value="null" type="date" name="resposta_pesquisa" label="Pesquisa Respondida Em" />
            </div>
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
  