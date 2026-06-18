# API da TME

## Estado atual

A TME ainda nao possui API REST publica completa. A migration `2026_06_18_saas_foundation.sql` adiciona a tabela `api_tokens` e `webhook_endpoints` para preparar a camada de integracoes externas.

## Padrao alvo

Base URL:

```text
/api/v1
```

Formato:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <token>
```

Quando o servidor/proxy nao repassar o header `Authorization`, use:

```http
X-Api-Token: <token>
```

## Recursos planejados

### Autenticacao

- `POST /api/v1/auth/token`
- `DELETE /api/v1/auth/token`
- `GET /api/v1/me`

### Organizacoes

- `GET /api/v1/organization`
- `PATCH /api/v1/organization`

### Usuarios

- `GET /api/v1/users`
- `GET /api/v1/users/{id}`
- `POST /api/v1/users`
- `PATCH /api/v1/users/{id}`

### Cursos

- `GET /api/v1/courses`
- `POST /api/v1/courses`
- `GET /api/v1/courses/{id}`
- `PATCH /api/v1/courses/{id}`
- `DELETE /api/v1/courses/{id}`

### Matriculas

- `GET /api/v1/enrollments`
- `POST /api/v1/courses/{id}/enroll`
- `PATCH /api/v1/enrollments/{id}`

### Progresso

- `POST /api/v1/lessons/{id}/complete`
- `GET /api/v1/users/{id}/progress`

### Certificados

- `GET /api/v1/certificates`
- `GET /api/v1/certificates/{code}/validate`

### Financeiro

- `GET /api/v1/plans`
- `POST /api/v1/subscriptions`
- `GET /api/v1/transactions`

### Webhooks

- `POST /api/v1/webhooks`
- `GET /api/v1/webhooks`
- `DELETE /api/v1/webhooks/{id}`

## Respostas padronizadas

Sucesso:

```json
{
  "data": {},
  "meta": {}
}
```

Erro:

```json
{
  "error": {
    "code": "validation_error",
    "message": "Dados invalidos.",
    "fields": {}
  }
}
```

## Codigos HTTP

- `200`: sucesso.
- `201`: criado.
- `204`: sem conteudo.
- `400`: requisicao invalida.
- `401`: nao autenticado.
- `403`: sem permissao.
- `404`: nao encontrado.
- `422`: validacao.
- `429`: limite excedido.
- `500`: erro interno.

## Seguranca da API

- Tokens devem ser armazenados apenas como hash.
- Rate limit por token e IP.
- Escopo por `organization_id`.
- Abilities por token.
- Logs em `audit_events`.
- Webhooks assinados por segredo.

## OpenAPI

Arquivo criado:

```text
docs/openapi.yaml
```

Ele descreve a API REST inicial implementada em `/api/v1`.
