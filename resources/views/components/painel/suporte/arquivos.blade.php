@props(['arquivos'])
<div class="d-flex flex-wrap gap-2 mt-2">
    @foreach($arquivos->where('tipo', 'anexo') as $arquivo)
        <a class="btn btn-sm btn-light border" href="{{ route('suporte-arquivo-download', $arquivo->uid) }}">
            <i class="ri-attachment-line"></i> {{ $arquivo->nome }}
        </a>
    @endforeach
</div>
