<div class="card">
    <div class="card-body">

        <form method="POST"
            action="{{ isset($interlab->id) ? route('interlab-update', $interlab->uid) : route('interlab-create') }}"
            enctype="multipart/form-data">
            @csrf
            <div class="row gy-3">

                <div class="col-12">
                    <label class="form-label">Nome</label>
                    <input type="text" class="form-control" name="nome"
                        value="{{ old('nome') ?? ($interlab->nome ?? null) }}" required>
                    @error('nome') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">Tipo Interlab</label>
                    <select class="form-select" name="tipo" aria-label="Default select example">
                        <option value="BILATERAL" @selected($interlab->tipo == 'BILATERAL')>BILATERAL</option>
                        <option value="INTERLABORATORIAL" @selected($interlab->tipo == 'INTERLABORATORIAL')>INTERLABORATORIAL</option>
                    </select>
                    @error('tipo') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <x-forms.input-field name="tag"
                    tooltip="Informe o código do interlaboratorial para que seja gerada a tag e senha para o inscrito."
                    label="TAG (código)" maxlength="5" 
                    class="text-uppercase" 
                    :value="old('tag') ?? ($interlab->tag ?? null)" />
                    @error('tag') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" name="descricao" id="descricao" rows="2">{{ old('descricao') ?? ($interlab->descricao ?? null) }}</textarea>
                    <div class="form-text"> Informação que irá aparecer no site </div>
                    @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="2">{{ old('observacoes') ?? ($interlab->observacoes ?? null) }}</textarea>
                    <div class="form-text"> Informação interna </div>
                    @error('observacoes') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                {{-- thumb --}}
                    <label for="thumb" class="form-label">Ícone</label>
                    <div class="form-text"> Imagem que irá aparecer no site </div>
                    <div class="row">
                        @foreach($thumbs as $key => $thumb)
                        <div class="col-sm-2 p-1">
                            <div class="bg-light align-items-center text-center p-2">
                                <div class="form-check rounded check-bg" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $key }}"
                                    style="height: 80px; background: center / contain no-repeat url({{url( asset('build/images/site/').'/'.$thumb )}})">
                                    
                                    <input class="form-check-input" name="thumb" value="{{ $thumb }}" type="radio"
                                    @checked($thumb == $interlab->thumb ?? false)>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('thumb') <div class="text-warning">{{ $message }}</div> @enderror

                <div class="col-12">
                    <button type="submit"
                        class="btn btn-primary px-4">{{ $interlab->id ? 'Atualizar' : 'Salvar' }}</button>
                </div>
            </div>
        </form>
        @if ($interlab->id)
            <x-painel.form-delete.delete route="interlab-delete" id="{{ $interlab->uid }}" label="Curso" />
        @endif

    </div>

</div>
