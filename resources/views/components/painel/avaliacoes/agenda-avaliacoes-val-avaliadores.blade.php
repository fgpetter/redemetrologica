<div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nome do Avaliador</th>
                <th class="text-end">Total a Receber</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($totalavaliadores as $total_avaliador)
                <tr>
                    <td>{{ $total_avaliador['nome'] }}</td>
                    <td class="text-end">R$ {{ number_format($total_avaliador['total'], 2, ',', '.') }}</td>
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
