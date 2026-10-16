<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
        <h4 class="mb-0">
            @if($demanda?->temPendencia())
                <i class="ri-alert-fill text-warning me-1" title="Nova atividade após encerramento" aria-label="Nova atividade após encerramento"></i>
            @endif
            {{ $demanda ? '#'.$demanda->id.' · '.$demanda->titulo : 'Nova demanda' }}
        </h4>
        @if($demanda)
            <small class="d-block text-black">Criada por {{ $demanda->autor?->name }} em {{ $demanda->created_at->format('d/m/Y H:i') }}</small>
        @endif
        </div>
        <a href="{{ route('suporte-index') }}" class="btn btn-light"><i class="ri-arrow-left-line"></i> Voltar</a>
    </div>
    @if($editando)
        <form wire:submit="salvar" class="card">
            <div class="card-header"><h5 class="card-title mb-0">{{ $demanda ? 'Editar demanda' : 'Cadastrar demanda' }}</h5></div>
            <div class="card-body">
                <x-forms.input-field name="form.titulo" label="Título" wire:model="form.titulo" maxlength="255" required />
                @error('form.titulo')<div class="text-danger">{{ $message }}</div>@enderror
                <div class="mt-3" wire:key="demanda-editor-{{ $rascunho }}">
                    <x-forms.suporte-editor name="form.descricao" :value="$form->descricao" :rascunho="$rascunho" />
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-12 col-lg-9">
                    <div wire:key="demanda-participantes-{{ $rascunho }}" wire:ignore x-data="suporteParticipantes">
                        <label for="suporte-participantes" class="form-label mb-0">Usuários atrelados</label>
                        <select id="suporte-participantes" name="participantes[]" class="form-select"
                            x-ref="select" data-choices data-choices-removeItem multiple>
                            @foreach($usuarios as $user)
                                <option value="{{ $user->id }}" @selected(in_array($user->id, $form->participantes))>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('form.participantes')<div class="text-danger small">{{ $message }}</div>@enderror
                    <small class="text-muted">Quem publicar uma atividade também será incluído automaticamente.</small>
                    </div>
                    <div class="col-12 col-lg-3">
                        @can('admin')
                            <x-forms.input-field label="Horas gastas" name="form.horasGastas" type="number" wire:model="form.horasGastas" min="0" max="999999" step="1" />
                        @else
                            <label class="form-label">Horas gastas</label>
                            <div>{{ $demanda?->horas_gastas !== null ? $demanda->horas_gastas.' h' : '—' }}</div>
                        @endcan
                    </div>
                </div>
                <x-painel.suporte.uploads :arquivos="$arquivosSelecionados">
                    <x-slot:acoes>
                        <div class="row g-3">
                            @if($demanda)
                                <div class="col-12 col-md-auto">
                                    <x-forms.input-select label="Status" name="status" wire:model="status">
                                        @foreach($statuses as $item)<option value="{{ $item->value }}">{{ $item->label() }}</option>@endforeach
                                    </x-forms.input-select>
                                </div>
                            @endif
                            <div class="col-12 col-md-auto">
                                @can('admin')
                                    <x-forms.input-select label="Classificação" name="form.classificacao" wire:model="form.classificacao">
                                        <option value="">Não classificada</option>
                                        @foreach($classificacoes as $item)<option value="{{ $item->value }}">{{ $item->label() }}</option>@endforeach
                                    </x-forms.input-select>
                                @else
                                    <label class="form-label">Classificação</label>
                                    <div>{{ $demanda?->classificacao?->label() ?? 'Não classificada' }}</div>
                                @endcan
                            </div>
                        </div>
                    </x-slot:acoes>
                </x-painel.suporte.uploads>
                <div class="d-flex gap-2 mt-4" x-data>
                    <button class="btn btn-primary" wire:loading.attr="disabled" x-bind:disabled="$store.suporteUploads.total > 0">Salvar demanda</button>
                    @if($demanda && auth()->user()->hasPermissionTo('admin'))
                        <button type="button" class="btn btn-outline-danger" x-data x-on:click="Swal.fire({
                            title: 'Tem certeza?',
                            text: 'Excluir esta demanda e toda a conversa?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sim, excluir!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.excluir();
                            }
                        })">Excluir</button>
                    @endif
                    @if($demanda?->temPendencia() && auth()->user()->hasPermissionTo('admin'))
                        <button type="button" class="btn btn-outline-success" wire:click="resolver" wire:loading.attr="disabled">Marcar como resolvido</button>
                    @endif
                </div>
            </div>
        </form>
    @elseif($demanda)
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Descrição</h5></div>
            <div class="card-body">
                <div class="suporte-conteudo">{!! $demanda->descricao !!}</div>
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mt-4">
                    <h6 class="mb-0">Anexos</h6>
                    <div class="d-flex align-items-end gap-2">
    <x-forms.input-select label="Status" name="status" wire:model="status">
        @foreach($statuses as $item)<option value="{{ $item->value }}">{{ $item->label() }}</option>@endforeach
    </x-forms.input-select>
                        <div>
                            @can('admin')
                                <x-forms.input-select label="Classificação" name="form.classificacao" wire:model="form.classificacao">
                                    <option value="">Não classificada</option>
                                    @foreach($classificacoes as $item)<option value="{{ $item->value }}">{{ $item->label() }}</option>@endforeach
                                </x-forms.input-select>
                            @else
                                <label class="form-label">Classificação</label>
                                <div>{{ $demanda?->classificacao?->label() ?? 'Não classificada' }}</div>
                            @endcan
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary mt-3" wire:click="salvar" wire:loading.attr="disabled">Salvar demanda</button>
                <small class="d-block text-black my-2"><i class="ri-lock-line"></i> Reabra para editar o conteúdo. Novas atividades e respostas continuam disponíveis.</small>
                @if($demanda->temPendencia() && auth()->user()->hasPermissionTo('admin'))
                    <button type="button" class="btn btn-outline-success mb-2" wire:click="resolver" wire:loading.attr="disabled">Marcar como resolvido</button>
                @endif
                <x-painel.suporte.arquivos :arquivos="$demanda->arquivos" />
                @if($demanda->arquivos->where('tipo', 'anexo')->isEmpty())<small class="text-muted">Nenhum anexo.</small>@endif
                <div class="row g-3 mt-3">
                <div class="col-12 col-lg-9">
                <h6>Usuários atrelados</h6>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($demanda->participantes as $user)<span class="badge bg-secondary-subtle text-secondary">{{ $user->name }}</span>
                    @empty<small class="text-muted">Nenhum participante.</small>@endforelse
                </div>
                </div>
                <div class="col-12 col-lg-3">
                    @can('admin')
                        <x-forms.input-field label="Horas gastas" name="form.horasGastas" type="number" wire:model="form.horasGastas" min="0" max="999999" step="1" />
                    @else
                        <label class="form-label">Horas gastas</label>
                        <div>{{ $demanda?->horas_gastas !== null ? $demanda->horas_gastas.' h' : '—' }}</div>
                    @endcan
                </div>
                </div>
            </div>
        </div>
    @endif
    @if($demanda)
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Atividades</h5></div>
            <div class="card-body">
                @forelse($demanda->atividades as $atividade)
                    <x-painel.suporte.atividade :atividade="$atividade" :demanda="$demanda" />
                    @foreach($atividade->respostas as $resposta)
                        <div class="ms-3 ms-md-5 border-start ps-3"><x-painel.suporte.atividade :atividade="$resposta" :demanda="$demanda" /></div>
                    @endforeach
                @empty
                    <p class="text-muted mb-0">Nenhuma atividade publicada.</p>
                @endforelse
            </div>
        </div>
        <livewire:suporte.atividade-form :demanda="$demanda" :key="'atividades-'.$demanda->uid" />
    @endif
</div>
@script
<script>
    $wire.on('suporte-erro', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            iconColor: 'white',
            customClass: { popup: 'colored-toast' },
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            showCloseButton: true,
        });
        Toast.fire({ icon: 'error', title: event.message });
    });
</script>
@endscript
