<table>
  <thead>
  <tr>
      <th>Inscrição</th>
      <th>Razão Social</th>
      <th>CNPJ</th>
      <th>Responsável</th>
      <th>Email Responsável</th>
      <th>Laboratório</th>
      <th>Associado</th>
      <th>Endereço</th>
      <th>CEP</th>
      <th>Bairro</th>
      <th>Cidade</th>
      <th>Estado</th>
      <th>Participante</th>
      <th>Email Participante</th>
      <th>Fone Participante</th>
      <th>Informações</th>
  </tr>
  </thead>
  <tbody>
  @foreach($inscritos as $inscrito)
      <tr>
          <td>{{ $inscrito->id }}</td>
          <td>{{ $inscrito->empresa->nome_razao }}</td>
          <td>{{ $inscrito->empresa->cpf_cnpj }}</td>
          <td>{{ $inscrito->pessoa->nome_razao }}</td>
          <td>{{ $inscrito->pessoa->email }}</td>
          <td>{{ $inscrito->laboratorio->nome }}</td>
          <td>{{ ($inscrito->empresa->associado) ? 'SIM' : 'NÃO' }}</td>
          <td>{{ $inscrito->laboratorio->endereco->endereco .' - '. $inscrito->laboratorio->endereco->complemento }}</td>
          <td>{{ $inscrito->laboratorio->endereco->cep }}</td>
          <td>{{ $inscrito->laboratorio->endereco->bairro }}</td>
          <td>{{ $inscrito->laboratorio->endereco->cidade }}</td>
          <td>{{ $inscrito->laboratorio->endereco->estado }}</td>
          <td>{{ $inscrito->laboratorio->responsavel_tecnico }}</td>
          <td>{{ $inscrito->laboratorio->email }}</td>
          <td>{{ $inscrito->laboratorio->telefone }}</td>
          <td>{{ $inscrito->informacoes_inscricao }}</td>
      </tr>
  @endforeach
  </tbody>
</table>