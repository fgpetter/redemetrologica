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
          <form method="POST" action="{{ route('confirma-inscricao-interlab') }}" id="confirma-inscricao-interlab">
            @csrf
            <input type="hidden" name="interlab_uid" value="{{ $agendainterlab->uid }}">

            <div class="row gy-2">

              <div class="col-12 my-1">
                <label for="pessoa_id" class="form-label">Empresa para cobrança</label>
                <select class="form-control" data-choices name="empresa_uid" id="empresa">
                  <option value="">Selecione na lista</option>
                  @foreach($pessoas->where('tipo_pessoa', 'PJ') as $empresa)
                    <option value="{{ $empresa->uid }}">{{ $empresa->cpf_cnpj }} | {{ $empresa->nome_razao }}</option>
                  @endforeach
                </select>
              </div>
              <div class="text-danger d-none" id="invalid-empresa">Selecione uma opção válida</div>


              <div class="col-12 my-1">
                <label for="pessoa_uid" class="form-label">Pessoa responsável</label>
                <select class="form-control" data-choices name="pessoa_uid" id="pessoa">
                  <option value="">Selecione na lista</option>
                  @foreach($pessoas->where('tipo_pessoa', 'PF') as $pessoa)
                    <option value="{{ $pessoa->uid }}">{{ $pessoa->cpf_cnpj }} | {{ $pessoa->nome_razao }}</option>
                  @endforeach
                </select>
              </div>
              <div class="text-danger d-none" id="invalid-pessoa">Selecione uma opção válida</div>

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
                <x-forms.input-field :value="old('telefone') ?? null" name="telefone" label="Telefone" mask="telefone"/>
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
              <button type="button" class="btn btn-primary" id="send-button" >Salvar</button>
              <button type="submit" class="d-none" id="submit-button"></button>
            </div>
          </form>

        </div>

      </div>
    </div>
  </div>
</div>

@section('script')

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      let selectedEmpresa = false
      let selectedPessoa = false
      const empresa = document.getElementById('empresa')
      const pessoa = document.getElementById('pessoa')
      const form = document.getElementById('confirma-inscricao-interlab')
      const sendButton = document.getElementById('send-button')
      const warningDiv = document.getElementsByClassName('invalid-input')
      const invalidEmpresa = document.getElementById('invalid-empresa')
      const invalidPessoa = document.getElementById('invalid-pessoa')

      empresa.addEventListener('change', function(){
        selectedEmpresa = empresa.value
        invalidEmpresa.classList.add("d-none")
      })
      pessoa.addEventListener('change', function(){
        selectedPessoa = pessoa.value
        invalidPessoa.classList.add("d-none")
      })

      sendButton.addEventListener('click', function validFields() {
        if(selectedEmpresa && selectedPessoa){
          document.getElementById('submit-button').click()
        }
        if(!selectedEmpresa) { invalidEmpresa.classList.remove("d-none") }
        if(!selectedPessoa) { invalidPessoa.classList.remove("d-none") }

      })

    })

  </script>

@endsection