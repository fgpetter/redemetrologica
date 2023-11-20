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
                            <label class="form-label">Titulo
                                <small class="text-danger-emphasis opacity-75">(Obrigatório) </small>
                            </label>
                            <input type="text" class="form-control" name="titulo"
                                value="{{ old('titulo') ?? ($post->titulo ?? null) }}">
                            @error('titulo')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- teste Ckeditor 5  --}}
                        <div class="py-12">
                            <div class="mx-auto sm:px-6 lg:px-8">
                                <!-- Remova a classe max-w-7xl -->
                                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                    <div class="p-6 bg-white border-b border-gray-200">
                                        <div class="mb-12">
                                            <label class="block">
                                                <span class="text-gray-700">Conteudo</span>
                                                <textarea id="editor" class="block w-full mt-1 rounded-md" name="conteudo" rows="3">{{ old('conteudo') ?? ($post->conteudo ?? null) }}</textarea>
                                            </label>
                                            @error('description')
                                                <div class="text-sm text-red-600">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        {{-- teste Ckeditor 5  --}}

                        {{-- <div class="col-12">
                            <label class="form-label">Conteúdo
                                <small class="text-danger-emphasis opacity-75">(Obrigatório) </small>
                            </label>
                            <textarea class="form-control" name="conteudo" rows="3">{{ old('conteudo') ?? ($post->conteudo ?? null) }}</textarea>
                            @error('conteudo')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div> --}}

                        <div class="col-12">
                            <label class="form-label">Imagem</label>
                            <input type="text" class="form-control" name="thumb"
                                value="{{ old('thumb') ?? ($post->thumb ?? null) }}">
                            @error('thumb')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo" aria-label="Default select example">
                                <option value="noticia" @selected($post->tipo == 'noticia')>Noticia</option>
                                <option value="galeria" @selected($post->tipo == 'galeria')>Galeria</option>
                            </select>
                        </div>

                        <div class="row mt-4 d-flex align-items-center">

                            <div class="col-7">
                                <label for="data-publicacao">Selecione uma data para publicação</label>
                                <input class="form-control" type="date" id="data_publicacao" name="data_publicacao"
                                    value="{{ old('data_publicacao') ? old('data_publicacao') : ($post->data_publicacao ? \Carbon\Carbon::parse($post->data_publicacao)->format('Y-m-d') : '') }}" />
                                @error('data_publicacao')
                                    <div class="text-warning">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-5">
                                <div class="form-check">
                                    <input class="form-check-input" name="rascunho" type="checkbox" value="1"
                                        @checked($post->rascunho)>
                                    <label class="form-check-label" for="flexCheckDefault">Rascunho</label>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="col-12 mt-3">
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
<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>
