@if (session('endereco-success')) <div class="alert alert-success"> {{ session('endereco-success') }} </div> @endif
@if (session('endereco-error')) 
  <div class="alert alert-danger"> 
    {{ session('endereco-error') }} 
    @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </div> 
@endif

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h4 class="card-title mb-0">Endereços</h4>
    <a href="#" class="btn btn-sm btn-success" > <i class="ri-add-line align-bottom me-1"></i> Adicionar </a>
  </div><!-- end card header -->
  <div class="card-body">

    <ul class="list-group list-group-flush">
      @forelse ($pessoa->enderecos as $endereco)
        @if(!$endereco->unidade_id) {{-- Lista somente endereços sem unidade atrelada --}}
          <div class="list-group-item d-flex justify-content-between align-items-center">
            {{$endereco->endereco}}, {{$endereco->complemento}} <br>
            {{$endereco->bairro}}, {{$endereco->cidade}} <br>
            {{$endereco->uf}} - CEP: {{$endereco->cep}}
            <div>
              @if ($pessoa->end_padrao == $endereco->id)
                <span class="badge bg-primary align-top mt-1">Padrão</span>
              @endif
              @if ($pessoa->end_cobranca == $endereco->id)
                <span class="badge bg-primary align-top mt-1">Cobrança</span>
              @endif
                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" 
                    data-bs-toggle="tooltip" title="Detalhes e edição"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                  <li>
                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="{{"#modal_endereco_$endereco->uid"}}">Editar</a>
                  </li>
                  @if($pessoa->end_padrao != $endereco->id)  {{-- Impede deletar endereço padrão --}}
                    <form method="POST" action="{{ route('endereco-delete', $endereco->uid) }}">
                      @csrf
                      <button class="dropdown-item" type="submit">Deletar</button>
                    </form>
                  @endif
                </ul>
            </div>
          </div>
          <x-painel.enderecos.modal :endereco="$endereco" :pessoa="$pessoa"/>
        @endif
        @empty
        <p>Não há endereço cadastrado</p>
    </ul>

    @endforelse
  </div>
</div>
