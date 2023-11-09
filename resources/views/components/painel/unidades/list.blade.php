<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h4 class="card-title mb-0">Unidades</h4>
    <a href="#" class="btn btn-sm btn-success" > <i class="ri-add-line align-bottom me-1"></i> Adicionar </a>
  </div><!-- end card header -->
  <div class="card-body">
      @forelse ($pessoa->enderecos as $endereco)
        <div class="card">
          <div class="card-body d-flex justify-content-between align-items-start">
            <div>
              <h6 class="card-subtitle mb-2">Unidade {{$endereco->rua}}</h6>
              <p>
                Fone: (51) 3343-0915 <br>
                Responsável: José da Silva Sauro <br>
                Email: teste@teste.com.br <br>
                Código do laboratório: 123456 <br>
                Responsável técnico: João Alberto Barroso
              </p>
              <p>{{$endereco->rua}}, {{$endereco->numero}}, {{$endereco->complemento}}, {{$endereco->bairro}} 
              <br> {{$endereco->cidade}} / {{$endereco->uf}} CEP: {{$endereco->cep}}</p>
            </div>
            <div>
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
        </div>
        </div>
      @empty
        <p>Não há endereço cadastrado</p>
      @endforelse
  </div>
</div>