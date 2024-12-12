@php
  $index = $conta->uid ?? '';
@endphp

<div class="modal fade" id="{{ ($index) ? "modal_conta_$index" : "modal_conta_cadastro"}}" 
  tabindex="-1" aria-labelledby="{{ ($index) ? "modalgridConta$index" : "modalgridConta" }}" aria-modal="true">
  <div class="modal-dialog modal-dialog-right modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" 
        id="{{ ($index) ? "modalgridConta$index" : "modalgridConta" }}">
        {{$conta->conta ?? 'Cadastrar Conta'}}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ isset($conta->uid) ? route('conta-update', $conta->uid) : route('conta-create') }}" method="POST">
          @csrf
          <input type="hidden" name="pessoa_id" value="{{ $fornecedor->pessoa->id }}">
          <div class="row gy-3">
            <div class="col-sm-6">
              <label class="form-label">Nome da conta</label>
              <input type="text" class="form-control" name="nome_conta"
                value="{{old('nome_conta') ?? $conta->nome_conta ?? ''}}" placeholder="Ex. Conta caixa">
            </div>

            <div class="col-sm-6">
              <label class="form-label">Nome do banco<small class="text-danger-emphasis opacity-75"> * </small></label>
              <input type="text" class="form-control" name="nome_banco" required 
                value="{{old('nome_banco') ?? $conta->nome_banco ?? ''}}">
            </div>

            <div class="col-sm-6">
              <label class="form-label">Código do banco<small class="text-danger-emphasis opacity-75"> * </small></label>
              <input type="text" class="form-control" name="cod_banco" required 
                value="{{old('cod_banco') ?? $conta->cod_banco ?? ''}}">
            </div>

            <div class="col-sm-6">
              <label class="form-label">Agencia<small class="text-danger-emphasis opacity-75"> * </small></label>
              <input type="text" class="form-control" name="agencia" required 
                value="{{old('agencia') ?? $conta->agencia ?? ''}}">
            </div>

            <div class="col-sm-6">
              <label class="form-label">Conta<small class="text-danger-emphasis opacity-75"> * </small></label>
              <input type="text" class="form-control" name="conta" required 
                value="{{old('conta') ?? $conta->conta ?? ''}}">
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
</div>