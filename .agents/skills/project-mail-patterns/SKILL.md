---
name: project-mail-patterns
description: Padrões para Mailables (Emails) e Notificações no projeto sistema-rede.
---

# Mail Patterns

O projeto utiliza Mailables do Laravel com suporte a filas (`ShouldQueue`).

## 1. Regras de Implementação

- **Estilo Moderno (Obrigatório):** Implementar `envelope()`, `content()` e `attachments()`. Evitar o método `build()` legado.
- **Fila:** Sempre implementar `implements ShouldQueue` e usar os traits `Queueable, SerializesModels`.
- **Resiliência:** Definir `$tries = 3` e `$timeout = 120`.
- **ReplyTo:** Configurar sempre no `envelope()` de acordo com o departamento (`interlab@...`, `cursos@...`).
- **Dados:** Passar via construtor público (`public $dados`) para acesso automático na view.

## 2. Exemplo de Referência (Moderno)

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExemploMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(public array $dados) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address('departamento@redemetrologica.com.br')],
            subject: 'Assunto do Email - ' . $this->dados['info'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exemplo-view',
            with: ['dados' => $this->dados]
        );
    }
}
```

## 3. Anti-Patterns

| ❌ Anti-pattern                   | ✅ Correto                                        |
| :-------------------------------- | :------------------------------------------------ |
| Usar `build()` em novos Mailables | Usar `envelope()` e `content()`                   |
| Enviar email de forma síncrona    | Usar `implements ShouldQueue`                     |
| `from` fixo no código             | Usar o padrão global ou `Address` no `envelope()` |
| Omitir `$tries` / `$timeout`      | Definir explicitamente para segurança da fila     |

## 4. Checklist

- [ ] Implementa `ShouldQueue`
- [ ] Usa `envelope()` e `content()`
- [ ] Possui `$tries = 3` e `$timeout = 120`
- [ ] `replyTo` configurado corretamente
- [ ] Propriedades públicas usadas para dados na view
