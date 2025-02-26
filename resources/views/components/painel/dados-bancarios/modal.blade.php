<div class="modal fade" id="{{ (isset($conta->uid)) ? "modal_conta_$conta->uid" : "modal_conta_cadastro"}}" 
  tabindex="-1" aria-labelledby="{{ (isset($conta->uid)) ? "modalgridEndereco$conta->uid" : "modalgridEndereco" }}" aria-modal="true">
  <div class="modal-dialog modal-dialog-right modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="{{ (isset($conta->uid)) ? "modalgridEndereco$conta->uid" : "modalgridEndereco" }}">
        {{$conta->banco ?? 'Cadastrar Conta Bancária'}}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('conta-save') }}" method="POST">
          @csrf
          <div class="row mb-4">
            <input type="hidden" name="pessoa_id" value="{{$pessoa->id}}">
            <input type="hidden" name="conta_id" value="{{$conta->uid ?? null}}">
            <div class="row gy-2">
              <div class="col-8">
                <x-forms.input-field name="nome_banco" label="Nome do banco" :value="$conta->nome_banco ?? null" placeholder="Ex. Banco do Brasil"/>
                @error('nome_banco') <div class="text-warning"> {{ $message }} </div> @enderror
              </div>
              <div class="col-4">
                <x-forms.input-field name="cod_banco" label="Código do banco" :value="$conta->cod_banco ?? null"/>
                @error('cod_banco') <div class="text-warning"> {{ $message }} </div> @enderror
              </div>
              <div class="col-6">
                <x-forms.input-field name="agencia" label="Agência" :value="$conta->agencia ?? null"/>
                @error('agencia') <div class="text-warning"> {{ $message }} </div> @enderror
              </div>
              <div class="col-6">
                <x-forms.input-field name="conta" label="Conta" :value="$conta->conta ?? null"/>
                @error('conta') <div class="text-warning"> {{ $message }} </div> @enderror
              </div>
            </div>
          </div>
            <div class="col-lg-12">
              <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Sair</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
            </div>
          </div><!--end row-->
        </form>
      </div>
    </div>
  </div>