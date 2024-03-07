 @if (session('error'))
     <x-alerts.alert type="error" />
 @endif
 @if (session('success'))
     <x-alerts.alert type="success" />
 @endif
 <div class="card">
     <div class="card-body">

         <form method="POST"
             action="{{ isset($lancamento->id) ? route('lancamento-financeiro-update', $lancamento->uid) : route('lancamento-financeiro-store') }}">
             @csrf
             <div class="row gy-3">

                 <div class="col-3">
                     <label class="form-label">Data Emissão</label>
                     <input type="date" class="form-control" name="data_emissao"
                         value="{{ old('data_emissao') ?? ($lancamento->data_emissao ?? null) }}">
                     @error('data_emissao')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-3">
                     <label class="form-label">Número do documento</label>
                     <input type="text" class="form-control" name="num_documento"
                         value="{{ old('num_documento') ?? ($lancamento->num_documento ?? null) }}">
                     @error('num_documento')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-6">
                     <label class="form-label">Documento</label>
                     <input type="text" class="form-control" name="documento"
                         value="{{ old('documento') ?? ($lancamento->documento ?? null) }}">
                     @error('documento')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-6">
                     <label class="form-label">Pessoa</label>
                     <select class="form-select" name="pessoa_id" aria-label="Default select example">
                         <option> - </option>
                         @foreach ($pessoas as $pessoa)
                             <option @selected($lancamento->pessoa_id == $pessoa->id) value="{{ $pessoa->id }}">{{ $pessoa->nome_razao }}
                             </option>
                         @endforeach
                     </select>
                     @error('pessoa')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-6">
                     <label class="form-label">Conta | Centro Custo</label>
                     <select class="form-select" name="centro_custo_id" aria-label="Default select example">
                         <option> - </option>
                         @foreach ($centrosdecusto as $centrodecusto)
                             <option @selected($lancamento->centro_custo_id == $centrodecusto->id) value="{{ $centrodecusto->id }}">
                                 {{ $centrodecusto->descricao }}</option>
                         @endforeach
                     </select>
                     @error('centro_custo_id')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-8">
                     <label class="form-label">Histórico</label>
                     <input type="text" class="form-control" name="historico"
                         value="{{ old('historico') ?? ($lancamento->historico ?? null) }}">
                     @error('historico')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-3">
                     <label class="form-label">Tipo</label>
                     <select class="form-select" name="tipo_lancamento" aria-label="Default select example">
                         <option value="CREDITO"> CRÉDITO </option>
                         <option value="DEBITO"> DEBITO </option>
                     </select>
                     @error('tipo_lancamento')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-3">
                     <label class="form-label">Valor</label>
                     <input type="text" class="form-control money" name="valor"
                         value="{{ old('valor') ?? ($lancamento->valor ?? null) }}">
                     @error('valor')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-3">
                     <label class="form-label">Vencimento</label>
                     <input type="date" class="form-control" name="vencimento"
                         value="{{ old('vencimento') ?? ($lancamento->vencimento ?? null) }}">
                     @error('vencimento')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-3">
                     <label class="form-label">Pagamento</label>
                     <input type="date" class="form-control" name="pagamento"
                         value="{{ old('pagamento') ?? ($lancamento->pagamento ?? null) }}">
                     @error('pagamento')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-3">
                     <label class="form-label">Status</label>
                     <select class="form-select" name="status" aria-label="Default select example">
                         <option value="EFETIVADO"> EFETIVADO </option>
                         <option value="PROVISIONADO"> PROVISIONADO </option>
                     </select>
                     @error('status')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 </div>

                 <div class="col-12">
                     <label class="form-label">Observações</label>
                     <textarea name="observacoes" class="form-select" name="observacoes">{{ old('observacoes') ?? ($lancamento->observacoes ?? null) }}
      </textarea>
                 </div>





                 <div class="col-12">
                     <button type="submit"
                         class="btn btn-primary px-4">{{ $lancamento->id ? 'Atualizar' : 'Salvar' }}</button>
                 </div>
             </div>
         </form>
         @if ($lancamento->id)
             <x-painel.form-delete.delete route="lancamento-financeiro-delete" id="{{ $lancamento->uid }}"
                 label="Lançamento" />
         @endif

     </div>

 </div>
