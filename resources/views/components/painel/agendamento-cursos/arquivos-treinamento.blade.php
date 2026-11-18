@props(['agendacurso'])

<div class="card">
    <div class="card-body">
        <h6>Materiais do Treinamento</h6>

        @if ($agendacurso->materiaisTreinamento->isNotEmpty())
            <ul class="list-group list-group-flush">
                @foreach ($agendacurso->materiaisTreinamento as $material)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="pe-2 pe-xxl-4">
                            {{ $material->descricao }} <br>
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($material->arquivo) }}" target="_blank" rel="noopener noreferrer">
                                <i class="ph-file-arrow-down align-middle" style="font-size: 1.4rem" aria-hidden="true"></i>
                                {{ basename($material->arquivo) }}
                            </a>
                        </div>
                        <form method="POST" action="{{ route('agendamento-curso-delete-material', ['agendaCurso' => $agendacurso->uid, 'material' => $material->uid]) }}" onsubmit="return confirm('Remover este material da turma?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger py-0 px-1" aria-label="Remover {{ $material->descricao ?: basename($material->arquivo) }}" title="Remover material">
                                <i class="ph-trash align-middle d-block d-xxl-none" style="font-size: 1rem" aria-hidden="true"></i>
                                <span class="d-none d-xxl-block">REMOVER</span>
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ route('agendamento-curso-upload-material', ['agendaCurso' => $agendacurso->uid]) }}" class="row g-2 my-3 border-top">
            @csrf
            <div class="col-12 col-xxl-8 pt-2">
                <x-forms.input-field name="descricao" id="material-descricao" label="Descrição" :value="old('descricao')" maxlength="190" placeholder="Nome do documento" />
                @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-xxl-8">
                <input class="form-control" name="arquivo" type="file" id="material-arquivo" aria-label="Arquivo" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
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
