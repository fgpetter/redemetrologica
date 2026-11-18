@extends('layouts.email')

@section('content')
<h1 style="font-size: 20px;">Instruções para o instrutor do curso: {{ $dados['curso'] }}</h1>

<p><strong>Nome do Curso:</strong> {{ $dados['curso'] }}<br>
<strong>Tipo de curso:</strong> {{ $dados['tipo'] }}<br>
<strong>Data:</strong> {{ $dados['data'] }} <br>
<strong>Horário:</strong> {{ $dados['horario'] }}<br>
<strong>Local/Empresa:</strong> {{ $dados['local'] }}<br>
<strong>Pessoa de contato:</strong> {{ $dados['contato'] }}<br>
<strong>Fone/Fax:</strong> {{ $dados['telefone'] }}<br>
<strong>Nome do Instrutor:</strong> {{ $dados['instrutor'] }}<br>
<strong>Número de participantes:</strong> {{ $dados['participantes'] }}<br>
<strong>Valor hora-aula:</strong> R$ {{ $dados['valor_hora'] }} -
<strong>Bônus de Deslocamento:</strong> R$ {{ $dados['bonus'] }}<br>
<strong>Valor total da Nota Fiscal:</strong> R$ {{ $dados['total'] }} ({{ $dados['total_extenso'] }})</p>

<h2 style="font-size: 17px;">ORIENTAÇÕES:</h2>
<p>Acesso à plataforma Zoom para o instrutor: <a href="https://zoom.us/">https://zoom.us/</a><br>
    E-mail: eventos@redemetrologica.com.br<br>
    Senha: Eventos2801
</p>
<p>Ingressar em uma reunião como participante –<br>
    <a href="{{ $dados['link'] }}">{{ $dados['link'] }}</a>
</p>
<p>Tornar-se o anfitrião usando a chave –</p>
<ol>
    <li>Clicar em Participantes na barra inferior do Zoom.</li>
    <li>Na lista de participantes, clicar em "Reivindicar função de anfitrião" (Claim Host),
        geralmente localizada no canto inferior direito da janela de participantes.
    </li>
    <li>Inserir a chave de anfitrião quando solicitado <br>
        <mark><strong>CHAVE ANFITRIÃO: 891175</strong></mark>
    </li>
    <li>Clicar em "Tornar-se anfitrião" (Claim Host).</li>
</ol>
<p>
    <mark><strong>POR FAVOR GRAVAR O TREINAMENTO<br>
    POR FAVOR MARCAR A PRESENÇA DOS PARTICIPANTES NA LISTA</strong></mark>
</p>

<p><strong>Contato Rede Metrológica RS:</strong> Raquel Bresolin <br>
<strong>Fone/ Fax:</strong> 51 2200 3988
<br><strong>E-mail de contato:</strong> eventos@redemetrologica.com.br</p>
@endsection
