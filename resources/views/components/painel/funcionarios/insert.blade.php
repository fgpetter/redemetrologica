@if (session('funcionario-error')) <div class="alert alert-danger"> {{ session('error') }} </div> @endif
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ isset($funcionario->id) ? route('funcionario-update', $funcionario->id) : route('funcionario-create') }}">
      @csrf
      <div class="row gy-3">

        <div class="col-8">
          <label class="form-label">Nome Completo<small class="text-danger-emphasis opacity-75"> (Obrigatório) </small></label>
          <input type="text" class="form-control" name="nome_razao" 
            value="{{ old('nome_razao') ?? $funcionario->pessoa->nome_razao ?? null }}">
          @error('nome_razao') <div class="text-warning">{{ $message }}</div> @enderror 
        </div>

        <div class="col-4">
          <label class="form-label">CPF<small class="text-danger-emphasis opacity-75"> (Obrigatório) </small></label>
          <input type="text" class="form-control" name="cpf_cnpj" id="input-cpf" 
            value="{{ old('cpf_cnpj') ?? $funcionario->pessoa->cpf_cnpj ?? null }}" >
          @error('cpf_cnpj') <div class="text-warning">{{ $message }}</div> @enderror 
        </div>

        <div class="col-4">
          <label class="form-label">RG</label>
          <input type="number" class="form-control" name="rg_ie" 
            value="{{ old('rg_ie') ?? $funcionario->pessoa->rg_ie ?? null }}" >
          @error('rg_ie') <div class="text-warning">{{ $message }}</div> @enderror 
        </div>

        <div class="col-4">
          <label class="form-label">Telefone</label>
          <input type="text" class="form-control" name="telefone" id="telefone" 
            value="{{ old('telefone') ?? $funcionario->pessoa->telefone ?? null }}" >
          @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror 
        </div>

        <div class="col-4">
          <label class="form-label">Email</label>
          <input type="text" class="form-control" name="email" id="email" 
            value="{{ old('email') ?? $funcionario->pessoa->email ?? null }}" >
          @error('email') <div class="text-warning">{{ $message }}</div> @enderror 
        </div>

        <div class="row mt-3">
          <div class="col-6">
            <label class="form-label">Cargo</label>
            <select class="form-select" name="cargo" aria-label="Default select example">
              <option value="" >Selecione um cargo</option>
              <option value="analista">Analista</option>
              <option value="gerente">Gerente</option>
              <option value="supervisor">Supervisor</option>
            </select>
          </div>
  
          <div class="col-6">
            <label class="form-label">Setor</label>
            <select class="form-select" name="setor" aria-label="Default select example">
              <option value="" >Selecione um setor</option>
              <option value="rh">RH</option>
              <option value="financeiro">Financeiro</option>
              <option value="direcao">Direção</option>
            </select>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-3">
            <label class="form-label">Data admissão</label>
            <input type="date" class="form-control" name="admissao" id="admissao" 
              value="{{ old('admissao') ?? $funcionario->pessoa->admissao ?? null }}" >
            @error('admissao') <div class="text-warning">{{ $message }}</div> @enderror 
          </div>
  
          <div class="col-3">
            <label class="form-label">Data demissão</label>
            <input type="date" class="form-control" name="demissao" id="demissao" 
              value="{{ old('demissao') ?? $funcionario->pessoa->demissao ?? null }}" >
            @error('demissao') <div class="text-warning">{{ $message }}</div> @enderror 
          </div>

          <div class="col-6">
            <label for="curriculo" class="form-label">Currículo</label>
            <input class="form-control" name="curriculo" type="file" id="curriculo">
            @error('demissao') <div class="text-warning">{{ $message }}</div> @enderror 
          </div>
        </div>
        <h6 class="mb-0">Dados de endereço</h6>
        <x-painel.enderecos.form-endereco :endereco="$funcionario->pessoa->enderecos[0] ?? null"/>

        <div class="row mt-3">
          <div class="col">
            <label for="oservacoes" class="form-label">Observações</label>
            <textarea class="form-control" name="oservacoes" id="oservacoes" rows="3"></textarea>
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary px-4">{{ ($funcionario->id) ? 'Atualizar' : 'Salvar'}}</button>
        </div>

      </div>
    </form>
    @if($funcionario->id)
      <x-painel.funcionarios.form-delete route="funcionario-delete" id="{{$funcionario->uid}}" />
    @endif

  </div>
  
</div>
