---
name: project-patterns
description: Índice central dos padrões arquiteturais do projeto sistema-rede (Laravel + Livewire). Use as sub-skills para detalhes específicos.
---

# Padrões do Projeto — Sistema Rede Metrológica

Os padrões deste projeto foram modularizados para facilitar a consulta. Siga o link correspondente à camada que você está trabalhando:

## 📚 Índice de Padrões

1.  **[Livewire Patterns](../project-livewire-patterns/SKILL.md)** — Componentes Livewire 4 baseados em classes, listagens, modais, Form Objects e eventos.
2.  **[Models Patterns](../project-models-patterns/SKILL.md)**: Eloquent no Laravel 13, traits, casts, SoftDeletes e scopes.
3.  **[Controllers & Actions](../project-controllers-patterns/SKILL.md)**: Nomenclatura CRUD própria, FormRequests, Validação e Actions (Pattern de Serviço).
4.  **[Views & Frontend](../project-views-patterns/SKILL.md)**: Componentes Blade `x-forms`, Bootstrap 5, JS @script e ícones.
5.  **[Mail Patterns](../project-mail-patterns/SKILL.md)**: Padrões para Mailables modernos e filas.
7.  **[Laravel PDF](../laravel-pdf/SKILL.md)**: Geração e configuração de PDFs com `spatie/laravel-pdf` (drivers, testes e entrega).
8.  **[Efficiency Guidelines](../efficiency-guidelines/SKILL.md)**: Guias de eficiência para reduzir erros comuns de LLM.

---

## 🚀 Contexto Geral

- **Stack** — PHP 8.4 · Laravel 13 · Livewire 4 · Alpine.js · Bootstrap 5
- **Convenção de código:** PSR-12 obrigatória.
- **UX/UI:** Wow Aesthetics (Glassmorphism sutil, cores harmônicas, Phosphorus/Remix Icons).

## 🛑 Regras de Ouro

- Nunca use `$rules` no componente Livewire; use **Form Objects**.
- Nunca crie `<input>` puro em formulários; use **Blade Components**.
- Lógica complexa **nunca** fica no Controller ou Livewire; use **Actions** para ações ou delegue para **Model** o tratamento dos dados.
- Cada action deve ter uma única responsabilidade clara. Se necessário, crie mais de uma action.
- Models usam `$guarded = []`. A validação e a seleção dos dados a persistir cabem ao controller ou à Action; em Livewire, o componente ou a Action executa a validação do Form Object e seleciona os dados.
- Models usam `SetDefaultUid` e `LogsModelActivity` (que incorpora `LogsActivity`), salvo exceção explícita e justificada para a entidade.
- Use **UIDs** (trait `SetDefaultUid`) para referências públicas em vez de IDs incrementais.
- Erros de validação devem ser registrados no log `validation` e notificados via Flash/Toast.
- Quando tocar em algum trecho de código que contenha algum Anti-pattern, refatorar para o pattern definido.
- Para tarefas complexas, sempre use as guidelines de eficiência para reduzir erros comuns de LLM.
