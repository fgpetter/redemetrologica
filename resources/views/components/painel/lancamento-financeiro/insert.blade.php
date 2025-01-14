@if($errors->any())

  @foreach ($errors->all() as $error)
    <div class="alert alert-warning">
      <strong>Erro ao salvar os dados!</strong> <br><br>
      <ul>
        <li>{{ $error }}</li>
      </ul>
    </div>
  @endforeach
  
@endif

<div class="card">
  <div class="card-body">

    <form method="POST" action="{{ isset($lancamento->id) ? route('lancamento-financeiro-update', $lancamento->uid) : route('lancamento-financeiro-store') }}">
      @csrf
      <div class="row gy-3">

        <div class="col-3">
          <label class="form-label">Data Emissão</label>
          <input type="date" class="form-control" name="data_emissao" value="{{ old('data_emissao') ?? ($lancamento->data_emissao ?? null) }}">
          @error('data_emissao')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-3">
          <label class="form-label">Nota Fiscal</label>
          <input type="text" class="form-control" name="nota_fiscal" value="{{ old('nota_fiscal') ?? ($lancamento->nota_fiscal ?? null) }}">
          @error('nota_fiscal')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="col-3">
          <label class="form-label">Conciliação</label>
          <input type="text" class="form-control" name="consiliacao" value="{{ old('consiliacao') ?? ($lancamento->consiliacao ?? null) }}">
          @error('consiliacao')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-3">
          <label class="form-label">Documento</label>
          <input type="text" class="form-control" name="documento" value="{{ old('documento') ?? ($lancamento->documento ?? null) }}">
          @error('documento')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-6">
          <label class="form-label">Pessoa</label>
          <select class="form-select" name="pessoa_id" id="pessoa">
            <option value="" > Selecione uma pessoa </option>
            @if($lancamento->pessoa_id)
              <option selected value="{{ $lancamento->pessoa->id }}">
                {{ $lancamento->pessoa->cpf_cnpj }} - {{ $lancamento->pessoa->nome_razao }}
              </option>
            @endif
            @foreach ($pessoas as $pessoa)
            <option @selected($lancamento->pessoa_id == $pessoa->id) value="{{ $pessoa->id }}">
              {{ $pessoa->cpf_cnpj }} - {{ $pessoa->nome_razao }}
            </option>
            @endforeach
          </select>
          @error('pessoa')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-3">
          <label class="form-label">Centro Custo</label>
          <select class="form-select" name="centro_custo_id">
            <option value=""> Selecione um centro de custo </option>
            @foreach ($centrosdecusto as $centrodecusto)
            <option @selected($lancamento->centro_custo_id == $centrodecusto->id) value="{{ $centrodecusto->id }}">
              {{ $centrodecusto->descricao }}</option>
            @endforeach
          </select>
          @error('centro_custo_id')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-3">
          <label class="form-label">Plano Conta</label>
          <select class="form-select" name="plano_conta_id" id="plano_conta">
            <option value=""> Selecione um plano de conta </option>
            @foreach ($planosconta as $planoconta)
            <option @selected($lancamento->plano_conta_id == $planoconta->id) value="{{ $planoconta->id }}">
              {{ $planoconta->descricao }}</option>
            @endforeach
          </select>
          @error('plano_conta_id')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-8">
          <label class="form-label">Histórico</label>
          <input type="text" class="form-control" name="historico" value="{{ old('historico') ?? ($lancamento->historico ?? null) }}">
          @error('historico')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-3">
          <label class="form-label">Tipo</label>
          <select class="form-select" name="tipo_lancamento">
            <option value="CREDITO"> CRÉDITO </option>
            <option value="DEBITO"> DEBITO </option>
          </select>
          @error('tipo_lancamento')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-8">
          <div class="card border overflow-hidden card-border-dark-subtle no-shadow mb-0">
            <div class="card-body pb-1 lh-lg">
              Nome ou razão social: {{ Str::title($lancamento->pessoa->nome_razao) }} <br>
              CPF ou CNPJ: {{ $lancamento->pessoa->cpf_cnpj }} <br>
              @if($lancamento->pessoa->enderecos->first())
              Endereço: {{$enderecocobranca->endereco }}, 
              {{$enderecocobranca->complemento }} - {{$enderecocobranca->cidade }} / {{$enderecocobranca->uf }} - CEP: {{$enderecocobranca->cep }}
              @endif
              <div class="text-end">
                <a href="{{ route('pessoa-insert', $lancamento->pessoa->uid) }}" class="link-primary fw-medium">
                  Editar dados
                  <i class="ri-arrow-right-line align-middle"></i>
                </a>
              </div>

            </div>
          </div>
        </div>
        <div class="col-4"></div>

        <div class="col-2">
          <label class="form-label">Valor</label>
          <input type="text" class="form-control money" name="valor" value="{{ old('valor') ?? ($lancamento->valor ?? null) }}">
          @error('valor')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-2">
          <label class="form-label">Vencimento</label>
          <input type="date" class="form-control" name="data_vencimento" value="{{ old('data_vencimento') ?? ($lancamento->data_vencimento ?? null) }}">
          @error('data_vencimento')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-2">
          <label class="form-label">Pagamento</label>
          <input type="date" class="form-control" name="data_pagamento" value="{{ old('data_pagamento') ?? ($lancamento->data_pagamento ?? null) }}" id="data_pagamento">
          @error('data_pagamento')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-3">
          <label class="form-label">Status</label>
          <input type="text" class="form-control" name="status" value="{{ $lancamento->data_pagamento ? 'EFETIVADO' : 'PROVISIONADO'  }}" readonly >
        </div>

        <div class="col-3">
          <label class="form-label">Modalidade de Pagamento</label>
          <select class="form-select" name="modalidade_pagamento_id">
            <option> - </option>
            @foreach ($modalidadepagamento as $modalidade)
            <option @selected($lancamento->modalidade_pagamento_id == $modalidade->id) value="{{ $modalidade->id }}">
              {{ $modalidade->descricao }}</option>
            @endforeach
          </select>
          @error('modalidade_pagamento_id')
          <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Observações</label>
          <textarea name="observacoes" class="form-control" name="observacoes" rows="5">{{ old('observacoes') ?? ($lancamento->observacoes ?? null) }}</textarea>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary px-4">{{ $lancamento->id ? 'Atualizar' : 'Salvar' }}</button>
        </div>
      </div>
    </form>
    @if ($lancamento->id)
    <x-painel.form-delete.delete route="lancamento-financeiro-delete" id="{{ $lancamento->uid }}" label="Lançamento" />
    @endif

  </div>

</div>
