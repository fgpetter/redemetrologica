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
      <th>Responsável Técnico</th>
      <th>Email Resp Téc</th>
      <th>Fone Resp Téc</th>
      <th>Informações</th>
      <th>TAG Senha</th>
      @if($agendainterlab->certificado === 'PARTICIPANTE')
          <th>Nome Analista</th>
      @endif
  </tr>
  </thead>
  <tbody>
  @foreach($inscritos as $inscrito)
      @php
          $linhas = $agendainterlab->certificado === 'PARTICIPANTE'
              ? $inscrito->analistas
              : collect([null]);
      @endphp
      @foreach($linhas as $analista)
          <tr>
              <td>{{ $inscrito->id }}</td>
              <td>{{ $inscrito->empresa->nome_razao }}</td>
              <td>{{ $inscrito->empresa->cpf_cnpj }}</td>
              <td>{{ $inscrito->pessoa->nome_razao }}</td>
              <td>{{ $inscrito->pessoa->email }}</td>
              <td>{{ $inscrito->laboratorio->nome }}</td>
              <td>{{ ($inscrito->empresa->associado) ? 'SIM' : 'NÃO' }}</td>
              <td>{{ $inscrito->laboratorio->endereco->endereco ?? '--' }}{{ !empty($inscrito->laboratorio->endereco->complemento) ? ' - ' . $inscrito->laboratorio->endereco->complemento : '' }}</td>
              <td>{{ $inscrito->laboratorio->endereco->cep ?? '--' }}</td>
              <td>{{ $inscrito->laboratorio->endereco->bairro ?? '--' }}</td>
              <td>{{ $inscrito->laboratorio->endereco->cidade ?? '--' }}</td>
              <td>{{ $inscrito->laboratorio->endereco->uf ?? '--' }}</td>
              <td>{{ $inscrito->responsavel_tecnico }}</td>
              <td>{{ $inscrito->email }}</td>
              <td>{{ $inscrito->telefone }}</td>
              <td>{{ $inscrito->informacoes_inscricao }}</td>
              <td>{{ $agendainterlab->certificado === 'PARTICIPANTE' ? $analista->tag_senha : $inscrito->tag_senha }}</td>
              @if($agendainterlab->certificado === 'PARTICIPANTE')
                  <td>{{ $analista->nome }}</td>
              @endif
          </tr>
      @endforeach
  @endforeach
  </tbody>
</table>
