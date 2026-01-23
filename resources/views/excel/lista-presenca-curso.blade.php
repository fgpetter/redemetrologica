<table>
  <thead>
    <tr>
      <th colspan="2"> <strong>Curso: {{ $agendacurso->curso->descricao }}</strong> </th>
    </tr>
    <tr>
      <th colspan="2"><strong>Data Início: {{ \Carbon\Carbon::parse($agendacurso->data_inicio)->format('d/m/Y') }}</strong></th>
      </tr>
    <tr></tr>
    <tr>
      <th>Inscrito</th>
      <th>Empresa</th>
      <th>Email</th>
      <th>Presença</th>
    </tr>
  </thead>
  <tbody>
    @foreach($agendacurso->inscritos as $inscrito)
      @if($inscrito->valor)
        <tr>
          <td>{{ $inscrito->nome }}</td>
          <td>{{ $inscrito->empresa?->nome_razao ?? 'Individual' }}</td>
          <td>{{ $inscrito->email }}</td>
          <td></td>
        </tr>
      @endif
    @endforeach
  </tbody>
</table>