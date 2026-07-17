# Reenviar Senhas Interlab

Este comando enfileira o envio dos e-mails de carta senha para os inscritos de uma agenda interlab.

## Uso Padrão

```bash
vendor/bin/sail artisan app:reenviar-senhas-interlab {agenda_interlab_id}
```

No uso padrão, o comando mantém o comportamento existente: gera `tag_senha` quando necessário e enfileira os envios com delay incremental.

## Reenvio De E-mails

```bash
vendor/bin/sail artisan app:reenviar-senhas-interlab {agenda_interlab_id} --resend-email
```

Use `--resend-email` para reenfileirar e-mails usando apenas as `tag_senha` já existentes.

Neste modo:

- O comando não recria nem altera `tag_senha`.
- Inscritos ou analistas sem `tag_senha` são ignorados.
- Os envios continuam enfileirados com delay incremental entre cada e-mail.
