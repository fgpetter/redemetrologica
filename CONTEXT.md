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

## Relationships

- Um fluxo de inscrição ou documento pode **concluir com sucesso** mesmo quando dispara um **alerta de e-mail inválido ou ausente** (envio ao participante omitido).
- O **alerta** notifica a equipe interna; não substitui o **e-mail ao participante**.

## Example dialogue

> **Dev:** "Sem e-mail do inscrito, a action deve retornar erro?"
> **Especialista:** "Não. Cria o registro, não enfileira o job de envio, e manda o alerta interno. O cadastro não pode travar por e-mail faltando."

## Flagged ambiguities

- Relatórios estáticos que marcam `new InvalidEmailException` sem `throw` como bug — **resolvido**: é alerta operacional, não falha de transação (decisão 2026-06-03).
