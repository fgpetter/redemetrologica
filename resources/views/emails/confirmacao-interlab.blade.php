@extends('layouts.email')

@section('content')
<h3>Confirmação de Realização - {{ $participante->agendaInterlab->interlab->nome }}</h3>
<p>Prezado laboratório,</p>
<p>Informamos que o Programa de Ensaio de Proficiência (PEP) -
    <strong>{{ $participante->agendaInterlab->interlab->nome }}</strong> de
    <strong>{{ $participante->agendaInterlab->interlab->ano_referencia }}</strong> está confirmado.</p>
<p>EA partir de agora, qualquer alteração no planejamento será comunicada a vocês.</p>
<p>Agradecemos sua participação e estamos à disposição para quaisquer dúvidas.</p>

<br>
<p>
    Atenciosamente,<br>
    Equipe Rede Metrológica RS
</p>
@endsection
