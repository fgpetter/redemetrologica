<div class="col-12 col-xxl-6 col-xl-8">

  @if($errors->any())
    @foreach($errors->all() as $error)
      <div class="alert alert-warning">{{ $error }}</div>
    @endforeach
  @endif

  <div class="card">
    <div class="card-body">
      <h4 class="">Confirme sua inscrição:</h4>
      <h5 class="card-subtitle mt-3 mb-2 text-primary-emphasis">Dados do interlaboratorial:</h5>

      <p class="pb-3">
        <strong>Interlaboratorial:</strong> {{ $interlab->interlab->nome }} <br>
        <strong>Agenda:</strong> de {{ \Carbon\Carbon::parse($interlab->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($interlab->data_fim)->format('d/m/Y') }} <br>
      </p>
      
      @if($empresa)
      <div class="mt-3 mb-5">
        <h5 class="card-subtitle mb-2 text-primary-emphasis">Dados da empresa participante:</h5>
        <p>
          <strong>Razaão social:</strong> {{ $empresa->nome_razao }} <br>
          <strong>CNPJ:</strong> {{ $empresa->cpf_cnpj }} <br>
        </p>
      </div>
      @endif

      @if(!$empresa)
        <form action="{{ route('informa-empresa-interlab') }}" method="post" class="mb-5">
          @csrf
            <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-warning rounded">
              <p class="mb-2 text-black">Essa inscrição não está relacionada a uma empresa!</p>
              <footer class="blockquote-footer mt-0 text-black">Adicione o CNPJ da sua empresa para essa inscrição ou deixe em branco para uma inscrição individual:</footer>
            </blockquote>
            <div class="row">
              <div class="col-8 col-xxl-6">
                <input type="text" class="form-control" name="cnpj" placeholder="CNPJ" id="input-cnpj">
                @error('cnpj')<div class="text-warning">{{ $message }}</div>@enderror
              </div>
              <div class="col-2">
                <button type="submit" class="btn btn-primary">Adicionar</button>
              </div>
            </div>
        </form>
      @endif
      
    <!-- Nav tabs -->
    <ul class="nav nav-pills arrow-navtabs nav-info bg-light mb-3" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#participante" role="tab" aria-selected="true">
        Meu cadastro
        </a>
      </li>

      @if($empresa && !$convite)
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#convite" role="tab" aria-selected="false">
          Adicionar participantes
          </a>
        </li>
      @endif
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="participante" role="tabpanel"> <!-- Meu cadastro -->
        <form action="{{ route('confirma-inscricao-interlab') }}" method="post" >
          @csrf
          <h5 class="card-subtitle mb-2 text-primary-emphasis">Dados do participante:</h5>
          <div class="col-sm-8 col-xxl-6">
            <label for="nome" class="form-label">Nome <span class="text-danger"> * </span></label>
            <input type="text" class="form-control" name="nome" value="{{ auth()->user()->pessoa->nome_razao }}" required>
            @error('nome')<div class="text-warning">{{ $message }}</div>@enderror
  
            <label for="email" class="form-label mt-2">Email <span class="text-danger"> * </span></label>
            <input type="email" class="form-control" name="email" value="{{ auth()->user()->pessoa->email }}" required>
            @error('email')<div class="text-warning">{{ $message }}</div>@enderror
  
            <label for="telefone" class="form-label mt-2">Telefone <span class="text-danger"> * </span></label>
            <input type="text" class="form-control telefone" name="telefone" value="{{ auth()->user()->pessoa->telefone }}" required>
            @error('telefone')<div class="text-warning">{{ $message }}</div>@enderror
  
            <label for="cpf_cnpj" class="form-label mt-2">Documento <span class="text-danger"> * </span></label>
            <input type="text" class="form-control" name="cpf_cnpj" id="input-cpf" value="{{ auth()->user()->pessoa->cpf_cnpj }}" required>
            @error('cpf_cnpj')<div class="text-warning">{{ $message }}</div>@enderror
          </div>
  
          <input type="hidden" name="id_pessoa" value="{{ auth()->user()->pessoa->id }}">
          <input type="hidden" name="id_interlab" value="{{ $interlab->id }}">
          @if($empresa)<input type="hidden" name="id_empresa" value="{{ $empresa->id }}">@endif
  
          <button class="btn btn-primary mt-3">Confirmar meu cadastro</button>
        </form>
      </div>

      @if($empresa && !$convite)
      <div class="tab-pane" id="convite" role="tabpanel"> <!-- Adicionar participantes -->
        <h6 class="card-subtitle my-3 text-primary-emphasis">Adicionar outros participantes da minha empresa:</h6>

        <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-info rounded">
          <p>
            <i class="ri-information-fill text-info fs-5"></i>
            <span class="text-black">As pessoas adicionadas receberão um email com link para confirmarem suas inscrições.</span>
          </p>
        </blockquote>

        <form action="{{ route('envia-convite-interlab') }}" method="post" id="form-convite" >
          @csrf
          <div class="row row-invite mt-1 gx-1">
            <div class="col-5">
              <input type="text" class="form-control" name="indicacao-nome[]" placeholder="Nome" minlength="3" required>
            </div>
            <div class="col-5">
              <input type="email" class="form-control" name="indicacao-email[]" placeholder="Email" required>
            </div>
            <div class="col-2">
              <a href="javascript:void(0)" onclick="duplicateRow()" class="btn btn-primary"> + </a>
              <a href="javascript:void(0)" onclick="deleteRow(this)" class="btn btn-danger"> - </a>
            </div>
          </div>
          <button class="btn btn-primary mt-3">Enviar convites</button>
        </form>
      </div>
      @endif

    </div>
  </div>
</div>

<script>
  function duplicateRow() {
    let row = $('.row-invite').last().clone()
    row.find('input').val('')
    row.insertAfter('.row-invite:last');
  }

  function deleteRow(el){
    $(el).closest('.row-invite').remove();
  }

  document.getElementById('form-convite').addEventListener('submit', function(e){
    e.preventDefault();
      Swal.fire({
        title: 'Confirme os dados!',
        html: '<p class="fs-6"> Os convites serão enviados para os <b>nomes e emails informados</b>. <br> Não prefere revisar? </p>',
        icon: 'info',
        showCancelButton: true,
        cancelButtonText: 'Revisar',
        confirmButtonText: 'Já revisei, envie!',
        confirmButtonColor: '#2DCB73',
        cancelButtonColor: '#4AB0C1',
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) {
          e.target.submit();
        }
      });

  });
</script>