@php
    $isAssociado = $isAssociado ?? $this->isAssociado;
    $blocosMeta = $valores_inscricao
        ? $valores_inscricao->map(fn ($valorItem) => [
            'id' => (int) $valorItem->id,
            'analistas' => (int) ($valorItem->analistas ?? 0),
        ])->values()->all()
        : [];
    $maxAnalistas = max((int) ($valores_inscricao?->max('analistas') ?? 0), (int) ($numero_analistas ?? 0));
@endphp
<form wire:submit.prevent="salvar">
    <div class="row g-3"
        x-data="{
            requerAnalistas: @js($requer_analistas),
            blocosMeta: @js($blocosMeta),
            blocoSelecionado: $wire.entangle('form.blocoSelecionado').live,
            blocosSelecionados: $wire.entangle('form.blocosSelecionados').live,
            analistas: $wire.entangle('form.analistas').live,
            analistasIniciais: @js((int) ($numero_analistas ?? 0)),
            get idsSelecionados() {
                if (this.requerAnalistas) {
                    return this.blocoSelecionado ? [Number(this.blocoSelecionado)] : [];
                }

                if (!Array.isArray(this.blocosSelecionados)) {
                    return [];
                }

                return this.blocosSelecionados.map((id) => Number(id)).filter(Boolean);
            },
            get quantidadeAnalistas() {
                if (!this.requerAnalistas) {
                    return 0;
                }

                if (this.idsSelecionados.length === 0) {
                    return this.analistasIniciais;
                }

                return this.idsSelecionados.reduce((maximo, idSelecionado) => {
                    const bloco = this.blocosMeta.find((item) => Number(item.id) === Number(idSelecionado));
                    return Math.max(maximo, Number(bloco?.analistas ?? 0));
                }, 0);
            },
            sincronizarAnalistas() {
                const totalAnalistas = this.quantidadeAnalistas;
                if (!Array.isArray(this.analistas)) {
                    this.analistas = [];
                }

                if (this.analistas.length < totalAnalistas) {
                    for (let i = this.analistas.length; i < totalAnalistas; i++) {
                        this.analistas.push({ nome: '', email: '', telefone: '' });
                    }
                }

                if (this.analistas.length > totalAnalistas) {
                    this.analistas = this.analistas.slice(0, totalAnalistas);
                }
            },
            init() {
                this.sincronizarAnalistas();
                this.$watch('blocoSelecionado', () => {
                    this.analistasIniciais = 0;
                    this.sincronizarAnalistas();
                });
                this.$watch('blocosSelecionados', () => {
                    this.sincronizarAnalistas();
                });
            },
        }">
        <div class="col-12 col-xl-6">
            <x-forms.input-field wire:model="form.laboratorio.nome" name="lab_nome" label="Laboratório" required />
            @error('form.laboratorio.nome') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-12 col-xl-6">
            <x-forms.input-field wire:model="form.laboratorio.responsavel_tecnico" name="lab_resp" label="Responsável Técnico" required />
            @error('form.laboratorio.responsavel_tecnico') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-12 col-sm-6">
            <x-forms.input-field wire:model="form.laboratorio.telefone" name="lab_tel" label="Telefone"
                mask="telefone" maxlength="15" />
            @error('form.laboratorio.telefone') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-12 col-sm-6">
            <x-forms.input-field wire:model="form.laboratorio.email" name="lab_email" label="E-mail" type="email" required />
            @error('form.laboratorio.email') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="col-12"><hr class="my-2"><h6 class="text-muted">Endereço do Laboratório</h6></div>
        
        <div class="col-md-4">
             <x-forms.input-field wire:model="form.laboratorio.endereco.cep" name="lab_cep" label="CEP"
                wire:blur="buscaCep" maxlength="9" x-mask="99999-999" required />
             @error('form.laboratorio.endereco.cep') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-6">
             <x-forms.input-field wire:model="form.laboratorio.endereco.endereco" name="lab_end" label="Endereço" required />
             @error('form.laboratorio.endereco.endereco') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-2">
             <x-forms.input-field wire:model="form.laboratorio.endereco.numero" name="lab_num" label="Número" required />
             @error('form.laboratorio.endereco.numero') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
         <div class="col-md-6">
             <x-forms.input-field wire:model="form.laboratorio.endereco.complemento" name="lab_comp" label="Complemento" />
        </div>
        <div class="col-md-6">
             <x-forms.input-field wire:model="form.laboratorio.endereco.bairro" name="lab_bairro" label="Bairro" required />
             @error('form.laboratorio.endereco.bairro') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-6">
             <x-forms.input-field wire:model="form.laboratorio.endereco.cidade" name="lab_cid" label="Cidade" required />
             @error('form.laboratorio.endereco.cidade') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-2">
             <x-forms.input-field wire:model="form.laboratorio.endereco.uf" name="lab_uf" label="UF" maxlength="2" style="text-transform: uppercase;" required />
             @error('form.laboratorio.endereco.uf') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="col-12">
            <x-forms.input-textarea wire:model="informacoes_inscricao" name="lab_obs" label="Observações adicionais">
            </x-forms.input-textarea>
        </div>

        <hr class="my-3"></div>

        <div class="col-12">
            @include('painel.painel-cliente.partials.tutorial-help', ['items' => [[
                'section' => 'blocos-analistas',
                'title' => 'Como selecionar os blocos e cadastrar os analistas?',
            ]]])
        </div>

        <!-- Dados dos Analistas -->
        <div class="col-12" x-cloak x-show="requerAnalistas && quantidadeAnalistas > 0">
                <div class="card border border-info">
                    <div class="card-header bg-info-subtle">
                        <h6 class="text-info my-1">
                            <i class="ri-user-star-line me-2"></i>
                            <span x-text="quantidadeAnalistas === 1 ? 'Dados do Analista' : 'Dados dos Analistas'"></span>
                        </h6>
                        <small class="text-muted">
                            Preencha os dados dos analistas participantes. Todos os campos são obrigatórios.
                        </small>
                    </div>
                    <div class="card-body">
                        @for ($i = 0; $i < $maxAnalistas; $i++)
                            <div class="row g-3 {{ $i > 0 ? 'mt-3 pt-3 border-top' : '' }}" x-cloak x-show="quantidadeAnalistas > {{ $i }}">
                                <div class="col-12">
                                    <strong class="text-primary-emphasis small text-uppercase">Analista {{ $i + 1 }}</strong>
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-forms.input-field wire:model="form.analistas.{{ $i }}.nome" name="analista_{{ $i }}_nome" label="Nome" x-bind:required="quantidadeAnalistas > {{ $i }}" />
                                    @error("analistas.{$i}.nome") <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-forms.input-field wire:model="form.analistas.{{ $i }}.email" name="analista_{{ $i }}_email" label="E-mail" type="email" x-bind:required="quantidadeAnalistas > {{ $i }}" />
                                    @error("analistas.{$i}.email") <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-forms.input-field wire:model="form.analistas.{{ $i }}.telefone" name="analista_{{ $i }}_telefone" label="Telefone"
                                        mask="telefone" maxlength="15"
                                        x-bind:required="quantidadeAnalistas > {{ $i }}" />
                                    @error("analistas.{$i}.telefone") <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
        </div>

        <!-- Blocos de inscrição -->
        @if ($valores_inscricao && $valores_inscricao->isNotEmpty())
            <div class="col-12">
            <div class="col-12">
                <div class="card border">
                    <div class="card-header bg-primary-subtle">
                        <h6 class=" text-primary">
                            <i class="ri-checkbox-multiple-line me-2"></i>{{ $requer_analistas ? 'Selecione o Bloco de Inscrição' : 'Selecione os Blocos de Inscrição' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($valores_inscricao as $valorItem)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="border rounded p-3 h-100 shadow-sm d-flex align-items-start">
                                        <div class="form-check mb-0">
                                            @if ($requer_analistas)
                                                <input class="form-check-input fs-5 mt-0" type="radio"
                                                    wire:model.live="form.blocoSelecionado"
                                                    name="bloco_selecionado"
                                                    value="{{ $valorItem->id }}"
                                                    id="bloco_{{ $valorItem->id }}">
                                            @else
                                                <input class="form-check-input fs-5 mt-0" type="checkbox"
                                                    wire:model.live="form.blocosSelecionados"
                                                    value="{{ $valorItem->id }}"
                                                    id="bloco_{{ $valorItem->id }}">
                                            @endif
                                        </div>
                                        <label class="form-check-label ms-3 flex-grow-1" for="bloco_{{ $valorItem->id }}" style="cursor: pointer;">
                                            <strong class="d-block mb-2">{{ $valorItem->descricao }}</strong>
                                            
                                            <span class="badge bg-primary fs-6">
                                                R$ {{ number_format($isAssociado && $valorItem->valor_assoc ? $valorItem->valor_assoc : $valorItem->valor, 2, ',', '.') }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if(!$isAssociado)
                            <div class="mt-3 p-2 bg-light bg-opacity-50 border rounded d-flex align-items-center text-muted small">
                                <i class="ri-vip-crown-2-fill text-warning fs-5 me-2"></i>
                                <span>
                                    <strong>Valores especiais para associados:</strong>
                                    @foreach($valores_inscricao as $v)
                                        @if($v->valor_assoc)
                                            {{ $v->descricao }} <span class="text-primary fw-bold">R$ {{ number_format($v->valor_assoc, 2, ',', '.') }}</span>@if(!$loop->last) <span class="mx-1">•</span> @endif
                                        @endif
                                    @endforeach
                                </span>
                            </div>
                        @endif

                        @if ($requer_analistas)
                            @error('form.blocoSelecionado') <span class="text-danger d-block mt-3">{{ $message }}</span> @enderror
                        @else
                            @error('form.blocosSelecionados') <span class="text-danger d-block mt-3">{{ $message }}</span> @enderror
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Solicita certificado -->
        <div class="col-12">
            @include('painel.painel-cliente.partials.tutorial-help', ['items' => [[
                'section' => 'certificado',
                'title' => 'Como solicitar o Certificado de Desempenho?',
            ]]])
        </div>
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="form-check form-switch form-switch-lg">
                        <input class="form-check-input" type="checkbox" wire:model.live="solicitar_certificado" id="certificado_switch">
                        <label class="form-check-label" for="certificado_switch">
                            <strong><i class="ri-award-line me-2"></i>Solicitar Certificado de Desempenho</strong> 
                            <span class="badge bg-warning text-dark ms-2">+ R$ 300,00</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    
</form>
