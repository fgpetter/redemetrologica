---
description: Definição de Padrões de Projeto
---

# Workflow: Definição de Padrões de Projeto

## Trigger

- Comando manual: `@define-patterns` ou via análise de novo módulo.

## Sequence

### Phase 1: Discovery

1. Buscar arquivos relacionados ao módulo selecionado.
2. Analisar a estrutura de código em:
   - **Model:** Verificar `Mass Assignment`, `Relationships`, `Scopes`.
   - **Controller/Livewire:** Verificar validação, tratamento de erros e redirecionamentos.
   - **Frontend:** Verificar consistência de UI (Tailwind/Alpine) e reatividade.

### Phase 2: Synthesis

1. Gerar um relatório temporário:
   - "Padrão Identificado: [Ex: Clean Architecture / CRUD Simples]"
   - "Exemplo de Código de Referência: [Trecho curto]"
   - "Regras de Nomenclatura detectadas."

### Phase 3: Validation

1. **Aguardar input do usuário:** "Este padrão reflete a arquitetura desejada para o PremiumWeb? (Sim/Não/Ajustar)"

### Phase 4: Skill Update

1. Se "Sim", atualizar ou criar o arquivo `.agent/skills/project-patterns/SKILL.md`.
2. O arquivo deve conter:
   - **Contexto:** "Siga estas regras para novos módulos."
   - **Specs:** Regras técnicas por camada (Model, View, Controller).
   - **Anti-patterns:** O que evitar (baseado em erros comuns detectados)./
