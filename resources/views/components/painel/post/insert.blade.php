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
                                  <small class="text-danger-emphasis opacity-75"> * </small>
                              </label>
                              <input type="text" class="form-control" name="titulo"
                                  value="{{ old('titulo') ?? ($post->titulo ?? null) }}" required>
                              @error('titulo')
                                  <div class="text-warning">{{ $message }}</div>
                              @enderror
                          </div>

                          {{-- ckeditor --}}
                          @if ($tipo == 'noticia')
                              {{-- mostra só se for noticia --}}
                              <div class="col-12">
                                  <label class="form-label">Conteudo
                                      <small class="text-danger-emphasis opacity-75"> * </small>
                                  </label>
                                  <textarea id="editor" class="ckeditor-classic" name="conteudo" required>{!! old('conteudo') ?? ($post->conteudo ?? null) !!}</textarea>
                                  @error('conteudo')
                                      <div class="text-warning">{{ $message }}</div>
                                  @enderror
                              </div>
                          @endif
                          {{-- ckeditor --}}
                          {{-- thumb normal --}}
                          <div class="col-12">
                              @if ($post->thumb)
                                  <label class="form-label">Editar Capa</label>
                                  <input type="text" name="thumb" class="form-control thumb-text" readonly
                                      value="{{ $post->thumb }}">
                                  <input type="file" class="form-control thumb-file" name="thumb" id="formFile"
                                      accept="image/png, image/jpeg" style="display: none;">

                                  <div class="card mt-3 border" style="width: 11rem; height: 11rem" id="card-thumb">
                                      <div class="card-body"
                                          style="background-image: url('{{ asset('post-media/' . $post->thumb) }}'); background-size: cover;">
                                          <a href="javascript:void(0)" class="btn btn-danger btn-sm "
                                              onclick="event.preventDefault(); addToDeleteList('thumb');">
                                              <strong>X</strong>
                                          </a>
                                      </div>
                                  </div>
                              @else
                                  <label class="form-label">Inserir Capa</label>
                                  <input type="file" class="form-control" name ="thumb" id="formFile"
                                      accept="image/png, image/jpeg"
                                      value="{{ old('thumb') ?? ($post->thumb ?? null) }}">
                                  @error('thumb')
                                      <div class="text-warning">{{ $message }}</div>
                                  @enderror
                              @endif
                          </div>
                          {{-- thumb normal --}}

                          {{-- fotos da galeria --}}
                          @if ($tipo == 'galeria')
                              <div class="col-12">
                                  <label class="form-label">Inserir Fotos</label>
                                  <input type="file" class="form-control" name="imagens[]" id="formFileMultiple"
                                      accept="image/png, image/jpeg" multiple>

                                  @error('imagens[]')
                                      <div class="text-warning">{{ $message }}</div>
                                  @enderror

                                  @if (isset($postMedia) && count($postMedia) > 0)
                                      <div class="d-flex flex-wrap mt-3">
                                          @foreach ($postMedia as $media)
                                              <div class="card border m-2" style="width: 11rem; height: 11rem"
                                                  id="card-{{ $media->id }}">
                                                  <div class="card-body"
                                                      style="background-image: url('{{ asset('post-media/' . $media->caminho_media) }}'); background-size: cover;">
                                                      <a href="javascript:void(0)" class="btn btn-danger btn-sm"
                                                          onclick="event.preventDefault(); addToDeleteList({{ $media->id }});">
                                                          <strong>X</strong>
                                                      </a>
                                                  </div>
                                              </div>
                                          @endforeach
                                      </div>
                                  @endif
                              </div>
                          @endif
                          {{-- fotos da galeria --}}

                          {{-- deletelist oculto --}}
                          <input class="form-control" type="hidden" id="deleteList" name="deleteList" value="">
                          {{-- deletelist oculto --}}


                          <div class="row mt-4 d-flex align-items-center">
                              <div class="col-7">
                                  <label for="data_publicacao">Selecione uma data para publicação</label>
                                  <input class="form-control" type="date" id="data_publicacao" name="data_publicacao"
                                      value="{{ old('data_publicacao') ? old('data_publicacao') : ($post->data_publicacao ? \Carbon\Carbon::parse($post->data_publicacao)->format('Y-m-d') : '') }}" />
                                  @error('data_publicacao')
                                      <div class="text-warning">{{ $message }}</div>
                                  @enderror
                              </div>

                              <div class="col-5">
                                  <div class="form-check">
                                      <input class="form-check-input" id="form-check-input" name="rascunho"
                                          type="checkbox" value="1" @checked($post->rascunho)>
                                      <label class="form-check-label" for="form-check-input">Rascunho</label>
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
              <x-painel.form-delete.delete route="post-delete" id="{{ $post->id }}" />
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
      document.getElementById('card-thumb').addEventListener('click', function() {
          let thumbText = document.querySelector('.thumb-text');
          let thumbFile = document.querySelector('.thumb-file');

          if (thumbText.style.display !== 'none') {
              thumbText.style.display = 'none';
              thumbFile.style.display = 'block';
          } else {
              thumbText.style.display = 'block';
              thumbFile.style.display = 'none';
          }
      });
      let deleteList = [];

      function addToDeleteList(id) {
          let index = deleteList.indexOf(id);

          // Se a id da imagem já está na lista de exclusão
          if (index !== -1) {
              // Remove a id da lista de exclusão
              deleteList.splice(index, 1);

              // Restaura a opacidade da imagem para 1
              document.getElementById('card-' + id).style.opacity = "1";


          } else {
              // Adiciona a id da imagem à lista de exclusão
              deleteList.push(id);

              // Altera a opacidade da imagem para 0.5
              document.getElementById('card-' + id).style.opacity = "0.5";


          }

          // Atualiza o valor do campo de entrada oculto
          document.getElementById('deleteList').value = deleteList.join(',');


      }
  </script>
