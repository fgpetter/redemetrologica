<form wire:submit.prevent="salvar">
    <div class="row gy-3">
        <input type="hidden" wire:model="rodada.agenda_interlab_id">

        <div class="col-10 py-2">
            <x-forms.input-field wire:model.lazy="rodada.descricao" name="rodada.descricao"
                label="Descrição" required />
            @error('rodada.descricao')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-2 py-2">
            <x-forms.input-field type="number" wire:model.lazy="rodada.vias" name="rodada.vias"
                label="N° de Vias" required />
            @error('rodada.vias')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 m-2 py-2 bg-light rounded">
            <label class="form-label">Selecione os parâmetros da rodada</label>
            @foreach ($interlabParametros as $parametro)
                <div class="form-check mb-2">
                    <input class="form-check-input" 
                           type="checkbox"
                           wire:model.lazy="rodada.parametros"
                           value="{{ $parametro->parametro->id }}"
                           id="checkBox{{ $rodada['uid'] ?? 'new' }}{{ $parametro->parametro->id }}">
                    <label class="form-check-label" 
                           for="checkBox{{ $rodada['uid'] ?? 'new' }}{{ $parametro->parametro->id }}">
                        {{ $parametro->parametro->descricao }}
                    </label>
                </div>
            @endforeach
            @error('rodada.parametros')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <hr>

        <div class="col-md-4">
            <x-forms.input-field :value="old('data_envio_amostras') ?? ($rodada['data_envio_amostras']?->format('Y-m-d') ?? null)" type="date" name="data_envio_amostras"
                label="Envio de amostras" />
            @error('data_envio_amostras') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <x-forms.input-field :value="old('data_inicio_ensaios') ?? ($rodada['data_inicio_ensaios']?->format('Y-m-d') ?? null)" type="date" name="data_inicio_ensaios"
                label="Início de ensaios" />
            @error('data_inicio_ensaios') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <x-forms.input-field :value="old('data_limite_envio_resultados') ?? ($rodada['data_limite_envio_resultados']?->format('Y-m-d') ?? null)" type="date" name="data_limite_envio_resultados"
                label="Limite de envio de resultados" />
            @error('data_limite_envio_resultados') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <x-forms.input-field :value="old('data_divulgacao_relatorios') ?? ($rodada['data_divulgacao_relatorios']?->format('Y-m-d') ?? null)" type="date" name="data_divulgacao_relatorios"
                label="Divulgação de relatórios" />
            @error('data_divulgacao_relatorios') <div class="text-warning">{{ $message }}</div> @enderror
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
