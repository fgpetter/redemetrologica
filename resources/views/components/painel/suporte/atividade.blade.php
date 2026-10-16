@props(['atividade', 'demanda'])
<div class="border rounded p-3 mb-2" wire:key="atividade-{{ $atividade->uid }}" id="atividade-{{ $atividade->uid }}">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
        <div>
            <strong>{{ $atividade->owner?->name ?? 'Usuário indisponível' }}</strong>
            <small class="text-muted ms-2">{{ $atividade->created_at->format('d/m/Y H:i') }}</small>
            @if($atividade->updated_at->gt($atividade->created_at))
                <small class="text-muted ms-1">(editada em {{ $atividade->updated_at->format('d/m/Y H:i') }})</small>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if(! $atividade->parent_id)
                <button type="button" class="btn btn-sm btn-link p-0"
                    wire:click="$dispatch('suporte-responder', { uid: '{{ $atividade->uid }}' })">Responder</button>
            @endif
            @if(! $demanda->status->encerrada())
                @if($atividade->podeEditar(auth()->user()))
                    <button type="button" class="btn btn-sm btn-link p-0"
                        wire:click="$dispatch('suporte-editar-atividade', { uid: '{{ $atividade->uid }}' })">Editar</button>
                @endif
                @if(auth()->user()->hasPermissionTo('admin'))
                    <button type="button" class="btn btn-sm btn-link text-danger p-0"
                        x-data x-on:click="Swal.fire({
                            title: 'Tem certeza?',
                            text: 'Excluir esta atividade e suas respostas?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sim, excluir!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.excluirAtividade('{{ $atividade->uid }}');
                            }
                        })">Excluir</button>
                @endif
            @endif
        </div>
    </div>
    <div class="suporte-conteudo">{!! $atividade->descricao !!}</div>
    <x-painel.suporte.arquivos :arquivos="$atividade->arquivos" />
</div>
