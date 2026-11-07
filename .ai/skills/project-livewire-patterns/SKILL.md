---
name: project-livewire-patterns
description: Desenvolver, depurar e testar componentes Livewire 4 no sistema-rede, incluindo reatividade, formulários, eventos, desempenho e migração da versão 3.
---

# Livewire 4 — Padrões do Sistema Rede

Use `search-docs` para consultar as APIs do Livewire 4 e o [guia de upgrade](https://livewire.laravel.com/docs/4.x/upgrading) ao adaptar componentes da versão 3.

## Estrutura dos módulos

O projeto usa componentes baseados em classes. Em `config/livewire.php`, `make_command.type` é `class`. Use `php artisan make:livewire NomeModulo/NomeComponente --class` para gerar um componente desse formato.

```text
app/Livewire/
├── NomeModulo/
│   ├── NomeModuloTable.php
│   ├── Listview.php
│   └── Modalform.php
└── Forms/
    └── NomeModuloForm.php

resources/views/livewire/nome-modulo/
```

Use `Route::livewire()` ao registrar uma página renderizada diretamente por um componente Livewire. Feche as tags de componentes, como `<livewire:nome-modulo.listview />`.

- O Livewire também aceita componentes em arquivo único ou múltiplos arquivos. O padrão deste projeto é baseado em classes e usa `component_layout` definido como `layouts::app`.
- Use um elemento raiz no componente e uma `wire:key` estável em cada item de uma lista.
- Mantenha o estado no servidor. Inicialize-o em `mount()` e use hooks como `updatedSearch()` para efeitos de alterações específicas.

## Listagens

- Use `WithPagination` na tabela principal, extraia a consulta para `getQuery()` e use `#[Url]` nos filtros que precisam aparecer na URL. Para o tamanho da página, siga o padrão local `#[Url(as: 'p', history: false)] public int $perPage = 15`.
- Resete a página quando um filtro, a ordenação ou o tamanho da página mudar. Use `updated($propertyName)` com os campos relevantes quando vários campos compartilharem esse comportamento. Para paginadores nomeados, passe a `resetPage(pageName: 'nome-da-pagina')` o mesmo nome usado em `paginate()`.
- Em listagens contextuais, receba o model pai tipado e use `public ?string $itemAtivo = null` para controlar qual item abre no modal. Na exclusão via `deletar(string $uid)`, localize o item na relação do pai e autorize a ação.

## Form Objects e modais

- Encapsule o estado, as regras e as mensagens do formulário em um Form Object de `App\Livewire\Forms`. Use `#[Validate]` para regras estáticas e `rules()` para regras dinâmicas.
- Inicialize o Form Object em `mount()`. Use métodos como `setXxx(Model $model)` e `setNew()` quando precisar carregar um registro ou preparar os valores de criação.
- Mantenha os uploads em propriedades do componente com `WithFileUploads`, conforme a convenção local.
- Antes de salvar, o componente ou a Action deve executar a validação definida no Form Object, autorizar a operação e selecionar explicitamente os campos a persistir. Na edição, localize o registro no escopo do pai quando houver essa relação. Os models usam `$guarded = []` e não selecionam nem validam os dados de entrada. A implementação padrão de `toArray()` inclui todas as propriedades públicas do Form Object.
- Aplique `#[Locked]` aos UIDs públicos que o navegador deve apenas ler. Autorize também as ações que recebem UIDs como argumentos.
- Aplique `wire:ignore.self` ao elemento externo de modais e offcanvas cujos atributos são gerenciados pelo Bootstrap. O conteúdo interno continua recebendo atualizações do Livewire.

## Eventos

- Após salvar, dispare um evento em `kebab-case` com `$this->dispatch()`. Use parâmetros nomeados quando houver dados no evento.
- Para atualizar só o pai, escute o evento na tag do filho, como `<livewire:nome-modulo.modalform @saved="$refresh" />` com `$this->dispatch('saved')`. Use `#[On('refresh-xxx-list')]` em um método listener para atualizar a lista por um evento compartilhado. Se houver várias instâncias na página, inclua o UID do pai no nome desse evento.
- Para alertas de sucesso, preserve o evento da interface `$this->dispatch('show-success-alert', message: '...')`.

## Bindings de formulário

- Use `wire:model` quando o servidor puder receber o valor na próxima ação, como no envio do formulário.
- Use `wire:model.live.debounce.300ms` quando a busca precisar consultar o servidor durante a digitação.
- Use `wire:model.live.blur` ou `wire:model.live.change` para enviar uma requisição no evento correspondente. Sem `.live`, `.blur` e `.change` controlam quando o estado local é atualizado.
- Aplique o binding ao campo que emite o evento. Use `.deep` quando um wrapper precisar captar intencionalmente eventos dos filhos.
- Use `wire:loading` com `wire:target` para indicar uma ação em andamento e `wire:dirty` para indicar alterações ainda não sincronizadas.

## JavaScript e testes

- O Livewire já inclui Alpine.js. Use a instância fornecida e registre interceptores globais em `livewire:init`. Use `Livewire.interceptRequest()` para requisições HTTP e `Livewire.interceptMessage()` para atualizações de componentes. `onError` trata erros HTTP e `onFailure`, falhas de rede. Consulte a [API de interceptores](https://livewire.laravel.com/docs/4.x/javascript#interceptors).
- Use `Livewire::test()` para verificar ações, estado e HTML renderizado. Para uma rota Laravel que incorpora o componente, use `assertSeeLivewire()` na resposta.

## Checklist

- [ ] Form Object em `App\Livewire\Forms` com validação antes de persistir
- [ ] Criação, edição e exclusão autorizadas no escopo correto
- [ ] Listagem atualizada após salvar e página redefinida ao mudar filtros
- [ ] Eventos em `kebab-case` e propriedades em `camelCase`
- [ ] Tags Livewire fechadas e bindings sincronizados no momento esperado

Consulte o [guia de upgrade do Livewire 4](https://livewire.laravel.com/docs/4.x/upgrading) ao adaptar componentes existentes.
