---
name: project-controllers-patterns
description: Padrões para Controllers HTTP, FormRequests e Actions (Pattern de Serviço) no projeto sistema-rede.
---

# Controllers & Actions

## 1. Controllers HTTP

Controllers são responsáveis por receber a requisição, validar, orquestrar a lógica via Actions e retornar a resposta. **Sem lógica de negócio complexa inline.**

### Nomenclatura CRUD (Padrão do Projeto)

O projeto usa uma nomenclatura de métodos própria em vez do REST padrão:

| Método HTTP | Método do Controller | Finalidade                   |
| :---------- | :------------------- | :--------------------------- |
| `GET`       | `index()`            | Tela de listagem             |
| `GET`       | `insert(Model $m)`   | Tela de detalhe/edição       |
| `POST`      | `create(Request $r)` | Salvar novo registro         |
| `PUT/POST`  | `update(Request, M)` | Atualizar registro existente |
| `DELETE`    | `delete(Model $m)`   | Remover registro             |

### Regras de Implementação

- Tipar retornos: `: View`, `: RedirectResponse`, `: BinaryFileResponse`.
- Usar **Route Model Binding** sempre que possível.
- **Validação:**
  - Até 4 regras → `$request->validate([...])` inline.
  - 5 ou mais regras → Criar `FormRequest` em `App\Http\Requests\`.
- **Flash messages:** usar apenas as chaves `success`, `error` e `warning`.
- **Queries:** usar `->when()` para filtros dinâmicos no `index()`.
- **Transações:** operações multi-tabelas devem usar `DB::transaction()`.

## 2. FormRequest (`App\Http\Requests`)

Obrigatório para validações com 5+ regras.

- Estender `Illuminate\Foundation\Http\FormRequest`.
- Implementar `failedValidation()` para logar erros no canal `validation`.
- Usar `prepareForValidation()` para formatar valores (moeda, CNPJ) antes da validação.

```php
public function failedValidation(Validator $validator)
{
    Log::channel('validation')->info('Erro de validação', [
        'user' => auth()->user()->id ?? null,
        'errors' => $validator->errors(),
        'request' => $this->all()
    ]);

    return back()->withErrors($validator)->withInput()->with('error', 'Revise os dados informados.');
}
```

## 3. Actions (`App\Actions`)

O projeto utiliza Actions para encapsular lógica de negócio reutilizável e complexa.

### Tipos de Assinatura

1.  **`static handle(...)` (Ações Utilitárias/Sem Estado):**
    - Ex: `FileUploadAction::handle()`, `CreateUserForPessoaAction::handle()`.
    - Chamadas diretas: `XxxAction::handle($params)`.

2.  **`execute(...)` via Instância (Ações de Negócio com Dependências):**
    - Ex: `EnviarCertificadoAction`, `InscricaoInterlabAction`.
    - Chamadas via container: `app(XxxAction::class)->execute($params)`.

### Regras de Actions

- Uma responsabilidade por Action.
- PHPDoc `@param` e `@return` obrigatórios.
- Se a Action disparar Jobs, usar `::dispatch()->delay()`.
- Se envolver persistência múltipla, usar `DB::transaction()` dentro da Action.

## 4. Anti-Patterns

| ❌ Anti-pattern                      | ✅ Correto                               |
| :----------------------------------- | :--------------------------------------- |
| Mais de 4 regras de validação inline | Criar `FormRequest`                      |
| `FormRequest` sem log de falha       | Implementar `failedValidation()` com Log |
| Lógica de negócio no Controller      | Delegar a `Actions/`                     |
| E-mails síncronos no fluxo principal | Usar `Job::dispatch()->delay()`          |

## 5. Checklist

- [ ] Métodos seguem `index`, `insert`, `create`, `update`, `delete`
- [ ] Retorno de tipo declarado (`: View`, etc)
- [ ] Validação 5+ regras movida para `FormRequest`
- [ ] Erros de validação logados no canal `validation`
- [ ] Flash messages usam `success`, `error` ou `warning`
- [ ] Lógica complexa movida para `Action::execute()` ou `Action::handle()`
- [ ] Operações multi-tabela em `DB::transaction()`
