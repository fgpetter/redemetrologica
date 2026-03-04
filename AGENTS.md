# Sistema Rede – Instruções para Agentes

## Base

- Seguir diretrizes do **Laravel Boost** (`.cursor/rules/laravel-boost.mdc`).
- Deltas do projeto em `.cursor/rules/php-laravel-sistema-rede.mdc`.

## Comportamento

- **Idioma:** Sempre responder em português.
- **Comandos:** Usar `vendor/bin/sail` para Artisan, Composer e npm.
- **Pint:** Não rodar Laravel Pint após alterar PHP ou Blade.
- **Debug:** Usar `Log::` do Laravel, nunca `file_put_contents` em `.cursor/debug.log`.
- **Docs:** Usar `search-docs` do Boost; se indisponível, Context7 MCP.
- **Frontend:** Checar erros com Chrome DevTools MCP após mudanças.

## Prioridades

- Reutilizar componentes em `/resources/views/components` antes de criar novos.
- Preferir Actions sobre Services; Services só quando a lógica exige vários métodos relacionados.
- Ativar skill `livewire-development` ao trabalhar com Livewire.
