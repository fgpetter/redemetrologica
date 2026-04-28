<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
</head>

    <body
        style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="margin-top: 1.8rem; margin-bottom: 1.8rem;">
                <figure style="text-align: center;">
                    <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" width="140px"
                        style="max-width: 50%">
                </figure>
            </div>

            <div
                style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">

                <h3>{{ $rodada->agendainterlab->interlab->nome }}</h3>
                <p>Prezado participante,</p>
                <p>
                    Lembramos que o prazo para envio dos resultados do “Programa de Ensaio de Proficiência
                    {{ $rodada->agendainterlab->interlab->nome }} - {{ $rodada->agendainterlab->ano_referencia }}” está se aproximando,
                    conforme estabelecido no protocolo do programa.
                </p>
                <p>
                    Solicitamos, por gentileza, que verifiquem suas pendências e realizem o reporte dentro do prazo
                    estipulado. Ressaltamos que não serão aceitos dados enviados fora do prazo definido em
                    protocolo.
                </p>

                @if ($rodada->descricao_arquivo_limite_envio_resultados)
                    <div style="background-color: #FFF3CD; padding: 12px; border-radius: 3px; margin-bottom: 10px; color: #856404;">
                        <p><strong>Informações adicionais:</strong> <br>
                            {!! nl2br(e($rodada->descricao_arquivo_limite_envio_resultados)) !!}
                        </p>
                    </div>
                @endif

                @if ($rodada->arquivo_limite_envio_resultados)
                    <p>
                        <a href="{{ url('interlab-material/' . $rodada->arquivo_limite_envio_resultados) }}" target="_blank"
                            style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 3px;">
                            Clique aqui para fazer o download do documento
                        </a>
                    </p>
                @endif

                <p style="font-size: 13px; color: #6c757d;">
                    Este é um e-mail automático. Em caso de dúvidas, entre em contato com a coordenação do
                    programa.
                </p>

                <p>
                    Atenciosamente,<br>
                    Equipe Rede Metrológica RS
                </p>
            </div>
            <div style="text-align: center;">
                <span style="font-size: 12px;">© {{ date('Y') }} Sistema Rede MetrológicaRS.</span>
            </div>
        </div>
    </body>

</html>