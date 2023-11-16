@if (session('error'))
    <div class="alert alert-danger"> {{ session('error') }} </div>
@endif
@if (session('success'))
    <div class="alert alert-success"> {{ session('success') }} </div>
@endif
<div class="card">
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane active" id="post" role="tabpanel">
                <form method="POST"
                    action="{{ isset($post->slug) ? route('post-update', $post->slug) : route('post-create') }}">
                    @csrf
                    <div class="row gy-3">
                        {{-- <input type="hidden" name="X" value="X"> --}}
                        <div class="col-12">
                            <label class="form-label">id<small class="text-danger-emphasis opacity-75">
                                    (Obrigatório) </small></label>
                            <input type="text" class="form-control" name="id"
                                value="{{ old('id') ?? ($post->id ?? null) }}">
                            @error('id')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">titulo<small class="text-danger-emphasis opacity-75">
                                    (Obrigatório) </small></label>
                            <input type="text" class="form-control" name="titulo"
                                value="{{ old('titulo') ?? ($post->titulo ?? null) }}">
                            @error('titulo')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">slug<small class="text-danger-emphasis opacity-75">
                                    (Obrigatório) </small></label>
                            <input type="text" class="form-control" name="slug"
                                value="{{ old('slug') ?? ($post->slug ?? null) }}">
                            @error('slug')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">conteudo<small class="text-danger-emphasis opacity-75">
                                    (Obrigatório) </small></label>
                            <input type="text" class="form-control" name="conteudo"
                                value="{{ old('conteudo') ?? ($post->conteudo ?? null) }}">
                            @error('conteudo')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">thumb<small class="text-danger-emphasis opacity-75">
                                    (Obrigatório) </small></label>
                            <input type="text" class="form-control" name="thumb"
                                value="{{ old('thumb') ?? ($post->thumb ?? null) }}">
                            @error('thumb')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">rascunho<small class="text-danger-emphasis opacity-75">
                                    (Obrigatório) </small></label>
                            <input type="text" class="form-control" name="rascunho"
                                value="{{ old('rascunho') ?? ($post->rascunho ?? null) }}">
                            @error('rascunho')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tipo</label>

                            <select class="form-select" name="tipo" aria-label="Default select example">

                                <option value="noticia">Noticia</option>
                                <option value="galeria">Galeria</option>
                                @error('tipo')
                                    <div class="text-warning">{{ $message }}</div>
                                @enderror
                            </select>

                        </div>
                        <div class="col-12">
                            <label class="form-label">data_publicacao<small class="text-danger-emphasis opacity-75">
                                    (Obrigatório) </small></label>
                            <input type="text" class="form-control" name="data_publicacao"
                                value="{{ old('data_publicacao') ?? ($post->data_publicacao ?? null) }}">
                            @error('data_publicacao')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit"
                            class="btn btn-primary px-4">{{ $post->slug ? 'Atualizar' : 'Salvar' }}</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($post->id)
            <x-painel.noticias.form-delete route="post-delete" id="{{ $post->id }}" />
        @endif

    </div>

</div>
