@if($empresa)
<div class="card card border overflow-hidden card-border-primary">
  <div class="card-header">
      <h6 class="card-title mb-0">Dados da empresa para cobrança:</h6>
  </div>
  <div class="card-body">
    <blockquote class="blockquote ps-2">
      <strong>Razaão social:</strong> {{ $empresa->nome_razao }} <br>
      <strong>CNPJ:</strong> {{ $empresa->cpf_cnpj }} <br>
    </blockquote>
    </div>
</div>
@endif

@if( !$empresa )
<blockquote class="blockquote custom-blockquote blockquote-outline blockquote-warning rounded mt-3 mb-5">
  <form action="{{ route('informa-empresa') }}" method="post">
    @csrf
      <p class="mb-2 text-black"> <strong>Importante:</strong> Os dados de cobrança não estão relacionados a uma empresa
        <br>
        <span class="fs-6">Adicione o CNPJ da sua empresa para essa inscrição ou deixe em branco para uma inscrição com seu CPF:</span>
      </p>
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
</blockquote>
@endif
