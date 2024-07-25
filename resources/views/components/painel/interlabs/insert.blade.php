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

                <div class="col-12">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" name="descricao" id="descricao" rows="2">{{ old('descricao') ?? ($interlab->descricao ?? null) }}</textarea>
                    @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                {{-- thumb --}}
                <div class="col-12">
                    <label for="folder" class="form-label">Thumb</label>
                    @if ($interlab->thumb)
                        <div class="input-group mt-0">
                            <input type="text" class="form-control" readonly
                                value="{{ explode('interlab-thumb/', $interlab->thumb)[0] }}">
                            <button class="btn btn-success dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false"></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ asset($interlab->thumb) }}"
                                        target="_blank">Baixar</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                        onclick="document.getElementById('thumb-delete').submit();">Remover
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @else
                        <input class="form-control" name="thumb" type="file" id="thumb"
                            accept=".jpeg, .jpg, .png">
                        @error('thumb')
                            <div class="text-warning">{{ $message }}</div>
                        @enderror
                    @endif
                    <div class="form-text"> Imagem que irá aparecer no site </div>
                </div>
                {{-- thumb --}}
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="2">{{ old('observacoes') ?? ($interlab->observacoes ?? null) }}</textarea>
                    @error('observacoes') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <button type="submit"
                        class="btn btn-primary px-4">{{ $interlab->id ? 'Atualizar' : 'Salvar' }}</button>
                </div>
            </div>
        </form>
        @if ($interlab->id)
            <x-painel.form-delete.delete route="interlab-delete" id="{{ $interlab->uid }}" label="Curso" />


            <form method="POST" id="thumb-delete" action="{{ route('interlab-thumb-delete', $interlab->uid) }}">
                @csrf
            </form>
        @endif

    </div>

</div>
