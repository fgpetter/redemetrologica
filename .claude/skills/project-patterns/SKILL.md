---
name: project-patterns
description: Índice central dos padrões arquiteturais do projeto sistema-rede (Laravel + Livewire). Use as sub-skills para detalhes específicos.
---

# Padrões do Projeto — Sistema Rede Metrológica

Os padrões deste projeto foram modularizados para facilitar a consulta. Siga o link correspondente à camada que você está trabalhando:

## 📚 Índice de Padrões

1.  **[Livewire Patterns](project-livewire-patterns/SKILL.md)**: Arquitetura v3, componentes Table/Listview/Modalform, Form Objects e eventos.
2.  **[Models Patterns](project-models-patterns/SKILL.md)**: Eloquent, Traits, Accessors PHP 8.3+, SoftDeletes e Scopes.
3.  **[Controllers & Actions](project-controllers-patterns/SKILL.md)**: Nomenclatura CRUD própria, FormRequests, Validação e Actions (Pattern de Serviço).
4.  **[Views & Frontend](project-views-patterns/SKILL.md)**: Componentes Blade `x-forms`, Bootstrap 5, JS @script e ícones.
5.  **[Mail Patterns](project-mail-patterns/SKILL.md)**: Padrões para Mailables modernos e filas.
6.  **[Livewire Development](livewire-development/SKILL.md)**: Guia operacional para desenvolvimento e depuração de componentes Livewire 3.
7.  **[Laravel PDF](laravel-pdf/SKILL.md)**: Geração e configuração de PDFs com `spatie/laravel-pdf` (drivers, testes e entrega).
8.  **[Efficiency Guidelines](efficiency-guidelines/SKILL.md)**: Guias de eficiência para reduzir erros comuns de LLM.

---

## 🚀 Contexto Geral

- **Stack:** PHP 8.3 · Laravel 11 · Livewire v3 · Alpine.js · Bootstrap 5
- **Convenção de código:** PSR-12 obrigatória.
- **UX/UI:** Wow Aesthetics (Glassmorphism sutil, cores harmônicas, Phosphorus/Remix Icons).

## 🛑 Regras de Ouro

- Nunca use `$rules` no componente Livewire; use **Form Objects**.
- Nunca crie `<input>` puro em formulários; use **Blade Components**.
- Lógica complexa **nunca** fica no Controller ou Livewire; use **Actions** para ações ou delegue para **Model** o tratamento dos dados.
- Cada action deve ter uma única responsabilidade clara. Se necessário, crie mais de uma action.
- Use **UIDs** (trait `SetDefaultUid`) para referências públicas em vez de IDs incrementais.
- Erros de validação devem ser registrados no log `validation` e notificados via Flash/Toast.
- Quando tocar em algum trecho de código que contenha algum Anti-pattern, refatorar para o pattern definido.
- Para tarefas complexas, sempre use as guidelines de eficiência para reduzir erros comuns de LLM.
