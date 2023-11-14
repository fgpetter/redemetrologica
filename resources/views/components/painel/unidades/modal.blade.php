@php
  $index = $unidade->uid ?? '';
@endphp
<div class="modal fade" 
  id="{{ ($index) ? "modal_unidade_$index" : "modal_cadastro"}}" 
  tabindex="-1" aria-labelledby="{{ ($index) ? "modalgridLabel$index" : "modalgridLabel" }}" aria-modal="true">
  <div class="modal-dialog modal-dialog-right modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" 
        id="{{ ($index) ? "modalgridLabel$index" : "modalgridLabel" }}">
        {{$unidade->nome ?? 'Cadastrar Unidade'}}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ isset($unidade->uid) ? route('unidade-update', $unidade->uid) : route('unidade-create') }}" method="POST">
          @csrf
          <input type="hidden" name="pessoa" value="{{ $pessoa->id }}">
          <div class="row g-3">
            <div class="col-12">
              <div>
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" name="nome"
                  value="{{old('nome') ?? $unidade->nome ?? ''}}">
              </div>
            </div>

            <div class="col-4">
              <div>
                <label class="form-label">Telefone</label>
                <input type="text" class="form-control telefone" 
                  pattern="^\(\d{2}\)\s(9\s)?\d{4}-\d{4}$" title="Telefone inválido" 
                  name="telefone" value="{{old('telefone') ?? $unidade->fone ?? ''}}">
              </div>
            </div>
            
            <div class="col-5">
              <div>
                <label class="form-label">Email</label>
                <input type="text" class="form-control" name="email"
                  value="{{old('email') ?? $unidade->email ?? ''}}">
              </div>
            </div>
            
            <div class="col-3">
              <div>
                <label class="form-label">Código de laboratório</label>
                <input type="text" class="form-control" name="cod_laboratorio"
                  value="{{old('cod_laboratorio') ?? $unidade->cod_laboratorio ?? ''}}">
              </div>
            </div>
            
            <div class="col-6">
              <div>
                <label class="form-label">Responsável <small class="text-muted"> (opcional) </small> </label>
                <input type="text" class="form-control" name="nome_responsavel"
                  value="{{old('nome_responsavel') ?? $unidade->nome_responsavel ?? ''}}">
              </div>
            </div>
            
            <div class="col-6">
              <div>
                <label class="form-label">Responsável técnico <small class="text-muted"> (opcional) </small> </label>
                <input type="text" class="form-control" name="responsavel_tecnico"
                value="{{old('responsavel_tecnico') ?? $unidade->responsavel_tecnico ?? ''}}">
              </div>
            </div>
              <div></div>
              <x-painel.enderecos.form-endereco :endereco="$unidade->endereco ?? ''"/>

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