# Interlaboratorial — Carta Senha

Contexto do envio de códigos de identificação (senha) e documentos associados no fluxo de interlaboratorial.

## Language

**Tag Senha**:
Código de identificação alfanumérico atribuído a um laboratório inscrito ou a um analista.
_Avoid_: Senha, código, tag

**Carta Senha**:
PDF gerado sob demanda a partir dos dados da inscrição, contendo a Tag Senha e dados do laboratório (e do analista, quando aplicável).
_Avoid_: Senha PDF, documento de senha, tag PDF

**Link de Download**:
URL pública única associada a um registro de geração de documento, pela qual o destinatário baixa a Carta Senha.
_Avoid_: Link de senha, URL da senha

**Interlab por Laboratório**:
Modalidade em que a Tag Senha e a Carta Senha pertencem ao laboratório inscrito (`avaliacao` diferente de `ANALISTA`).
_Avoid_: Fluxo de lab, senha do inscrito

**Interlab por Analista**:
Modalidade em que cada analista da inscrição possui Tag Senha e Carta Senha próprias (`avaliacao = ANALISTA`).
_Avoid_: Fluxo de analista, senha individual
