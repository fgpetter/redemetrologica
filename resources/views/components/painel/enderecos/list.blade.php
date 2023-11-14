<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h4 class="card-title mb-0">Endereços</h4>
    <a href="#" class="btn btn-sm btn-success" > <i class="ri-add-line align-bottom me-1"></i> Adicionar </a>
  </div><!-- end card header -->
  <div class="card-body">
    <ul class="list-group list-group-flush">
      @forelse ($pessoa->enderecos as $endereco)
        <li ondblclick="alert('abre edicao')" class="list-group-item d-flex justify-content-between align-items-center">
          {{$endereco->endereco}}, {{$endereco->complemento}}<br>{{$endereco->bairro}}, 
           {{$endereco->cidade}} / {{$endereco->uf}} 
          <br> CEP: {{$endereco->cep}}
          <div class="">
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
                <li><a class="dropdown-item" href="{{--route('endereco-insert', ['endereco' => $endereco->id])--}}">Editar</a></li>
                <li>
                  <form method="POST" action="{{-- route('endereco-delete', $endereco->id) --}}">
                    @csrf
                    <button class="dropdown-item" type="submit">Deletar</button>
                  </form>
                </li>
              </ul>

          </div>
        </li>
        @empty
        <p>Não há endereço cadastrado</p>
    </ul>
    @endforelse
  </div>
</div>
