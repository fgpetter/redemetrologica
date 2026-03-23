---
name: project-livewire-patterns
description: Padrões para arquitetura Livewire v3, Form Objects e Comunicação no projeto sistema-rede.
---

# Livewire — Padrões Arquiteturais v3

## 1. Estrutura Canônica de Módulos

Cada módulo novo deve seguir esta organização de pastas:

```
app/Livewire/
└── NomeModulo/
    ├── Listview.php      ← Lista contextual (dentro de outro card)
    ├── Modalform.php     ← Formulário em modal
    ├── NomeModuloTable.php   ← Tabela principal (filtros + paginação)
    └── NomeModuloForm.php    ← Form Object obrigatório
```

## 2. Componente `Table` (Listagem Principal)

- Usar `WithPagination` + `#[Url(as: 'p', history: false)] public $perPage = 15`.
- Filtros como propriedades públicas com `#[Url]`.
- Resetar paginação no método central `updated($propertyName)`.
- Extrair query para `protected function getQuery()`.

## 3. Componente `Listview` (Sub-módulo)

- Receber Model pai via Model Binding.
- Propriedade `public ?string $itemAtivo = null` para controle do modal.
- `#[On('refresh-xxx-list')]` no `render()` para reatividade.
- Exclusão via `deletar(string $uid)`.

## 4. Form Object (`App\Livewire\Forms`) — **OBRIGATÓRIO**

Nunca usar `$rules` ou `$messages` no componente. Usar Form Objects para encapsular estado e validação do formulário.

- Propriedades com `#[Validate]`.
- Métodos `setXxx(Model $model)` e `setNew()`.
- Método `toArray()` para persistência rápida.

## 5. Componente `Modalform`

- Receber Model pai e/ou `itemUid`.
- `mount()` inicializa o Form Object (`setXxx` ou `setNew`).
- **Arquivos:** uploads ficam como propriedades no componente (com `WithFileUploads`), não no Form Object.
- Salvar com `Model::updateOrCreate(['uid' => $this->form->uid], $this->form->toArray())`.
- Disparar `refresh-xxx-list` após sucesso.

## 6. Comunicação e Eventos

| Situação            | Mecanismo                                               |
| :------------------ | :------------------------------------------------------ |
| Filho notifica pai  | `$this->dispatch('nome-evento', key: $value)`           |
| Escutar evento      | `#[On('nome-evento')]`                                  |
| Alerta Sucesso/Erro | `$this->dispatch('show-success-alert', message: '...')` |

## 7. Anti-Patterns

| ❌ Anti-pattern                     | ✅ Correto                                       |
| :---------------------------------- | :----------------------------------------------- |
| `$rules` no componente              | Usar Form Object com `#[Validate]`               |
| Múltiplos `updatedXxx()`            | `updated($propertyName)` centralizado            |
| Propriedades em `PascalCase`        | `camelCase` obrigatório (PSR-12)                 |
| Lógica complexa no `render()`       | Extrair para Computed Properties ou `getQuery()` |
| Omitir `wire:ignore.self` em modais | Obrigatório para evitar fechamento inesperado    |

## 8. Checklist

- [ ] Form Object criado em `App\Livewire\Forms`
- [ ] `Modalform` usa Form Object (sem `$rules`)
- [ ] `Listview` escuta `refresh-xxx-list`
- [ ] `Table` usa `#[Url]` para filtros e alias curto
- [ ] Eventos em `kebab-case`
- [ ] Propriedades em `camelCase`
