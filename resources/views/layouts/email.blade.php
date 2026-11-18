<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rede Metrológica RS</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 10px; border: 1px solid #e0e0e0; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
        pre, code { white-space: pre-wrap; overflow-wrap: anywhere; }
    </style>
</head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="margin-top: 1.8rem; margin-bottom: 1.8rem; text-align: center;">
            <img src="{{ $message->embed(public_path('build/images/site/LOGO_REDE_COLOR_150x376.png')) }}" alt="Rede Metrológica RS" width="150" style="max-width: 150px; height: auto;">
        </div>

        <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            @yield('content')
        </div>

        <div style="text-align: center;">
            <span style="font-size: 12px;">© {{ date('Y') }} Sistema Rede Metrológica RS.</span>
        </div>
    </div>
</body>
</html>
