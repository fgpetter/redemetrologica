<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
</head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="margin-top: 1.8rem; margin-bottom: 1.8rem;">
      <figure style="text-align: center;">
        <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" width="140px" style="max-width: 50%">
      </figure>
    </div>

    <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
      <h3>Código de identificação - {{ $dadosDoc->content['interlab_nome'] }}</h3>
      
      @if (isset($dadosDoc->content['analista_nome']))
        Olá {{ $dadosDoc->content['analista_nome'] }},
        <p>Segue seu código de identificação (senha) para o interlaboratorial - Laboratório: {{ $dadosDoc->content['laboratorio_nome'] }}.</p>
      @else
        <p>Segue código de identificação (senha) do laboratório - {{ $dadosDoc->content['laboratorio_nome'] }}.</p>
      @endif
      
      {{-- #region agent log --}}
      @php
        $__dbgEnv = env('APP_URL');
        $__dbgConfig = config('app.url');
        $__dbgRoute = route('dados-doc.download', $dadosDoc->link);
        $__dbgPayload = json_encode([
          'sessionId' => 'b824d6',
          'runId' => 'post-fix',
          'hypothesisId' => 'H-B',
          'location' => 'emails.link-senha-interlab',
          'message' => 'Blade href after route() fix',
          'data' => [
            'envNull' => $__dbgEnv === null,
            'configCached' => app()->configurationIsCached(),
            'routeIsAbsolute' => str_starts_with($__dbgRoute, 'http'),
            'routePreview' => preg_replace('#https?://[^/]+#', '[HOST]', $__dbgRoute),
            'configPresent' => filled($__dbgConfig),
          ],
          'timestamp' => (int) (microtime(true) * 1000),
        ], JSON_UNESCAPED_UNICODE);
        if ($__dbgPayload !== false) {
          @file_put_contents(base_path('storage/logs/debug-b824d6.log'), $__dbgPayload."\n", FILE_APPEND | LOCK_EX);
          @file_put_contents(base_path('.cursor/debug-b824d6.log'), $__dbgPayload."\n", FILE_APPEND | LOCK_EX);
        }
      @endphp
      {{-- #endregion --}}
      <p style="margin: 30px 0;">
        <a href="{{ route('dados-doc.download', $dadosDoc->link) }}" 
           target="_blank"
           style="display: inline-block; padding: 12px 30px; background-color: #0056b3; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
          Baixar Carta Senha
        </a>
      </p>

      <p style="font-size: 12px; color: #888;">
        Ou copie e cole o link abaixo no seu navegador:<br>
        <a href="{{ route('dados-doc.download', $dadosDoc->link) }}" style="color: #0056b3; word-break: break-all;">{{ route('dados-doc.download', $dadosDoc->link) }}</a>
      </p>
      
      <br>
      <p>
        Atenciosamente,<br>
        Equipe Rede Metrológica RS
      </p>
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© 2025 Sistema Rede Metrológica RS.</span></div>
  </div>
</body>
</html>
