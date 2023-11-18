<div class="modal fade" id="{{ ($endereco->uid) ? "modal_endereco_$endereco->uid" : "modal_endereco_cadastro"}}" 
  tabindex="-1" aria-labelledby="{{ ($endereco->uid) ? "modalgridEndereco$endereco->uid" : "modalgridEndereco" }}" aria-modal="true">
  <div class="modal-dialog modal-dialog-left modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" 
        id="{{ ($endereco->uid) ? "modalgridEndereco$endereco->uid" : "modalgridEndereco" }}">
        {{$endereco->endereco ?? 'Cadastrar Endereço'}}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ isset($endereco->uid) ? route('endereco-update', $endereco->uid) : route('endereco-create') }}" method="POST">
          @csrf
          <div class="row">
            <x-painel.enderecos.form-endereco :endereco="$endereco ?? ''"/>
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