<div class="card">
  <div class="card-header d-flex align-items-center gap-3">
    <div class="avatar-sm">
      <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
        <i class="ph-buildings-light"></i>
      </span>
    </div>
    <h4 class="card-title mb-0">Empresas associadas</h4>
  </div><!-- end card header -->
  <div class="card-body">
    @foreach ($pessoa->empresas as $empresa)
    <form action="{{ route('pessoa-associa-empresa', $pessoa->uid) }}" method="post">
      @csrf
      <input type="hidden" name="detach" value="1">
      <input type="hidden" name="empresa_id" value="{{$empresa->uid}}">
      <div class="row">
        <div class="col-8 col-xxl-9">
          <p>
            <strong>Razaão social:</strong> {{ $empresa->nome_razao }} <br>
            <strong>CNPJ:</strong> {{ $empresa->cpf_cnpj }} <br>
          </p>
        </div>
        <div class="col-4 col-xxl-3">
          <button type="submit" class="btn btn-ghost-danger btn-sm">REMOVER</button>
        </div>
      </div>
    </form>
    @endforeach

    <form action="{{ route('pessoa-associa-empresa', $pessoa->uid) }}" method="post">
      @csrf
      <div class="row align-items-end mt-3">
        <div class="col-8 col-xxl-9">
          <select class="form-control" data-choices name="empresa_id" id="empresa">
            <option value="">Selecione na lista</option>
            @foreach ($empresas as $empresa)
              <option value="{{ $empresa->uid }}">{{ $empresa->cpf_cnpj . ' - ' . $empresa->nome_razao }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-4 col-xxl-3">
          <button type="submit" class="btn btn-primary">ADICIONAR</button>
        </div>
      </div>
    </form>
  </div>
</div>

@section('script')
  <script defer>
    const empresa = document.getElementById('empresa')
    if(empresa){
      const choices = new Choices(empresa,{
        searchFields: ['label'],
        allowHTML: true
      });
    }
  </script>
@endsection