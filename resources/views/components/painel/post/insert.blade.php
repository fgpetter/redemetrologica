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
        <form method="POST" enctype="multipart/form-data"
          action="{{ isset($post->slug) ? route('post-update', $post->slug) : route('post-create') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-12">
              <input type="hidden" name="tipo" value="{{ $tipo }}">
              <label class="form-label">Titulo {{ $tipo }}
                <small class="text-danger-emphasis opacity-75">(Obrigatório) </small>
              </label>
              <input type="text" class="form-control" name="titulo"
                value="{{ old('titulo') ?? ($post->titulo ?? null) }}">
              @error('titulo')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>

            {{-- ckeditor --}}
            @if ($tipo == 'noticia')
              {{-- mostra só se for noticia --}}
              <div class="col-12">
                <label class="form-label">Conteudo
                  <small class="text-danger-emphasis opacity-75">(Obrigatório) </small>
                </label>
                <textarea id="editor" class="ckeditor-classic" name="conteudo">
                  {!! old('conteudo') ?? ($post->conteudo ?? null) !!}
                </textarea>
                @error('conteudo')
                  <div class="text-warning">{{ $message }}</div>
                @enderror
              </div>
            @endif
            {{-- ckeditor --}}

            <div class="col-12">
              @if ($post->thumb)
                <label class="form-label">Editar Capa</label>
                <input type="text" class="form-control" readonly
                  value="{{ explode('post-media/', $post->thumb)[1] }}">

                <div class="card mt-3 border" style="width: 11rem; height: 11rem">
                  <div class="card-body"
                    style="background-image: url('{{ asset($post->thumb) }}'); background-size: cover;">

                    <a href="javascript:void(0)" class="btn btn-danger btn-sm"
                      onclick="document.getElementById('thumb-delete').submit();">
                      <strong>X</strong></a>
                  </div>
                </div>
              @else
                <label class="form-label">Inserir Capa</label>
                <input type="file" class="form-control" name ="thumb[]" id="formFile"
                  accept="image/png, image/jpeg" value="{{ old('thumb') ?? ($post->thumb ?? null) }}" multiple>

                @error('thumb')
                  <div class="text-warning">{{ $message }}</div>
                @enderror
              @endif
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
      <x-painel.post.form-delete route="post-delete" id="{{ $post->id }}" />
      <form method="POST" id="thumb-delete" action="{{ route('thumb-delete', $post->id) }}">
        @csrf
      </form>
    @endif

  </div>

</div>
<script src="{{ URL::asset('build/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>
<script>
  var ckClassicEditor = document.querySelectorAll(".ckeditor-classic")
  if (ckClassicEditor) {
    Array.from(ckClassicEditor).forEach(function() {
      ClassicEditor
        .create(document.querySelector('.ckeditor-classic'), {
          ckfinder: {
            uploadUrl: '{{ route('image-upload') . '?_token=' . csrf_token() }}',
          }
        })
        .then(function(editor) {
          editor.ui.view.editable.element.style.height = '200px';
        })
        .catch(function(error) {
          console.error(error);
        });
    });
  }
</script>
