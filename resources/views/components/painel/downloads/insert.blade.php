<div class="card">
  <div class="card-body">
    <div class="tab-content">
      <div class="tab-pane active" id="post" role="tabpanel">
        <form method="POST" enctype="multipart/form-data"
          action="{{ isset($download->uid) ? route('download-update', $download->uid) : route('download-create') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-12">
              <x-forms.input-field name="titulo" label="Título" :value="old('titulo') ?? $download->titulo ?? null" />
              @error('titulo') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <x-forms.input-field name="descricao" label="Descrição" :value="old('descricao') ?? $download->descricao ?? null" />
              @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-12 col-sm-6 col-xl-4">
              <x-forms.input-select name="categoria" label="Categoria">
                <option value="">Selecione</option>
                <option @selected( $download->categoria == 'CURSOS' ) value="CURSOS">CURSOS</option>
                <option @selected( $download->categoria == 'QUALIDADE' ) value="QUALIDADE">QUALIDADE</option>
                <option @selected( $download->categoria == 'INTERLAB' ) value="INTERLAB">INTERLAB</option>
                <option @selected( $download->categoria == 'INSTITUCIONAL' ) value="INSTITUCIONAL">INSTITUCIONAL</option>
              </x-forms.input-select>
            </div>

            <div class="col-12 col-sm-6 col-xl-8">
              <div class="form-check bg-light rounded check-bg" style="padding: 0.7rem 1.8rem 0.7rem; margin-top: 1.1rem">
                <input class="form-check-input" name="site" value="1" id="site" type="checkbox" @checked($download->site ?? false)>
                <label class="form-check-label" for="site" data-bs-toggle="tooltip" data-bs-html="true" 
                  title="Ao marcar essa opção o arquivo será exibido na página que lista todos downloads publicos no site">
                  MOSTRAR NA PÁGINA DE DOWNLOADS
                </label>
              </div>
              @error('site') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              @if ($download->arquivo)
                <label class="form-label">Arquivo atual</label><br>
                <a href="{{ asset('downloads/' . $download->arquivo) }}">
                  <span>
                    <i class="ph-file-arrow-down align-middle" style="font-size: 1rem"></i>
                  </span>
                  {{ $download->arquivo }}
                </a>
                <br>
                <br>
              @endif

              <label class="form-label">@if ($download->arquivo) Alterar @else Inserir @endif Arquivo
                <span data-bs-toggle="tooltip" data-bs-html="true" title="Ao adicionar um arquivo o anterior será removido">
                  <i class="ri-information-line align-middle text-warning-emphasis" style="font-size: 1rem"></i>
                </span>
              </label>
              <input type="file" class="form-control" name ="arquivo" id="formFile" accept="doc,pdf,docx" >
              @error('arquivo') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

          </div>

          <div class="col-12 mt-3">
            <button type="submit"
              class="btn btn-primary px-4">{{ $download->uid ? 'Atualizar' : 'Salvar' }}</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>