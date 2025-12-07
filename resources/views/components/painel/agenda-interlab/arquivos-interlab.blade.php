<div class="card">
  <div class="card-body">
    <h6>Protocolo do Interlab</h6>
    @if($agendainterlab->protocolo)
    <ul class="list-group list-group-flush mb-3">
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <div class="pe-2 pe-xxl-4">
          <a href="{{ asset('PROTOCOLO_INTERLAB/' . $agendainterlab->protocolo) }}" target="_blank">
            <i class="ph-file-arrow-down align-middle" style="font-size: 1.4rem"></i> 
            {{ $agendainterlab->protocolo }} 
          </a>
        </div>
        <form method="POST" action="{{ route('agenda-interlab-delete-protocolo', $agendainterlab->uid) }}">
          @csrf
          <button type="submit" class="btn btn-sm btn-danger py-0 px-1">
            <i class="ph-trash align-middle d-block d-xxl-none" style="font-size: 1rem"></i>
            <span class="d-none d-xxl-block">REMOVER</span>
          </button>
        </form>
      </li>
    </ul>
    @else
    <form method="POST" class="row g-2 mb-3 border-bottom pb-3" action="{{ route('agenda-interlab-upload-protocolo', $agendainterlab->uid) }}" enctype="multipart/form-data">
      @csrf
      <div class="col-12 col-xxl-8">
        <input class="form-control" name="protocolo" type="file" id="protocolo" accept=".doc, .pdf, .docx, .jpeg, .jpg, .png">
        @error('protocolo') <div class="text-warning">{{ $message }}</div> @enderror
      </div>
      <div class="col-2">
        <button type="submit" class="btn btn-primary">ENVIAR</button>
      </div>
    </form>
    @endif

    <h6>Materiais do Interlab</h6>

    @if($agendainterlab->materiais?->count() > 0)
    <ul class="list-group list-group-flush">
      @foreach($agendainterlab->materiais as $material)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div class="pe-2 pe-xxl-4">
            {{ $material->descricao }} <br>
            <a href="{{ asset('interlab-material/' . $material->arquivo) }}">
              <i class="ph-file-arrow-down align-middle" style="font-size: 1.4rem"></i> 
              {{ $material->arquivo }} 
            </a>
          </div>
          <form method="POST" action="{{ route('agenda-interlab-delete-material', $material->uid) }}" >
            @csrf
            <button type="submit" class="btn btn-sm btn-danger py-0 px-1">
              <i class="ph-trash align-middle d-block d-xxl-none" style="font-size: 1rem"></i>
              <span class="d-none d-xxl-block">REMOVER</span>
            </button>
          </form>
        </li>
      @endforeach
    </ul>
    @endif

    <form method="POST" class="row g-2 my-3 border-top" action="{{ route('agenda-interlab-upload-material', $agendainterlab->uid) }}" enctype="multipart/form-data">
      @csrf
      <div class="col-12 col-xxl-8 pt-2">
        <x-forms.input-field value="{{ old('descricao') }}" name="descricao" label="Descrição" placeholder="Nome do documento" />
        @error('descricao')<div class="text-warning">{{ $message }}</div>@enderror
      </div>
      <div class="col-12 col-xxl-8">
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