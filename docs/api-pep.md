# API PEP — Guia de uso

Esta API permite consultar agendas de Programas de Ensaios de Proficiência (PEP) e seus inscritos.

| Item        | Valor                          |
|-------------|--------------------------------|
| Base URL    | `https://{domínio}/api/v1`     |
| Formato     | JSON                           |
| Método HTTP | `POST` (em todas as rotas)     |

Envie sempre o header `Content-Type: application/json`.

---

## Autenticação

Todas as requisições exigem o campo `api_key` no corpo JSON. A chave é fornecida pelo administrador do sistema.

**Exemplo:**

```json
{
  "api_key": "sua-chave-de-acesso"
}
```

| Código | Resposta                         | Quando ocorre                    |
|--------|----------------------------------|----------------------------------|
| 401    | Chave de API não informada.      | `api_key` ausente ou vazio       |
| 401    | Chave de API inválida.           | `api_key` incorreta              |

---

## Rotas

### Listar agendas

```
POST /api/v1/peps
```

Retorna a lista de agendas PEP. Todos os filtros abaixo são opcionais e podem ser combinados.

#### Parâmetros

| Parâmetro    | Tipo    | Obrigatório | Descrição |
|--------------|---------|-------------|-----------|
| `api_key`    | string  | Sim         | Chave de acesso |
| `status`     | string  | Não         | `agendado`, `confirmado` ou `concluido` |
| `ano`        | integer | Não         | Ano de referência da agenda |
| `datainicio` | date    | Não         | Data inicial do período (`AAAA-MM-DD`) |
| `datafim`    | date    | Não         | Data final do período (`AAAA-MM-DD`) |

O filtro de período usa a **data de início** da agenda:

| Parâmetros enviados              | Comportamento |
|----------------------------------|---------------|
| `datainicio` e `datafim`         | Agendas cuja data de início está entre as duas datas (inclusive) |
| Apenas `datainicio`              | Agendas com data de início a partir dessa data |
| Apenas `datafim`                 | Agendas com data de início até essa data |

#### Exemplos de requisição

**Listar todas as agendas:**

```bash
curl -X POST "https://{domínio}/api/v1/peps" \
  -H "Content-Type: application/json" \
  -d '{"api_key": "sua-chave-de-acesso"}'
```

**Filtrar por status:**

```bash
curl -X POST "https://{domínio}/api/v1/peps" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "sua-chave-de-acesso",
    "status": "confirmado"
  }'
```

**Filtrar por ano:**

```bash
curl -X POST "https://{domínio}/api/v1/peps" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "sua-chave-de-acesso",
    "ano": 2026
  }'
```

**Filtrar por período:**

```bash
curl -X POST "https://{domínio}/api/v1/peps" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "sua-chave-de-acesso",
    "datainicio": "2026-01-01",
    "datafim": "2026-12-31"
  }'
```

**Combinar filtros (status + ano + período):**

```bash
curl -X POST "https://{domínio}/api/v1/peps" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "sua-chave-de-acesso",
    "status": "confirmado",
    "ano": 2026,
    "datainicio": "2026-01-01",
    "datafim": "2026-12-31"
  }'
```

#### Resposta esperada — sucesso (200)

```json
{
  "data": [
    {
      "nome_interlab": "PEP Bebidas",
      "uid": "abc123def456",
      "status": "confirmado",
      "ano_referencia": 2026
    },
    {
      "nome_interlab": "PEP Solos",
      "uid": "xyz789ghi012",
      "status": "agendado",
      "ano_referencia": 2025
    }
  ],
  "meta": {
    "total": 2
  }
}
```

| Campo            | Descrição |
|------------------|-----------|
| `nome_interlab`  | Nome do programa PEP |
| `uid`            | Identificador da agenda — use na rota de detalhe |
| `status`         | `agendado`, `confirmado` ou `concluido` |
| `ano_referencia` | Ano de referência |
| `meta.total`     | Quantidade de agendas retornadas |

#### Resposta esperada — parâmetro inválido (422)

Enviado, por exemplo, `"status": "cancelado"`:

```json
{
  "message": "O status deve ser agendado, confirmado ou concluido.",
  "errors": {
    "status": [
      "O status deve ser agendado, confirmado ou concluido."
    ]
  }
}
```

---

### Detalhar agenda

```
POST /api/v1/pep/{uid}
```

Retorna os dados completos de uma agenda, incluindo valores de inscrição e inscritos agrupados por empresa.

#### Parâmetros

| Parâmetro | Local  | Tipo   | Obrigatório | Descrição |
|-----------|--------|--------|-------------|-----------|
| `uid`     | URL    | string | Sim         | Identificador da agenda (campo `uid` da listagem) |
| `api_key` | corpo  | string | Sim         | Chave de acesso |

#### Exemplo de requisição

```bash
curl -X POST "https://{domínio}/api/v1/pep/abc123def456" \
  -H "Content-Type: application/json" \
  -d '{"api_key": "sua-chave-de-acesso"}'
```

#### Resposta esperada — sucesso (200)

```json
{
  "data": {
    "agenda": {
      "id": 1,
      "uid": "abc123def456",
      "interlab_id": 10,
      "status": "confirmado",
      "inscricao": true,
      "site": true,
      "destaque": false,
      "descricao": "Descrição da agenda",
      "data_inicio": "2026-04-01",
      "data_fim": "2026-04-30",
      "instrucoes_inscricao": "Instruções para inscrição",
      "ano_referencia": 2026,
      "data_limite_inscricao": "2026-03-15",
      "valor_desconto": null,
      "protocolo": null,
      "created_at": "2026-01-10T12:00:00.000000Z",
      "updated_at": "2026-02-01T08:30:00.000000Z"
    },
    "nome_interlab": "PEP Solos",
    "valores": [
      {
        "id": 1,
        "uid": "valor-uid-123",
        "agenda_interlab_id": 1,
        "descricao": "Inscrição padrão",
        "valor": "1500.00",
        "valor_assoc": "1200.00",
        "analistas": null,
        "created_at": "2026-01-10T12:00:00.000000Z",
        "updated_at": "2026-01-10T12:00:00.000000Z"
      }
    ],
    "inscritos_por_empresa": [
      {
        "empresa": {
          "id": 2,
          "uid": "empresa-uid-b",
          "nome_razao": "Empresa B",
          "cpf_cnpj": "12345678000199",
          "associado": true
        },
        "inscritos": [
          {
            "id": 5,
            "uid": "inscrito-uid-b",
            "pessoa_id": 100,
            "empresa_id": 2,
            "laboratorio_id": 50,
            "pessoa_inscrito_id": 101,
            "agenda_interlab_id": 1,
            "data_inscricao": "2026-03-01",
            "valor": "1500.00",
            "pesquisa_id": null,
            "resposta_pesquisa": null,
            "certificado_emitido": false,
            "certificado_path": null,
            "informacoes_inscricao": null,
            "responsavel_tecnico": "João Silva",
            "telefone": "11999999999",
            "email": "contato@empresa.com",
            "lancamento_financeiro_id": null,
            "senha_enviada": null,
            "created_at": "2026-03-01T10:00:00.000000Z",
            "updated_at": "2026-03-01T10:00:00.000000Z",
            "laboratorio": {
              "id": 50,
              "nome": "Laboratório Central"
            }
          }
        ]
      },
      {
        "empresa": {
          "id": 1,
          "uid": "empresa-uid-a",
          "nome_razao": "Empresa A",
          "cpf_cnpj": "98765432000188",
          "associado": false
        },
        "inscritos": [
          {
            "id": 4,
            "uid": "inscrito-uid-a",
            "data_inscricao": "2026-02-15",
            "responsavel_tecnico": "Maria Souza",
            "telefone": "11888888888",
            "email": "maria@empresa-a.com",
            "laboratorio": { }
          }
        ]
      }
    ]
  }
}
```

**Observações sobre a resposta:**

- `inscritos_por_empresa` agrupa os inscritos por empresa.
- As empresas aparecem ordenadas pela inscrição mais recente de cada uma.
- Dentro de cada empresa, os inscritos aparecem ordenados por data de inscrição (mais recente primeiro).
- Em PEPs avaliados por analista, cada inscrito inclui também o array `analistas`:

```json
"analistas": [
  {
    "id": 1,
    "uid": "analista-uid-123",
    "nome": "Analista Teste",
    "email": "analista@example.com",
    "telefone": "11999999999",
    "created_at": "2026-03-01T10:00:00.000000Z",
    "updated_at": "2026-03-01T10:00:00.000000Z"
  }
]
```

#### Resposta esperada — agenda não encontrada (404)

```json
{
  "message": "Agenda PEP não encontrada."
}
```

---

## Códigos de resposta

| Código | Significado |
|--------|-------------|
| 200    | Sucesso |
| 401    | Chave de API ausente ou inválida |
| 404    | Agenda não encontrada (rota de detalhe) |
| 422    | Parâmetro inválido (ex.: status fora dos valores permitidos) |

---

## Fluxo de uso

1. Chame `POST /api/v1/peps` para obter a lista de agendas.
2. Use o campo `uid` de cada agenda para chamar `POST /api/v1/pep/{uid}` e obter os detalhes e inscritos.
