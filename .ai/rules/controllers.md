---
paths:
  - app/Http/Controllers/PessoaController.php
---

# Controllers

## Criar usuário a partir de pessoa exige e-mail
Antes de CreateUserForPessoaAction em associaUsuario, recusar se blank($pessoa->email). Redirecionar back()->with('error', ...) para o toast do painel; não deixar o User::create falhar por e-mail nulo.
