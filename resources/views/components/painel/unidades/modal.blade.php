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
          <input type="hidden" name="pessoa" value="{{ $pessoa->uid }}">
          <div class="row g-3">

            <div class="col-12">
                <x-forms.input-field :value="old('nome') ?? $unidade->nome ?? ''" name="nome"
                  label="Nome da unidade" placeholder="Ex. Filial Caxias" required/>
                @error('nome') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-6 col-md-4">
              <x-forms.input-field :value="old('telefone') ?? $unidade->telefone ?? ''" name="telefone"
                label="Telefone" class="telefone" />
              @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-6 col-md-4">
              <x-forms.input-field :value="old('email') ?? $unidade->email ?? ''" name="email"
                label="E-mail" type="email" />
              @error('email') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-6 col-md-4">
              <x-forms.input-field :value="old('nome_responsavel') ?? $unidade->nome_responsavel ?? ''" name="nome_responsavel"
                label="Pessoa de contato" type="nome_responsavel" />
              @error('nome_responsavel') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <x-painel.enderecos.form-endereco :endereco="$unidade?->endereco" :nome="false" :padrao="false" />

            <div class="col-12">
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