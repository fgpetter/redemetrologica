<div>
    <div class="offcanvas offcanvas-end" style="--tb-offcanvas-width: min(90vw, 720px);" tabindex="-1" id="editar-inscrito-offcanvas" wire:ignore.self
        aria-labelledby="editarInscritoOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="editarInscritoOffcanvasLabel">Editar Participante</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        @if ($carregando)
            <div class="offcanvas-body d-flex flex-column align-items-center justify-content-center py-5"
                wire:key="editar-inscrito-loading-{{ $inscritoId }}" style="min-height: 12rem;">
                <div class="text-center text-muted">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Carregando</span>
                    </div>
                    <p class="small mb-0">Carregando dados do participante…</p>
                </div>
            </div>
            <div class="border-top bg-light p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">FECHAR</button>
            </div>
        @elseif ($inscrito)
            <div class="offcanvas-body" wire:key="editar-inscrito-body-{{ $inscrito->id }}">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <strong>Empresa</strong><br>
                        {{ $inscrito->empresa->nome_razao ?? '—' }}<br>
                        <strong>Email:</strong> {{ $inscrito->empresa->email ?? '—' }}<br>
                        <strong>Telefone:</strong> {{ $inscrito->empresa->telefone ?? '—' }}
                    </div>
                    <div class="text-end">
                        @if ($inscrito->empresa && $inscrito->empresa->deleted_at !== null)
                            <span class="text-secondary">Empresa excluída, somente leitura</span>
                        @elseif ($inscrito->empresa)
                            <a href="{{ route('pessoa-insert', $inscrito->empresa->uid) }}" class="link-primary fw-medium">
                                Editar Empresa
                                <i class="ri-arrow-right-line align-middle"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <strong>Responsável pela inscrição</strong><br>
                        {{ $inscrito->pessoa->nome_razao ?? '—' }}<br>
                        <strong>CPF/CNPJ:</strong> {{ $inscrito->pessoa->cpf_cnpj ?? '—' }}<br>
                        <strong>Email:</strong> {{ $inscrito->pessoa->email ?? '—' }}<br>
                        <strong>Telefone:</strong> {{ $inscrito->pessoa->telefone ?? '—' }}
                    </div>
                    <div class="text-end">
                        @if ($inscrito->pessoa->deleted_at !== null)
                            <span class="text-secondary">Pessoa excluída, somente leitura</span>
                        @else
                            <a href="{{ route('pessoa-insert', $inscrito->pessoa->uid) }}" class="link-primary fw-medium d-block mb-2">
                                Editar Responsável
                                <i class="ri-arrow-right-line align-middle"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-alterar-responsavel-{{ $inscrito->id }}" aria-expanded="false"
                                aria-controls="collapse-alterar-responsavel-{{ $inscrito->id }}">
                                Alterar Responsável
                            </button>
                        @endif
                    </div>
                </div>

                @if (($inscrito->pessoa?->deleted_at ?? null) === null)
                    <div class="collapse mt-3" id="collapse-alterar-responsavel-{{ $inscrito->id }}">
                        <div class="card card-body border">
                            <p class="small text-muted mb-2">Selecione o novo responsável (pessoa física).</p>
                            <div wire:ignore class="mb-3" wire:key="novo-responsavel-ts-{{ $inscrito->id }}">
                                <label class="form-label" for="novo-responsavel-id">Novo responsável</label>
                                <select id="novo-responsavel-id" autocomplete="off"
                                    data-placeholder="Digite para pesquisar...">
                                    <option value="">Selecione...</option>
                                    @foreach ($pessoas as $pessoa)
                                        <option value="{{ $pessoa['id'] }}">{{ $pessoa['cpf_cnpj'] }} | {{ $pessoa['nome_razao'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('novoResponsavelId')
                                <span class="text-danger small d-block mb-2">{{ $message }}</span>
                            @enderror
                            <div>
                                <button type="button" class="btn btn-primary btn-sm"
                                    @click.prevent="$wire.alterarResponsavel(document.getElementById('novo-responsavel-id').value)">
                                    Confirmar alteração
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <hr class="my-4">

                <h6 class="mb-3">Dados da inscrição</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-forms.input-field wire:model.lazy="form.valor" name="form.valor" label="Valor" mask="money" />
                        @error('form.valor')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12">
                        <x-forms.input-textarea wire:model.lazy="form.informacoes_inscricao" name="form.informacoes_inscricao"
                            label="Informações da inscrição"></x-forms.input-textarea>
                        @error('form.informacoes_inscricao')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <x-forms.input-field wire:model.lazy="form.responsavel_tecnico" name="form.responsavel_tecnico"
                            label="Responsável técnico" />
                        @error('form.responsavel_tecnico')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <x-forms.input-field wire:model.lazy="form.telefone" name="form.telefone" label="Telefone"
                            mask="telefone" />
                        @error('form.telefone')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <x-forms.input-field type="email" wire:model.lazy="form.email" name="form.email" label="Email" />
                        @error('form.email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <x-forms.input-field wire:model.lazy="form.tag_senha" name="form.tag_senha" label="Tag senha" />
                        @error('form.tag_senha')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="mb-3">Dados do laboratório</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <x-forms.input-field wire:model.lazy="form.labNome" name="form.labNome" label="Nome do Laboratório"
                            required />
                        @error('form.labNome')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3">Endereço</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <x-forms.input-field wire:model.blur="form.cep" name="form.cep" label="CEP" required mask="cep" />
                        @error('form.cep')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-9">
                        <x-forms.input-field wire:model.lazy="form.endereco" name="form.endereco" label="Endereço"
                            required />
                        @error('form.endereco')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <x-forms.input-field wire:model.lazy="form.bairro" name="form.bairro" label="Bairro" required />
                        @error('form.bairro')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <x-forms.input-field wire:model.lazy="form.cidade" name="form.cidade" label="Cidade" required />
                        @error('form.cidade')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <x-forms.input-field wire:model.lazy="form.uf" name="form.uf" label="UF" required maxlength="2" />
                        @error('form.uf')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <x-forms.input-field wire:model.lazy="form.complemento" name="form.complemento" label="Complemento" />
                        @error('form.complemento')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="border-top bg-light p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">FECHAR</button>
                <button type="button" class="btn btn-primary" wire:click="salvar">SALVAR</button>
            </div>
        @else
            <div class="offcanvas-body d-flex align-items-center justify-content-center py-5" wire:key="editar-inscrito-empty">
                <p class="text-muted small mb-0 text-center">Nenhum participante selecionado.</p>
            </div>
            <div class="border-top bg-light p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">FECHAR</button>
            </div>
        @endif
    </div>

    @script
        <script>
            let tomSelectResponsavel = null;

            function destroyTomSelectResponsavel() {
                if (tomSelectResponsavel) {
                    tomSelectResponsavel.destroy();
                    tomSelectResponsavel = null;
                }
            }

            function initTomSelectResponsavel() {
                destroyTomSelectResponsavel();
                const select = document.getElementById('novo-responsavel-id');
                if (!select || typeof TomSelect === 'undefined') {
                    return;
                }
                tomSelectResponsavel = new TomSelect(select, {
                    create: false,
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    },
                    placeholder: select.getAttribute('data-placeholder') ?? 'Selecione...',
                    onChange(value) {
                        select.value = value ?? '';
                        select.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        select.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    },
                });
            }

            $wire.on('offcanvas:open', () => {
                const el = document.getElementById('editar-inscrito-offcanvas');
                if (!el) {
                    return;
                }
                const instance = window.bootstrap.Offcanvas.getOrCreateInstance(el);
                instance.show();
                $wire.carregarInscrito();
            });

            $wire.on('editar-inscrito:carregado', () => {
                setTimeout(() => initTomSelectResponsavel(), 80);
            });

            $wire.on('offcanvas:close', () => {
                destroyTomSelectResponsavel();
                const el = document.getElementById('editar-inscrito-offcanvas');
                if (!el) {
                    return;
                }
                const instance = window.bootstrap.Offcanvas.getInstance(el);
                instance?.hide();
            });
        </script>
    @endscript
</div>
