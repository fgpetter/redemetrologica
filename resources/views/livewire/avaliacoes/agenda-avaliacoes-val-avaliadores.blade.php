
<div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nome do Avaliador</th>
                <th class="text-end">Total a Receber</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($avaliacao->areas as $area)
                <tr>
                    <td>{{ $area->avaliador->pessoa->nome_razao}}</td>
                    <td class="text-end">R$ {{ number_format($area->valor_avaliador, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">Nenhum avaliador encontrado.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th class="text-end">Total Geral:</th>
                <th class="text-end">R$ {{ number_format($avaliacao->areas->sum('valor_avaliador'), 2, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

</div>
