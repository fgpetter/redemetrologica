# Sistema Rede

Plataforma de gestão de cursos, interlaboratórios, inscrições e comunicação por e-mail com participantes e equipe interna.

## Language

### Notificações e e-mail

**E-mail ao participante**:
Mensagem enviada ao endereço do inscrito, analista ou contato do laboratório (confirmação, senha, certificado, etc.).
_Avoid_: "notificação" genérica quando o destinatário é o participante.

**Alerta de e-mail inválido ou ausente**:
Aviso interno para a equipe do sistema quando falta ou está inválido um e-mail necessário para envio ao participante; a operação de negócio principal não deve falhar por isso.
_Avoid_: Tratar como "erro de fluxo", "exceção não tratada", "falha silenciosa".

**InvalidEmailException** (nome legado):
Classe usada hoje para disparar o alerta interno via `new InvalidEmailException($context)` — o construtor envia o mail para operações. Instanciar **sem** `throw` é o comportamento intencional quando o fluxo deve continuar.
_Avoid_: Assumir que toda instanciação deve usar `throw`; "bug por falta de throw".

**Observação de lançamento por inscrição**:
Texto descritivo no lançamento financeiro gerado por inscrição em curso ou interlab (ex.: linha por participante/laboratório com nome, valor e data). O valor monetário na observação deve usar **formato brasileiro com duas casas decimais** (ex.: `R$ 80,00`), de forma **consistente** em curso e interlab.
_Avoid_: Valor bruto do banco sem formatação (`R$ 80`); misturar padrões entre módulos.

**Lançamento financeiro por inscrição**:
Registro provisionado gerado automaticamente ao inscrever participante (curso) ou laboratório (interlab). É sempre **receita**: tipo `CREDITO`, plano de contas Prestação de Serviços (plano 3).
_Avoid_: Criar lançamento de inscrição sem `tipo_lancamento`; tratar inscrição como débito.

**Endereço completo do laboratório (interlab)**:
Logradouro e número do imóvel representados como texto único `{logradouro}, {número}` (ex.: `Av Brasil, 1234`). No painel do cliente, o número é coletado em campo separado antes de compor esse texto.
_Avoid_: Tratar logradouro e número como campos independentes persistidos no banco; confundir com endereço de pessoa (cadastro geral de endereços).

## Relationships

- Um fluxo de inscrição ou documento pode **concluir com sucesso** mesmo quando dispara um **alerta de e-mail inválido ou ausente** (envio ao participante omitido).
- O **alerta** notifica a equipe interna; não substitui o **e-mail ao participante**.

## Example dialogue

> **Dev:** "Sem e-mail do inscrito, a action deve retornar erro?"
> **Especialista:** "Não. Cria o registro, não enfileira o job de envio, e manda o alerta interno. O cadastro não pode travar por e-mail faltando."

## Flagged ambiguities

- Relatórios estáticos que marcam `new InvalidEmailException` sem `throw` como bug — **resolvido**: é alerta operacional, não falha de transação (decisão 2026-06-03).
- Formato do valor em observação de lançamento por inscrição — **resolvido**: formato brasileiro com duas casas decimais, consistente curso/interlab (decisão 2026-06-03).
- Tipo do lançamento financeiro gerado por inscrição — **resolvido**: sempre `CREDITO` (receita / Prestação de Serviços, plano 3) (decisão 2026-06-03).
- Campo `analistas` em blocos de investimento da agenda interlab — **adiado**: validação condicional removida porque quebrava fluxo; teste `StoreAgendaInterlabRequestTest` retirado até correção; UI ainda exige analistas quando `avaliacao = ANALISTA` (decisão 2026-06-03).
