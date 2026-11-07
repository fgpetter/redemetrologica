# sistema-rede

- PHP 8.4. Confirme as versões em `composer.lock` ou com `composer show` ao depender de APIs. Preserve a estrutura existente da aplicação.
- Antes de editar, leia as regras aplicáveis em `.ai/rules/` (use o índice se existir) e ative as skills de domínio relevantes. Registre decisões duráveis com `record-rule`.
- Alterações de dependências e novos diretórios de primeiro nível exigem aprovação. Crie documentação somente quando solicitada.
- Use `rtk` nos comandos que ele suporta e execute os demais diretamente.
- Use os geradores do Artisan quando disponíveis, com `--no-interaction`.
- Para mudanças de comportamento, leia a skill `testing-best-practices`, adicione ou ajuste testes Pest e execute o recorte afetado com `php artisan test --compact`. Remover testes exige aprovação.
- Após editar PHP, execute `vendor/bin/pint --dirty --format agent`, usando o equivalente RTK quando disponível.
