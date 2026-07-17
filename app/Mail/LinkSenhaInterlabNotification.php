<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LinkSenhaInterlabNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public $dadosDoc;

    public function __construct($dadosDoc)
    {
        $this->dadosDoc = $dadosDoc;
    }

    public function build()
    {
        // #region agent log
        $envAppUrl = env('APP_URL');
        $configAppUrl = config('app.url');
        $link = $this->dadosDoc->link ?? null;
        $urlFromEnv = ($envAppUrl ?? '').'/dados-doc/'.$link;
        $urlFromConfig = rtrim((string) $configAppUrl, '/').'/dados-doc/'.$link;
        $urlFromRoute = null;
        try {
            $urlFromRoute = route('dados-doc.download', $link);
        } catch (\Throwable $e) {
            $urlFromRoute = 'ROUTE_ERROR: '.$e->getMessage();
        }
        $payload = json_encode([
            'sessionId' => 'b824d6',
            'runId' => 'post-fix',
            'hypothesisId' => 'H-A',
            'location' => 'LinkSenhaInterlabNotification::build',
            'message' => 'URL values at mailable build after fix',
            'data' => [
                'envAppUrlIsNull' => $envAppUrl === null,
                'envAppUrlEmpty' => $envAppUrl === null || $envAppUrl === '',
                'envAppUrlLength' => is_string($envAppUrl) ? strlen($envAppUrl) : 0,
                'configAppUrlEmpty' => empty($configAppUrl),
                'configAppUrlLength' => is_string($configAppUrl) ? strlen($configAppUrl) : 0,
                'configIsCached' => app()->configurationIsCached(),
                'urlFromEnvLooksRelative' => str_starts_with($urlFromEnv, '/dados-doc/'),
                'urlFromEnvPreview' => preg_replace('#https?://[^/]+#', '[HOST]', $urlFromEnv),
                'urlFromConfigPreview' => preg_replace('#https?://[^/]+#', '[HOST]', $urlFromConfig),
                'urlFromRoutePreview' => is_string($urlFromRoute) ? preg_replace('#https?://[^/]+#', '[HOST]', $urlFromRoute) : null,
                'hasLink' => filled($link),
            ],
            'timestamp' => (int) (microtime(true) * 1000),
        ], JSON_UNESCAPED_UNICODE);
        if ($payload !== false) {
            $logPath = base_path('storage/logs/debug-b824d6.log');
            $dir = dirname($logPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($logPath, $payload."\n", FILE_APPEND | LOCK_EX);
            @file_put_contents(base_path('.cursor/debug-b824d6.log'), $payload."\n", FILE_APPEND | LOCK_EX);
        }
        // #endregion

        return $this->subject('Código de Identificação - ' . $this->dadosDoc->content['interlab_nome'])
            ->replyTo('interlab@redemetrologica.com.br')
            ->view('emails.link-senha-interlab');
    }
}
