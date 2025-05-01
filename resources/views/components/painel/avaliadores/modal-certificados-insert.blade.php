{{-- modal --}}
<div class="modal fade" id="{{ isset($certificado) ? 'certificadoModal'.$certificado->uid : 'certificadoModal'}}" tabindex="-1" aria-labelledby="certificadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="certificadoModalLabel">
                    {{ isset($certificado) ? 'Editar Certificado' : 'Adicionar Certificado' }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ isset($certificado) ? route('avaliador-update-certificado', $certificado->uid) : route('avaliador-create-certificado', $avaliador->uid) }}">
            @csrf
            <div class="row gy-3 mb-3">
  
              <div class="col-6">
                <x-forms.input-field name="data" type="date" :value="old('data') ?? $certificado->data ?? null" label="Data Cadastro" />
                @error('data') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-6">
                <x-forms.input-field name="revisao" :value="old('revisao') ?? $certificado->revisao ?? null" label="Revisão" />
                @error('revisao') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              
              <div class="col-6">
                <x-forms.input-field name="responsavel" :value="old('responsavel') ?? $certificado->responsavel ?? null" label="Responsável" />
                @error('responsavel') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              
              <div class="col-6">
                <x-forms.input-field name="motivo" :value="old('motivo') ?? $certificado->motivo ?? null" label="Motivo" />
                @error('motivo') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
  
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  {{-- endmodal --}}
    