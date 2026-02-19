<form wire:submit.prevent="salvar">
  <div class="row gy-2">
    <input type="hidden" wire:model="form.agenda_interlab_id">

    <div class="col-10 py-2">
      <x-forms.input-field wire:model.lazy="form.descricao" name="form.descricao"
        label="Descrição" required />
      @error('form.descricao')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div class="col-2 py-2">
      <x-forms.input-field type="number" wire:model.lazy="form.vias" name="form.vias"
        label="N° de Vias" required />
      @error('form.vias')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 m-2 py-2 bg-light rounded">
      <label class="form-label">Selecione os parâmetros da rodada</label>
      @foreach ($interlabParametros as $parametro)
        <div class="form-check mb-2">
          <input class="form-check-input" 
               type="checkbox"
               wire:model="parametros"
               value="{{ $parametro->parametro->id }}"
               id="checkBox{{ $form->uid ?? 'new' }}{{ $parametro->parametro->id }}">
          <label class="form-check-label" 
               for="checkBox{{ $form->uid ?? 'new' }}{{ $parametro->parametro->id }}">
            {{ $parametro->parametro->descricao }}
          </label>
        </div>
      @endforeach
      @error('parametros')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div class="col-12"><hr class="my-2"></div>

    {{-- Envio de amostras --}}
    <div class="col-12">
        <div class="row g-1 align-items-end">
            <div class="col-12">
                <span class="fw-bold text-primary small"><i class="ri-send-plane-fill me-1"></i> 1. Envio de Amostras</span>
            </div>
            <div class="col-md-3">
                <x-forms.input-field 
                    wire:model.lazy="form.data_envio_amostras"
                    type="date" name="form.data_envio_amostras" label="Data de Envio" />
                @error('form.data_envio_amostras') <div class="text-warning small" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-9">
                <x-forms.input-file-upload 
                    label="Documento"
                    :arquivoSalvo="$this->arquivosSalvos['arquivo_envio'] ?? null"
                    wireModel="arquivo_envio"
                    campo="arquivo_envio" />
            </div>
            <div class="col-12">
                <x-forms.input-textarea 
                    wire:model.lazy="form.descricao_arquivo_envio"
                    name="form.descricao_arquivo_envio" label="Descrição" rows="2" />
            </div>
        </div>
    </div>

    <div class="col-12"><hr class="my-1 border-light"></div>

    {{-- Início de ensaios --}}
    <div class="col-12">
        <div class="row g-1 align-items-end">
            <div class="col-12">
                <span class="fw-bold text-primary small"><i class="ri-play-circle-fill me-1"></i> 2. Início de Ensaios</span>
            </div>
            <div class="col-md-3">
                <x-forms.input-field 
                    wire:model.lazy="form.data_inicio_ensaios"
                    type="date" name="form.data_inicio_ensaios" label="Data de Início" />
                @error('form.data_inicio_ensaios') <div class="text-warning small" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-9">
                <x-forms.input-file-upload 
                    label="Documento"
                    :arquivoSalvo="$this->arquivosSalvos['arquivo_inicio_ensaios'] ?? null"
                    wireModel="arquivo_inicio_ensaios"
                    campo="arquivo_inicio_ensaios" />
            </div>
            <div class="col-12">
                <x-forms.input-textarea 
                    wire:model.lazy="form.descricao_arquivo_inicio_ensaios"
                    name="form.descricao_arquivo_inicio_ensaios" label="Descrição" rows="2" />
            </div>
        </div>
    </div>

    <div class="col-12"><hr class="my-1 border-light"></div>

    {{-- Limite de envio de resultados --}}
    <div class="col-12">
        <div class="row g-1 align-items-end">
            <div class="col-12">
                <span class="fw-bold text-primary small"><i class="ri-timer-fill me-1"></i> 3. Limite de Envio de Resultados</span>
            </div>
            <div class="col-md-3">
                <x-forms.input-field 
                    wire:model.lazy="form.data_limite_envio_resultados"
                    type="date" name="form.data_limite_envio_resultados" label="Data Limite" />
                @error('form.data_limite_envio_resultados') <div class="text-warning small" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-9">
                <x-forms.input-file-upload 
                    label="Documento"
                    :arquivoSalvo="$this->arquivosSalvos['arquivo_limite_envio_resultados'] ?? null"
                    wireModel="arquivo_limite_envio_resultados"
                    campo="arquivo_limite_envio_resultados" />
            </div>
            <div class="col-12">
                <x-forms.input-textarea 
                    wire:model.lazy="form.descricao_arquivo_limite_envio_resultados"
                    name="form.descricao_arquivo_limite_envio_resultados" label="Descrição" rows="2" />
            </div>
        </div>
    </div>

    <div class="col-12"><hr class="my-1 border-light"></div>

    {{-- Divulgação de relatórios --}}
    <div class="col-12">
        <div class="row g-1 align-items-end">
            <div class="col-12">
                <span class="fw-bold text-primary small"><i class="ri-article-fill me-1"></i> 4. Divulgação de Relatórios</span>
            </div>
            <div class="col-md-3">
                <x-forms.input-field 
                    wire:model.lazy="form.data_divulgacao_relatorios"
                    type="date" name="form.data_divulgacao_relatorios" label="Data de Divulgação" />
                @error('form.data_divulgacao_relatorios') <div class="text-warning small" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-9">
                <x-forms.input-file-upload 
                    label="Documento"
                    :arquivoSalvo="$this->arquivosSalvos['arquivo_divulgacao_relatorios'] ?? null"
                    wireModel="arquivo_divulgacao_relatorios"
                    campo="arquivo_divulgacao_relatorios" />
            </div>
            <div class="col-12">
                <x-forms.input-textarea 
                    wire:model.lazy="form.descricao_arquivo_divulgacao_relatorios"
                    name="form.descricao_arquivo_divulgacao_relatorios" label="Descrição" rows="2" />
            </div>
        </div>
    </div>

    <div class="col-lg-12 mt-4">
      <div class="hstack gap-2 justify-content-end">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
        <button type="submit" class="btn btn-primary">
          Salvar
        </button>
      </div>
    </div>
  </div>
</form>

@script
  <script>
    $wire.on('refresh-rodadas-list', () => {
      setTimeout(() => {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => { 
          const closeBtn = modal.querySelector('.btn-close');
          if (closeBtn) {
            closeBtn.click();
          }
        });
      }, 300);
    });

    $wire.on('show-rodada-success', (event) => {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-right',
        iconColor: 'white',
        customClass: {
          popup: 'colored-toast',
        },
        showConfirmButton: false,
        timer: 6000,
        timerProgressBar: true,
        showCloseButton: true
      })

      Toast.fire({
        icon: 'success',
        title: event.message,
      })
    });
  </script>
@endscript
