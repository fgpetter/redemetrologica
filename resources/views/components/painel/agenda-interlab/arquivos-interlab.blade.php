<div class="card">
  <div class="card-body">
    <h6>Materiais do Interlab</h6>

    @if($agendainterlab->materiais?->count() > 0)
    <ul class="list-group list-group-flush">
      @foreach($agendainterlab->materiais as $material)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div class="pe-4">
            {{ $material->descricao }} <br>
            <a href="{{ asset('interlab-material/' . $material->arquivo) }}">
              <i class="ph-file-arrow-down align-middle" style="font-size: 1.4rem"></i> 
              {{ $material->arquivo }} 
            </a>
          </div>
          <form method="POST" action="{{ route('agenda-interlab-delete-material', $material->uid) }}" >
            @csrf
            <button type="submit" class="btn btn-danger py-0 px-1">REMOVER</button>
          </form>
        </li>
      @endforeach
    </ul>
    @endif

    <form method="POST" class="row g-2 my-3 border-top" action="{{ route('agenda-interlab-upload-material', $agendainterlab->uid) }}" enctype="multipart/form-data">
      @csrf
      <div class="col-8 pt-2">
        <x-forms.input-field value="{{ old('descricao') }}" name="descricao" label="Descrição" placeholder="Nome do documento" />
        @error('descricao')<div class="text-warning">{{ $message }}</div>@enderror
      </div>
      <div class="col-8">
        <input class="form-control" name="arquivo" type="file" id="arquivo"
          accept=".doc, .pdf, .docx, .jpeg, .jpg, .png">
          <div id="file-error" class="text-warning mt-1"></div>
        @error('arquivo')
          <div class="text-warning">{{ $message }}</div>
          <span class="text-warning">Alguns arquivos .doc e .docx podem estar corrompidos mesmo que abram no Word. Salve o arquivo em outra pasta ou com outro nome.</span>
        @enderror
      </div>
      <div class="col-2">
        <button type="submit" class="btn btn-primary">ENVIAR</button>
      </div>
    </form>

  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('arquivo');
    const errorContainer = document.getElementById('file-error');
    const maxFileSize = 5 * 1024 * 1024; // 5MB em bytes

    fileInput.addEventListener('change', function () {
      const file = fileInput.files[0];

      if (file && file.size > maxFileSize) {
        errorContainer.textContent = 'O arquivo é muito grande, diminua o arquivo usando www.ilovepdf.com/pt/comprimir_pdf ou www.tinyjpg.com.';
        fileInput.value = ''; 
      } else {
        errorContainer.textContent = ''; 
      }
    });
  });
</script>