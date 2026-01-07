<form wire:submit.prevent="salvar">
  <div class="row gy-3">
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

    <hr>
    {{-- Envio de amostras --}}
    <div class="row">
      <div class="col-md-4">
        <x-forms.input-field 
          wire:model.lazy="form.data_envio_amostras"
          type="date" name="form.data_envio_amostras" label="Envio de amostras" />
        @error('form.data_envio_amostras') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-8">
        <x-forms.input-file-upload 
          label="Arquivo - Envio de amostras"
          :arquivoSalvo="$this->arquivosSalvos['arquivo_envio'] ?? null"
          wireModel="arquivo_envio"
          campo="arquivo_envio" />
      </div>
    </div>

    {{-- Início de ensaios --}}
    <div class="row">
      <div class="col-md-4">
        <x-forms.input-field 
          wire:model.lazy="form.data_inicio_ensaios"
          type="date" name="form.data_inicio_ensaios" label="Início de ensaios" />
        @error('form.data_inicio_ensaios') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-8">
        <x-forms.input-file-upload 
          label="Arquivo - Início de ensaios"
          :arquivoSalvo="$this->arquivosSalvos['arquivo_inicio_ensaios'] ?? null"
          wireModel="arquivo_inicio_ensaios"
          campo="arquivo_inicio_ensaios" />
      </div>
    </div>

    {{-- Limite de envio de resultados --}}
    <div class="row">
      <div class="col-md-4">
        <x-forms.input-field 
          wire:model.lazy="form.data_limite_envio_resultados"
          type="date" name="form.data_limite_envio_resultados" label="Limite de envio de resultados" />
        @error('form.data_limite_envio_resultados') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-8">
        <x-forms.input-file-upload 
          label="Arquivo - Limite de envio de resultados"
          :arquivoSalvo="$this->arquivosSalvos['arquivo_limite_envio_resultados'] ?? null"
          wireModel="arquivo_limite_envio_resultados"
          campo="arquivo_limite_envio_resultados" />
      </div>
    </div>

    {{-- Divulgação de relatórios --}}
    <div class="row">
      <div class="col-md-4">
        <x-forms.input-field 
          wire:model.lazy="form.data_divulgacao_relatorios"
          type="date" name="form.data_divulgacao_relatorios" label="Divulgação de relatórios" />
        @error('form.data_divulgacao_relatorios') <div class="text-warning">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-8">
        <x-forms.input-file-upload 
          label="Arquivo - Divulgação de relatórios"
          :arquivoSalvo="$this->arquivosSalvos['arquivo_divulgacao_relatorios'] ?? null"
          wireModel="arquivo_divulgacao_relatorios"
          campo="arquivo_divulgacao_relatorios" />
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
