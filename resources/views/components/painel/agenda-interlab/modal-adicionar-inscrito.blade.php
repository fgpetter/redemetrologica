<div class="modal fade" id="adicionaParticipanteModal" 
  tabindex="-1" aria-labelledby="adicionaParticipanteModalLabel" >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adicionaParticipanteModalLabel"> Adicionar Participante </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <form method="POST" action="{{ route('confirma-inscricao-interlab') }}">
            @csrf
            <input type="hidden" name="interlab_uid" value="{{ $agendainterlab->uid }}">

            <div class="row gy-2">

              <div class="col-12 my-1">
                <label for="pessoa_id" class="form-label">Empresa para cobrança</label>
                <select class="form-control" data-choices name="empresa_uid" id="empresa">
                  <option value="">Selecione na lista</option>
                  @foreach($empresas as $empresa)
                    <option value="{{ $empresa->uid }}">{{ $empresa->cpf_cnpj }} | {{ $empresa->nome_razao }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-xl-6">
                <x-forms.input-field :value="old('laboratorio') ?? null" name="laboratorio" label="Laboratório" class="mb-2" required/>
                @error('laboratorio') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-xl-6">
                <x-forms.input-field :value="old('responsavel_tecnico') ?? null" name="responsavel_tecnico" label="Responsável Técnico" class="mb-2" required/>
                @error('responsavel_tecnico') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="row mb-2 gy-2">
              <div class="col-12 col-sm-6">
                <x-forms.input-field :value="old('telefone') ?? null" name="telefone" label="Telefone" class="telefone"/>
                @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-sm-6">
                <x-forms.input-field :value="old('email') ?? null" name="email" type="email" label="E-mail"/>
                @error('email') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="row my-3 gy-2">
              <x-painel.enderecos.form-endereco :nome="false" :padrao="false" />
            </div>

            <div class="row gy-2">
              <div class="col-4">
                <x-forms.input-field name="valor" label="Valor" class="money" :value="old('valor') ?? null"/>
                @error('valor') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-12">
                <x-forms.input-textarea name="informacoes_inscricao" label="Informações do inscrito"
                >{{ old('informacoes_inscricao') ?? null }}
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
