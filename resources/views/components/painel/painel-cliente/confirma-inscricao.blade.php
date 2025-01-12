@if($errors->any())
  @foreach($errors->all() as $error)
    <div class="alert alert-warning">{{ $error }}</div>
  @endforeach
@endif

<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12 col-xxl-6 border-end">
        <h4 class="mb-4">Confirme sua inscrição:</h4>
        {{-- Informações do curso --}}
        <x-painel.painel-cliente.dados-curso :curso="$curso" />
      </div>
      <div class="col-12 col-xxl-6">
        @if( session('success') )
          <div class="alert alert-success alert-dismissible bg-body-secondary fade show" role="alert">
            <strong> Tudo certo! </strong>
            <p class="text-dark">Se você não vai enviar nenhum convite para outras pessoas, 
            clique no botão para concluir a inscrição.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        {{-- Dados da empresa e form de informar empresa --}}
        @if( !$inscrito )
          <x-painel.painel-cliente.informa-empresa :empresa="$empresa" :curso="$curso"/>
        @endif

        <ul class="nav nav-pills arrow-navtabs nav-info bg-light mb-3" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#participante" role="tab" aria-selected="true">
            Me cadastrar
            </a>
          </li>
    
          @if($empresa && !$convidado)
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#convite" role="tab" aria-selected="false">
              Cadastrar outros
              </a>
            </li>
          @endif
        </ul>

        <div class="tab-content">
          <div class="tab-pane active" id="participante" role="tabpanel"> <!-- Meu cadastro -->
            <x-painel.painel-cliente.form-inscricao-curso 
              :pessoa="$pessoa"
              :empresa="$empresa" 
              :curso="$curso" 
              :convidado="$convidado" 
              :inscrito="$inscrito"/>
          </div>

          @if($empresa && !$convidado)
            <div class="tab-pane" id="convite" role="tabpanel"> <!-- Adicionar participantes -->
              <x-painel.painel-cliente.form-convite-curso :empresa="$empresa" :curso="$curso"/>
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>
  <div class="card-footer text-end">
    <a href="{{ route('conclui-inscricao') }}"  class="btn btn-danger">FECHAR INSCRIÇÃO</a>
  </div>
</div>