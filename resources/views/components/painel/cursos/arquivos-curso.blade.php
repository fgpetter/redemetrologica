<div class="card">
  <div class="card-body">
    <h6>Materiais do curso</h6>

    @if($curso->materiais->count() > 0)
    <ul class="list-group list-group-flush">
      @foreach($curso->materiais as $material)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div class="pe-4">
            {{ $material->descricao }} <br>
            <a href="{{ asset('curso-material/' . $material->arquivo) }}">
              <i class="ph-file-arrow-down align-middle" style="font-size: 1.4rem"></i> 
              {{ $material->arquivo }} 
            </a>
          </div>
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
        <x-forms.input-field value="{{ old('descricao') }}" name="descricao" label="Descrição" placeholder="Nome do documento" />
        @error('descricao')<div class="text-warning">{{ $message }}</div>@enderror
      </div>
      <div class="col-8">
        <input class="form-control" name="arquivo" type="file" id="arquivo"
          accept=".doc, .pdf, .docx, .jpeg, .jpg, .png">
        @error('arquivo')
          <div class="text-warning">{{ $message }}</div>
          <span class="text-muted">Alguns arquivos .doc e .docx podem estar corrompidos mesmo que abram no Word. Salve o arquivo em outra pasta ou com outro nome.</span>
        @enderror
      </div>
      <div class="col-2">
        <button type="submit" class="btn btn-primary">ENVIAR</button>
      </div>
    </form>

  </div>
</div>

