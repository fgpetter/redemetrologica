---
name: project-views-patterns
description: Padrões para views Blade, componentes UI (Bootstrap 5), JS (@script) e ícones no projeto sistema-rede.
---

# Views & Frontend Patterns

## 1. Componentes de Formulário (`x-forms.*`)

Nunca criar `<input>` avulso. Usar os componentes em `resources/views/components/forms/`.

| Componente                  | Uso              | Props Principais                     |
| :-------------------------- | :--------------- | :----------------------------------- |
| `x-forms.input-field`       | Texto, Núm, Data | `label`, `name`, `mask`, `required`  |
| `x-forms.input-select`      | Dropdowns        | `label`, `name`, `required`          |
| `x-forms.input-textarea`    | Texto longo      | `label`, `name`                      |
| `x-forms.input-file-upload` | Upload           | `label`, `wireModel`, `arquivoSalvo` |

### Máscaras (`mask` prop)

`cpf`, `cnpj`, `cpf_cnpj`, `cep`, `telefone`, `money` usar AlpineJs Mask.

## 2. Estrutura Livewire (Blade)

- **Cards:** Estrutura `card` > `card-header` (título + botão) > `card-body`.
- **Modais:** No mesmo arquivo da listagem, com `wire:ignore.self` e `key` dinâmico.
- **Tabelas:** `table-striped`, `align-middle`, `table-nowrap` no `Listview`.

## 3. JavaScript (`@script`)

Todo JS escopado ao componente deve estar em `@script` / `@endscript`.

- Fechar modal: `$wire.on('refresh-xxx-list', ...)` disparando clique no `.btn-close`.
- Toasts: Escutar eventos de sucesso e disparar `Swal.fire`.
- Choices.js: Wrapper com `wire:ignore` e binding manual via `@this.set()`.

## 4. Ícones e Layout

- **Remix Icons (`ri-`)**: Botões, filtros, ordenação.
- **Phosphor Icons (`ph-`)**: Menus de contexto (dots), avatares, docs.
- **Layout:** Todas estendem `@extends('layouts.master')`.
- **Alertas:** `<x-alerts.alert />` automático para flashes `success`/`error`/`warning`.

## 5. Anti-Patterns

| ❌ Anti-pattern                           | ✅ Correto                                  |
| :---------------------------------------- | :------------------------------------------ |
| `<input>` HTML puro                       | usar `<x-forms.*>`                          |
| `<script>` global em Livewire             | usar `@script` / `@endscript`               |
| Alpine sem `wire:ignore` em libs externas | Envolver em `wire:ignore` (Choices.js, etc) |
| Ícones inconsistentes                     | `ri-` para ações, `ph-` para contexto       |

## 6. Checklist Frontend

- [ ] View usa estrutura `card` Bootstrap
- [ ] Modal tem `wire:ignore.self` e `key="{{ $uid ?? 'new' }}"`
- [ ] Campos usam componentes `<x-forms.*>`
- [ ] Erros de validação exibidos abaixo do campo
- [ ] JS de fechamento de modal em `@script`
- [ ] Confirmação de exclusão com `wire:confirm`
- [ ] Search com `debounce.300ms`
