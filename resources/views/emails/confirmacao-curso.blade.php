@extends('layouts.email')

@section('content')
<p>Informamos que o Curso {{ $curso }} em {{ $dataInicio }} está confirmado. O acesso será encaminhado por e-mail até dois dias antes do treinamento.</p>
<p>Procederemos com o envio da cobrança relativa à sua inscrição. Em caso desistência solicitamos a gentileza de formalizar o pedido de cancelamento pois pode inviabilizar a realização do curso.</p>
<p>Estou à disposição para qualquer esclarecimento.</p>
@endsection
