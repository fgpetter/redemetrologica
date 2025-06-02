<table>
    <tr>
        <td><strong>Tipo: Fluxo Realizado </strong> </td>
        <td><strong>Data Inicial: {{ isset($inicio) ? \Carbon\Carbon::parse($inicio)->format('d/m/Y') : '' }} </strong>
        </td>
        <td><strong>Data Final: {{ isset($fim) ? \Carbon\Carbon::parse($fim)->format('d/m/Y') : '' }} </strong> </td>
    </tr>
    <tr>
        <td><strong>Banco: BANCO DO BRASIL - 5023-7 </strong> </td>
        <td></td>
        <td></td>
    </tr>
    <tr></tr>
    <tr></tr>
    <thead>
        <tr>
            <th><strong>Vencimento</strong></th>
            <th><strong>Nota Fiscal</strong></th>
            <th><strong>Pessoa</strong></th>
            <th><strong>Conta</strong></th>
            <th><strong>Modalidade</strong></th>
            <th><strong>Crédito</strong></th>
            <th><strong>Débito</strong></th>
            <th><strong>Cheque/Comp. Pagamento</strong></th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalCreditos = 0;
            $totalDebitos = 0;
        @endphp
        @foreach ($lancamentos as $lancamento)
            <tr>
                <td>{{ \Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') }}</td>
                <td>{{ $lancamento->nota_fiscal }}</td>
                <td>{{ $lancamento->nome_razao }}</td>
                <td>{{ $lancamento->plano_conta }}</td>
                <td>{{ $lancamento->centro_custo }}</td>
                <td>
                    @if ($lancamento->credito)
                        @php $totalCreditos += $lancamento->credito; @endphp
                        {{ number_format($lancamento->credito, 2, ',', '.') }}
                    @endif
                </td>
                <td>
                    @if ($lancamento->debito)
                        @php $totalDebitos += $lancamento->debito; @endphp
                        {{ number_format($lancamento->debito, 2, ',', '.') }}
                    @endif
                </td>
                <td>{{ $lancamento->consiliacao }}</td>
            </tr>
        @endforeach
        <tr></tr>
        <tr></tr>
    </tbody>
    <tfoot>
        <tr>
            <td><strong>Créditos</strong></td>
            <td><strong>{{ number_format($totalCreditos, 2, ',', '.') }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Débitos</strong></td>
            <td><strong>{{ number_format($totalDebitos, 2, ',', '.') }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Saldo Final</strong></td>
            <td>
                <strong>
                    {{ number_format($totalCreditos - $totalDebitos, 2, ',', '.') }}
                </strong>
            </td>
        </tr>
    </tfoot>
</table>
