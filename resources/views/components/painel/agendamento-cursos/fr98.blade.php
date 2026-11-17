@props(['agendacurso'])

@if ($agendacurso->exists)
    <div class="col-12 col-sm-auto">
        <div class="form-label mb-0">
            @if ($agendacurso->fr98_enviado_em)
                Enviado em {{ $agendacurso->fr98_enviado_em->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}
            @else
                <span aria-hidden="true">&nbsp;</span>
            @endif
        </div>
        <button type="submit" name="enviar_fr98" value="1" class="btn btn-primary" @disabled($agendacurso->fr98_envio_id)>
            {{ $agendacurso->fr98_enviado_em ? 'Reenviar FR98' : 'Enviar FR98' }}
        </button>
    </div>
@endif
