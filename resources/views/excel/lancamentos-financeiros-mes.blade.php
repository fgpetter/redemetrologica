<table>
    <tr>
        <td><strong>Tipo: Fluxo Realizado </strong> </td>
        <td><strong>Período: {{ filled($inicio) ? $inicio : '' }} </strong>
        </td>
        <td></td>
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
            <th><strong>Pagamento</strong></th>
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
                <td>{{ \Carbon\Carbon::parse($lancamento->data_pagamento)->format('d/m/Y') }}</td>
                <td>{{ $lancamento->nota_fiscal }}</td>
                <td>{{ $lancamento->nome_razao }}</td>
                <td>{{ $lancamento->plano_conta }}</td>
                <td>{{ $lancamento->centro_custo }}</td>
                <td>
                    @if ($lancamento->credito)
                        @php $totalCreditos += $lancamento->credito; @endphp
                        {{ $lancamento->credito }}
                    @endif
                </td>
                <td>
                    @if ($lancamento->debito)
                        @php $totalDebitos += $lancamento->debito; @endphp
                        {{ $lancamento->debito }}
                    @endif
                </td>
                <td data-format="@">{{ $lancamento->consiliacao }}</td>
            </tr>
        @endforeach
        <tr></tr>
        <tr></tr>
    </tbody>
    <tfoot>
        <tr>
            <td><strong>Créditos</strong></td>
            <td><strong>{{ $totalCreditos }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Débitos</strong></td>
            <td><strong>{{ $totalDebitos }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Saldo Final</strong></td>
            <td>
                <strong>
                    {{ $totalCreditos - $totalDebitos }}
                </strong>
            </td>
        </tr>
    </tfoot>
</table>
