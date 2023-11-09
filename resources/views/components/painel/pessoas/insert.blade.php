@if (session('error')) <div class="alert alert-danger"> {{ session('error') }} </div> @endif
<div class="card">
  <div class="card-body">

    {{-- Abas --}}
    @if (!$pessoa->id)
      <ul class="nav nav-pills nav-primary mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#pf" role="tab">Pessoa Física</a>
        </li>
        <li class="nav-item ">
            <a class="nav-link" data-bs-toggle="tab" href="#pj" role="tab">Pessoa Jurídica</a>
        </li>
      </ul>
    @endif
  
    {{-- Formularios --}}
    <div class="tab-content">

      {{-- PF --}}
      @if(!$pessoa->id || $pessoa->tipo_pessoa == 'PF')
        <div class="tab-pane active" id="pf" role="tabpanel">
          <form method="POST" action="{{ isset($pessoa->id) ? route('pessoa-update', $pessoa->id) : route('pessoa-create') }}">
            @csrf
            <div class="row gy-3">
      
              <div class="col-12">
                <label for="nome" class="form-label">Nome Completo</label>
                <input type="text" class="form-control" name="nome_razao" id="nome" 
                  value="{{ old('nome_razao') ?? $pessoa->nome_razao ?? null }}">
                @error('nome_razao') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
  
              <div class="col-6">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" class="form-control" name="cpf_cnpj" id="input-cpf" 
                  value="{{ old('cpf_cnpj') ?? $pessoa->cpf_cnpj ?? null }}" >
                @error('cpf_cnpj') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
      
              <div class="col-6">
                <label for="rg" class="form-label">RG</label>
                <input type="number" class="form-control" name="rg_ie" id="rg" 
                  value="{{ old('rg_ie') ?? $pessoa->rg_ie ?? null }}" >
                @error('rg_ie') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
      
              <div class="col-6">
                <label for="telefone" class="form-label">Telefone <small class="text-muted"> (opcional) </small></label>
                <input type="text" class="form-control" name="telefone" id="telefone" 
                  value="{{ old('telefone') ?? $pessoa->telefone ?? null }}" >
                @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
  
              <div class="col-6">
                <label for="email" class="form-label">Email <small class="text-muted"> (opcional) </small></label>
                <input type="text" class="form-control" name="email" id="email" 
                  value="{{ old('email') ?? $pessoa->email ?? null }}" >
                @error('email') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>

              @if (!$pessoa->id)
                <x-painel.enderecos.form-endereco/>
              @endif

              <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">{{ ($pessoa->id) ? 'Atualizar' : 'Salvar'}}</button>
              </div>
      
            </div>
          </form>
        </div>
      @endif
      {{-- PJ --}}
      @if(!$pessoa->id || $pessoa->tipo_pessoa == 'PJ')
        <div class="tab-pane {{$pessoa->tipo_pessoa == 'PJ' ? 'active' : ''}}" id="pj" role="tabpanel">
          <form method="POST" action="{{ isset($pessoa->id) ? route('pessoa-update', $pessoa->id) : route('pessoa-create') }}">
            @csrf
            <div class="row gy-3">

              <div class="col-12">
                <label for="nome" class="form-label">Razão Social</label>
                <input type="text" class="form-control" name="nome_razao" id="nome" 
                  value="{{ old('nome_razao') ?? $pessoa->nome_razao ?? null }}">
                @error('nome_razao') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
      
              <div class="col-12">
                <label for="nome-fantasia" class="form-label">Nome Fantasia <small class="text-muted"> (opcional) </small></label>
                <input type="text" class="form-control" name="nome_fantasia" id="nome-fantasia" 
                  value="{{ old('nome_fantasia') ?? $pessoa->nome_fantasia ?? null }}" >
                @error('nome_fantasia') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
      
              <div class="col-6">
                <label for="cpf-cnpj" class="form-label">CNPJ</label>
                <input type="text" class="form-control" name="cpf_cnpj" id="input-cnpj" 
                  value="{{ old('cpf_cnpj') ?? $pessoa->cpf_cnpj ?? null }}" >
                @error('cpf_cnpj') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
  
              <div class="col-6">
                <label for="ie" class="form-label">Inscrição estadual</label>
                <input type="number" class="form-control" name="rg_ie" id="ie" 
                  value="{{ old('rg_ie') ?? $pessoa->rg_ie ?? null }}" >
                @error('rg_ie') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
      
              <div class="col-6">
                <label for="insc_municipal" class="form-label">Inscrição Municipal <small class="text-muted"> (opcional) </small></label>
                <input type="number" class="form-control" name="insc_municipal" id="insc_municipal" 
                  value="{{ old('insc_municipal') ?? $pessoa->insc_municipal ?? null }}" placeholder="Digite CPF ou CNPJ" >
                @error('insc_municipal') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
  
              <div class="col-6">
                <label for="telefone" class="form-label">Telefone <small class="text-muted"> (opcional) </small></label>
                <input type="text" class="form-control" name="telefone" id="telefone2" 
                  value="{{ old('telefone') ?? $pessoa->telefone ?? null }}" >
                @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
  
              <div class="col-6">
                <label for="email" class="form-label">Email <small class="text-muted"> (opcional) </small></label>
                <input type="text" class="form-control" name="email" id="email" 
                  value="{{ old('email') ?? $pessoa->email ?? null }}" >
                @error('email') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>
  
              <div class="col-6">
                <label for="codigo_contabil" class="form-label">Código Contábil</label>
                <input type="text" class="form-control" name="codigo_contabil" id="codigo_contabil" 
                  value="{{ old('codigo_contabil') ?? $pessoa->codigo_contabil ?? null }}" >
                @error('codigo_contabil') <div class="text-warning">{{ $message }}</div> @enderror 
              </div>

              @if (!$pessoa->id)
                <x-painel.enderecos.form-endereco/>
              @endif

              <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">{{ ($pessoa->id) ? 'Atualizar' : 'Salvar'}}</button>
              </div>
            </div>
          </form>
        </div>
      @endif

    </div>

    @if($pessoa->id)
      <x-painel.pessoas.form-delete route="pessoa-delete" id="{{$pessoa->id}}" />
    @endif

  </div>
  
</div>
