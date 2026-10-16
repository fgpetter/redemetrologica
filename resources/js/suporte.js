document.addEventListener('alpine:init', () => {
    Alpine.store('suporteUploads', { total: 0 });

    Alpine.data('suporteParticipantes', () => ({
        choices: null,

        init() {
            this.choices = new Choices(this.$refs.select, {
                removeItemButton: true,
                allowHTML: false,
                placeholder: true,
                placeholderValue: 'Selecione os usuários',
                searchPlaceholderValue: 'Buscar usuário',
                noResultsText: 'Nenhum usuário encontrado',
                noChoicesText: 'Nenhum usuário disponível',
                itemSelectText: 'Selecionar',
            });
            this.$refs.select.addEventListener('change', () => {
                this.$wire.set('form.participantes', Alpine.raw(this.choices).getValue(true), false);
            });
            this.$watch('$wire.form.participantes', (participantes) => {
                const choices = Alpine.raw(this.choices);
                choices.removeActiveItems();
                choices.setChoiceByValue(participantes.map(String));
            });
        },

        destroy() {
            Alpine.raw(this.choices)?.destroy();
        },
    }));

    Alpine.data('suporteAnexos', () => ({
        enviando: false,
        erro: '',

        async enviar(input) {
            const files = Array.from(input.files);
            if (files.length === 0 || this.enviando) {
                return;
            }
            this.enviando = true;
            this.erro = '';
            this.$store.suporteUploads.total++;
            try {
                for (const file of files) {
                    if (file.size > 5 * 1024 * 1024) {
                        throw new Error('Cada arquivo deve ter no máximo 5 MB.');
                    }
                    await new Promise((resolve, reject) => {
                        this.$wire.upload('uploads', file, resolve, () => reject(new Error('Não foi possível enviar o arquivo. Confira o formato e tente novamente.')));
                    });
                }
            } catch (error) {
                this.erro = error.message;
            } finally {
                this.enviando = false;
                this.$store.suporteUploads.total--;
                input.value = '';
            }
        },
    }));

    Alpine.data('suporteEditor', (config) => ({
        editor: null,
        erro: '',
        pendente: false,

        async init() {
            try {
                const editor = await ClassicEditor.create(this.$el.querySelector('textarea'), {
                    language: 'pt-br',
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'imageUpload', 'blockQuote', 'insertTable', '|', 'undo', 'redo'],
                });
                if (!this.$el.isConnected) {
                    await editor.destroy();
                    return;
                }
                this.editor = editor;
                editor.setData(config.valor);
                editor.model.document.on('change:data', () => {
                    this.$wire.set(config.campo, editor.getData(), false);
                });
                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                    const controller = new AbortController();
                    return {
                        upload: async () => {
                            const file = await loader.file;
                            if (file.size > 5 * 1024 * 1024) {
                                throw new Error('Cada imagem deve ter no máximo 5 MB.');
                            }
                            const body = new FormData();
                            body.append('upload', file);
                            const response = await fetch(config.uploadUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                                body,
                                signal: controller.signal,
                            });
                            const result = await response.json();
                            if (!response.ok || !result.url) {
                                throw new Error(result.error?.message ?? result.message ?? 'Não foi possível enviar a imagem.');
                            }
                            return { default: result.url };
                        },
                        abort: () => controller.abort(),
                    };
                };
                editor.plugins.get('PendingActions').on('change:hasAny', (event, name, pending) => {
                    if (pending !== this.pendente) {
                        this.$store.suporteUploads.total += pending ? 1 : -1;
                        this.pendente = pending;
                    }
                });
            } catch (error) {
                this.erro = 'Não foi possível iniciar o editor. Recarregue a página.';
                console.error(error);
            }
        },

        destroy() {
            if (this.pendente) {
                this.$store.suporteUploads.total--;
                this.pendente = false;
            }
            Alpine.raw(this.editor)?.destroy();
        },
    }));
});
