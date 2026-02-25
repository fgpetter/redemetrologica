# Plano de Reorganização de `resources/views`

## Objetivo
Padronizar e organizar a estrutura de views, reduzindo inconsistências de nomenclatura, removendo referências quebradas e separando arquivos ativos de legados, sem quebrar rotas/fluxos existentes.

## Escopo
- Pasta principal: `resources/views`
- Arquivos relacionados para ajuste de referência:
  - `routes/`
  - `app/Http/Controllers/`
  - `app/Livewire/`
  - classes de e-mail (`app/Mail` ou similares)

## Regras de execução
- Executar mudanças em fases pequenas (commits/PRs pequenos).
- Não mover/remover arquivos legados sem prova de não uso.
- Após cada fase, rodar validação mínima direcionada.
- Em caso de dúvida de nomenclatura, seguir padrão já dominante no projeto e registrar decisão.

## Fase 0 - Preparação e baseline
1. Garantir ambiente ativo.
2. Criar branch de trabalho dedicada.
3. Capturar baseline:
   - árvore de `resources/views`
   - views referenciadas por `view()`, `markdown()`, `@include`, componentes `<x-*>`, Livewire `return view(...)`
4. Gerar inventário em três grupos:
   - `ATIVO` (referenciado no código)
   - `SUSPEITO_LEGADO` (sem referência direta)
   - `ORFAO` (referência para arquivo inexistente)

Critério de aceite:
- Inventário salvo e revisável antes de qualquer rename/move.

## Fase 1 - Correções críticas (integridade)
Objetivo: zerar erros óbvios de `view not found`.

Tarefas:
1. Corrigir includes inexistentes:
   - `site.layouts.site-navbar`
   - `site.layouts.site-footer`
2. Corrigir referência de e-mail inexistente:
   - `emails.certificados-deleted` (criar view faltante ou ajustar referência para a view correta)
3. Validar rapidamente os fluxos atingidos.

Critério de aceite:
- Sem referências para views inexistentes nos alvos conhecidos (`view()`, `markdown()`, `@include`).

## Fase 2 - Padronização de nomes das páginas (`painel/*`)
Objetivo: adotar convenção única para telas de CRUD.

Padrão proposto:
- `index.blade.php`
- `create.blade.php`
- `edit.blade.php`
- `show.blade.php`

Mapeamento inicial sugerido (validar com owner antes):
- `painel/*/insert.blade.php` -> `painel/*/create.blade.php` (ou `edit` quando fizer edição)
- `painel/users/user-index.blade.php` -> `painel/users/index.blade.php`
- `painel/users/user-update.blade.php` -> `painel/users/edit.blade.php`
- `painel/post/noticia-index.blade.php` -> `painel/post/index.blade.php`
- `painel/interlabs/labindex.blade.php` -> `painel/interlabs/index.blade.php` (se for a tela principal)

Tarefas:
1. Executar renames graduais por domínio.
2. Atualizar todas as chamadas `view('...')` relacionadas.
3. Validar rota por rota do domínio alterado.

Critério de aceite:
- Nenhuma tela do `painel` depende de nomes especiais não padronizados.

## Fase 3 - Padronização de componentes e Livewire
Objetivo: reduzir variação de nomenclatura e typos.

Padrão proposto:
- Arquivos em `kebab-case`
- Sem variação tipo `listview/modalform` quando houver equivalente claro (`list-view`, `modal-form`)

Tarefas:
1. Corrigir typo:
   - `components/painel/avaliadores/areas-atucao.blade.php` -> nome correto (`areas-atuacao` ou outro acordado)
2. Revisar e renomear arquivos Livewire inconsistentes:
   - `livewire/*/listview.blade.php`
   - `livewire/*/modalform.blade.php`
3. Ajustar referências em:
   - classes Livewire (`return view('...')`)
   - inclusões Blade relacionadas

Critério de aceite:
- Nomenclatura consistente em `components/` e `livewire/`.

## Fase 4 - Consolidação estrutural
Objetivo: eliminar ambiguidade de domínio e separar melhor responsabilidades.

Tarefas:
1. Resolver singular/plural por domínio:
   - exemplo atual: `painel/area-atuacao` vs `components/painel/areas-atuacao`
2. Definir convenção para componentes por intenção:
   - `form-*`, `table-*`, `modal-*`, `section-*` (quando aplicável)
3. Revisar pasta `steex-pages`:
   - confirmar uso real
   - se legado, mover para `resources/views/_legacy/steex-pages` ou remover com aprovação

Critério de aceite:
- Árvore de pastas com convenções consistentes e documentadas.

## Fase 5 - Ativos não-Blade em `views`
Objetivo: manter `resources/views` apenas para templates.

Tarefas:
1. Identificar ativos binários em `resources/views` (ex.: `.jpg`, `.png`, `.html` puro).
2. Mover para local adequado (`public/` ou `resources/images/`, conforme uso).
3. Atualizar referências nos templates (PDFs/emails).

Critério de aceite:
- `resources/views` sem arquivos binários ou estáticos fora de contexto.

## Fase 6 - Validação final
Tarefas:
1. Rodar testes focados por módulos alterados.
2. Validação manual mínima:
   - autenticação
   - telas principais do painel
   - páginas do site
   - disparo de e-mails afetados
3. Executar formatação exigida pelo projeto para arquivos PHP alterados:
   - `vendor/bin/sail bin pint --dirty --format agent`

Critério de aceite:
- Sem regressão funcional conhecida.
- Sem erro de resolução de view.

## Estratégia de entrega (recomendada)
- PR 1: Fase 1 (correções críticas)
- PR 2: Fase 2 (`painel/*`)
- PR 3: Fase 3 (components + livewire)
- PR 4: Fase 4 e Fase 5 (estrutura e legado)
- PR 5: hardening/ajustes finais de validação

## Rollback e segurança
- Evitar grandes renames em lote sem checkpoints.
- Em cada PR:
  - manter lista explícita de arquivos renomeados
  - incluir mapa `antes -> depois`
  - confirmar atualização de todas as referências
- Se quebrar resolução de view:
  1. restaurar rename do domínio afetado
  2. reaplicar por subdomínio menor

## Checklist rápido por PR
- [ ] Arquivos renomeados/movidos mapeados
- [ ] Referências `view()/markdown()/@include` atualizadas
- [ ] Referências Livewire atualizadas
- [ ] Sem erro de `view not found`
- [ ] Testes do escopo executados
- [ ] (Se houve PHP alterado) Pint executado
