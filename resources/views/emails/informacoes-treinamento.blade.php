<!DOCTYPE html>
<html lang="pt-br"><head><meta charset="UTF-8"></head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
  <figure style="text-align: center;"><img src="{{ $message->embed(public_path('build/images/site/LOGO_REDE_COLOR_150x376.png')) }}" alt="Rede Metrológica RS" width="150"></figure>
  <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
    <p>Prezado {{ $inscrito->nome }},</p>
    <p>Segue material e informações de acesso ao treinamento.</p>
    <p><strong>Data:</strong> {{ $agenda->data_inicio->format('d/m/Y') }}<br>
    <strong>Hora:</strong> {{ $agenda->horario }}<br>
    <strong>Link de acesso:</strong> <a href="{{ $agenda->link_aula }}">{{ $agenda->link_aula }}</a></p>
    <p><strong>Materiais:</strong></p>
    <ul>@foreach($agenda->materiaisTreinamento as $material)<li><a href="{{ url(\Illuminate\Support\Facades\Storage::disk('public')->url($material->arquivo)) }}">{{ $material->descricao ?: basename($material->arquivo) }}</a></li>@endforeach</ul>
    <p>Solicitamos sua autorização para gravação do treinamento. A gravação tem o intuito de não prejudicar o participante em uma ausência pontual durante o treinamento e poderá ser disponibilizada apenas nas 24h seguidas ao término, mediante solicitação, sendo descartada passado este prazo.</p>
    <p>Para emissão do certificado de participação, solicitamos que os participantes mantenham a câmera ligada durante todo o curso, permitindo a confirmação de presença e promovendo a interação entre o grupo.</p>
    <p>Favor confirmar recebimento.</p><p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
  </div>
  <div style="text-align: center;"><span style="font-size: 12px;">© {{ date('Y') }} Sistema Rede Metrológica RS.</span></div>
</div></body></html>
