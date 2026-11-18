@extends('layouts.email')

@section('content')
<p>Prezado {{ $dados['nome'] }},</p>
<p>Segue material e informações de acesso ao treinamento.</p>
<p><strong>Data:</strong> {{ \Carbon\CarbonImmutable::parse($dados['data_curso'], 'America/Sao_Paulo')->format('d/m/Y') }}<br>
<strong>Hora:</strong> {{ $dados['horario'] }}<br>
<strong>Link de acesso:</strong> <a href="{{ $dados['link'] }}">{{ $dados['link'] }}</a></p>
<p><strong>Materiais:</strong></p>
<ul>@foreach($dados['materiais'] as $material)<li><a href="{{ $material['url'] }}">{{ $material['descricao'] }}</a></li>@endforeach</ul>
<p>Solicitamos sua autorização para gravação do treinamento. A gravação tem o intuito de não prejudicar o participante em uma ausência pontual durante o treinamento e poderá ser disponibilizada apenas nas 24h seguidas ao término, mediante solicitação, sendo descartada passado este prazo.</p>
<p>Para emissão do certificado de participação, solicitamos que os participantes mantenham a câmera ligada durante todo o curso, permitindo a confirmação de presença e promovendo a interação entre o grupo.</p>
<p>Favor confirmar recebimento.</p><p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
@endsection
