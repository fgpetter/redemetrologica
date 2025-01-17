<div class="card">
  <div class="card-body">

    <form method="POST"
      action="{{ isset($curso->id) ? route('curso-update', $curso->uid) : route('curso-create') }}"
      enctype="multipart/form-data">
      @csrf
      <div class="row gy-3">

        <div class="col-12">
          <label class="form-label">Descrição</label>
          <input type="text" class="form-control" name="descricao"
            value="{{ old('descricao') ?? ($curso->descricao ?? null) }}">
          @error('descricao')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-6">
          <label class="form-label">Carga Horária</label>
          <input type="number" class="form-control" name="carga_horaria"
            value="{{ old('carga_horaria') ?? ($curso->carga_horaria ?? null) }}">
          @error('carga_horaria')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-6">
          <label class="form-label">Tipo Curso</label>
          <select class="form-select" name="tipo_curso" aria-label="Default select example">
            <option> - </option>
            <option value="OFICIAL" @selected($curso->tipo_curso == 'OFICIAL')>OFICIAL</option>
            <option value="SUPLENTE" @selected($curso->tipo_curso == 'SUPLENTE')>SUPLENTE</option>
            <option value="OUTROS" @selected($curso->tipo_curso == 'OUTROS')>OUTROS</option>
          </select>
          @error('tipo_curso')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Objetivo</label>
          <textarea class="form-control" name="objetivo" id="objetivo" rows="4">{{ old('objetivo') ?? ($curso->objetivo ?? null) }}</textarea>
          @error('objetivo')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Publico Alvo</label>
          <textarea class="form-control" name="publico_alvo" id="publico_alvo" rows="4">{{ old('publico_alvo') ?? ($curso->publico_alvo ?? null) }}</textarea>
          @error('publico_alvo')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Pré-Requisitos</label>
          <textarea class="form-control" name="pre_requisitos" id="pre_requisitos" rows="4">{{ old('pre_requisitos') ?? ($curso->pre_requisitos ?? null) }}</textarea>
          @error('pre_requisitos')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Exemplos Práticos</label>
          <textarea class="form-control" name="exemplos_praticos" id="exemplos_praticos" rows="4">{{ old('exemplos_praticos') ?? ($curso->exemplos_praticos ?? null) }}</textarea>
          @error('exemplos_praticos')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Referências Utilizadas</label>
          <textarea class="form-control" name="referencias_utilizadas" id="referencias_utilizadas" rows="4">{{ old('referencias_utilizadas') ?? ($curso->referencias_utilizadas ?? null) }}</textarea>
          @error('referencias_utilizadas')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Conteúdo Programático</label>
          <textarea class="form-control" name="conteudo_programatico" id="conteudo_programatico" rows="4">{{ old('conteudo_programatico') ?? ($curso->conteudo_programatico ?? null) }}</textarea>
          @error('conteudo_programatico')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        {{-- thumb --}}
        <div class="col-12">
        <label for="folder" class="form-label">Thumb</label>

          @if ($curso->thumb)
            <div class="input-group mt-0">
              <input type="text" class="form-control" readonly
                value="{{ explode('curso-thumb/', $curso->thumb)[0] }}">
              <button class="btn btn-success dropdown-toggle" type="button"
                data-bs-toggle="dropdown" aria-expanded="false"></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ asset($curso->thumb) }}"
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
        {{-- folder --}}
        <div class="col-12">
          <label for="folder" class="form-label">Folder</label>
          @if ($curso->folder)
            <div class="input-group mt-0">
              <input type="text" class="form-control" readonly
                value="{{ explode('curso-folder/', $curso->folder)[0] }}">
              <button class="btn btn-success dropdown-toggle" type="button"
                data-bs-toggle="dropdown" aria-expanded="false"></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ asset($curso->folder) }}"
                    target="_blank">Baixar</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li>
                  <a class="dropdown-item" href="javascript:void(0)"
                    onclick="document.getElementById('folder-delete').submit();">Remover
                  </a>
                </li>
              </ul>
          </div>
          @else
            <input class="form-control" name="folder" type="file" id="folder"
              accept=".doc, .pdf, .docx, .jpeg, .jpg, .png">
            @error('folder')
              <div class="text-warning">{{ $message }}</div>
            @enderror
          @endif
          <div class="form-text" > Folder do curso disponível para download </div>
        </div>
        {{-- folder --}}
        <div class="col-12">
          <label class="form-label">Observações Internas</label>
          <textarea class="form-control" name="observacoes_internas" id="observacoes_internas" rows="4">{{ old('observacoes_internas') ?? ($curso->observacoes_internas ?? null) }}</textarea>
          @error('observacoes_internas')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12">
          <button type="submit"
            class="btn btn-primary px-4">{{ $curso->id ? 'Atualizar' : 'Salvar' }}</button>
        </div>
      </div>
    </form>
    @if ($curso->id)
      <x-painel.form-delete.delete route="curso-delete" id="{{ $curso->uid }}" label="Curso" />


      <form method="POST" id="folder-delete" action="{{ route('curso-folder-delete', $curso->uid) }}">
        @csrf
      </form>
      <form method="POST" id="thumb-delete" action="{{ route('curso-thumb-delete', $curso->uid) }}">
        @csrf
      </form>
    @endif

  </div>

</div>