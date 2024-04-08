<div class="card">
    <div class="card-header d-flex align-items-center gap-3">
        <div class="avatar-sm">
            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                <i class="ph-buildings-light"></i>
            </span>
        </div>
        <h4 class="card-title mb-0">Empresa associada</h4>
    </div><!-- end card header -->
    <div class="card-body">
        <form action="{{ route('pessoa-associa-empresa', $pessoa->uid) }}" method="post">
            @csrf
            <div class="row align-items-end">
                <div class="col-10">
                    @if($pessoa->empresas->isNotEmpty())
                        <input type="text" name="empresas" value="{{ $pessoa->empresas->first()->nome_razao }}" class="form-control border-0" readonly>
                    @else
                        <x-forms.input-select name="empresa_id" label="Associar nova empresa">
                            <option value=""> Selecione </option>
                            @foreach (\App\Models\Pessoa::where('tipo_pessoa', 'PJ')->get() as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->nome_razao }}</option>                        
                            @endforeach
                        </x-forms.input-select>
                    @endif
                </div>
                <div class="col-2">
                    <button type="submit" class="btn btn-primary">{{ $pessoa->empresas->isNotEmpty() ? 'Remover' : 'Associar' }}</button>
                </div>
            </div>
        </form>
    </div>
</div>