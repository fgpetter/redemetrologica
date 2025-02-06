<div class="card">
  <div class="card-body">
    <h6>Materiais do curso</h6>

    @if($curso->materiais->count() > 0)
    <ul class="list-group list-group-flush">
      @foreach($curso->materiais as $material)
      <li class="list-group-item d-flex justify-content-between align-items-center">
        {{ $material->descricao }}
          <form method="POST" action="{{ route('curso-delete-material', $material->uid) }}" >
            @csrf
            <button type="submit" class="btn btn-danger py-0 px-1">REMOVER</button>
          </form>
      </li>
          @endforeach
    </ul>
    @endif

    <form method="POST" class="row g-2 my-3 border-top" action="{{ route('curso-upload-material', $curso->uid) }}" enctype="multipart/form-data">
      @csrf
      <div class="col-8 pt-2">
        <x-forms.input-field value="{{ old('descricao') }}" name="descricao" label="Nome do Arquivo"/>
        @error('descricao')<div class="text-warning">{{ $message }}</div>@enderror
      </div>
      <div class="col-8">
        <input class="form-control" name="arquivo" type="file" id="arquivo"
          accept=".doc, .pdf, .docx, .jpeg, .jpg, .png, .xls, .xlsx">
        @error('arquivo')<div class="text-warning">{{ $message }}</div>@enderror
      </div>
      <div class="col-2">
        <button type="submit" class="btn btn-primary">ENVIAR</button>
      </div>
    </form>

  </div>
</div>

