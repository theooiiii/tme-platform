# Documentacao do banco de dados

## Banco principal

Nome padrao: `tme_platform`

Arquivo base:

- `database/tme_initial.sql`

Migrations:

- `database/migrations/*.sql`

## Observacao sobre migrations

O projeto possui arquivos SQL incrementais, mas ainda nao possui um runner de migrations. Em producao, a TME deve ter uma tabela de controle:

```sql
CREATE TABLE migrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(180) NOT NULL UNIQUE,
    batch INT UNSIGNED NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Grupos de tabelas atuais

### Identidade e acesso

- `users`
- `roles`
- `permissions`
- `role_permissions`
- `user_settings`

### Instituicoes e SaaS

- `institutions`
- `organizations`
- `organization_domains`
- `organization_members`

`institutions` representa escolas ou instituicoes educacionais referenciadas em cadastro.
`organizations` representa o cliente SaaS/tenant que possui usuarios, cursos, tema, dominio e configuracoes.

### LMS

- `courses`
- `course_modules`
- `lessons`
- `materials`
- `enrollments`
- `lesson_progress`
- `activities`
- `submissions`
- `grades`

### Turmas e disciplinas

- `classes`
- `subjects`
- `class_students`
- `class_teachers`
- `class_subjects`
- `subject_teachers`

### Biblioteca

- `library_items`
- `library_favorites`
- `library_access_logs`

### Comunidade

- `posts`
- `comments`
- `post_likes`
- `post_saves`
- `content_moderation`

### Eventos e certificados

- `events`
- `event_registrations`
- `certificates`

### Gamificacao

- `gamification_profiles`
- `badges`
- `user_badges`
- `gamification_events`

### Provas

- `question_bank`
- `exams`
- `exam_questions`
- `exam_attempts`
- `exam_answers`

### Frequencia

- `attendance_records`

### Chat

- `chat_channels`
- `chat_channel_members`
- `chat_messages`

### Financeiro

- `plans`
- `transactions`
- `subscriptions`
- `creator_wallets`

### Notificacoes e logs

- `notifications`
- `logs`
- `audit_events`

### CRM, suporte, automacoes e API

- `crm_leads`
- `crm_lead_contacts`
- `support_tickets`
- `support_ticket_messages`
- `automation_jobs`
- `api_tokens`
- `webhook_endpoints`
- `password_resets`
- `login_attempts`

## Indices recomendados

Para multi-tenant, as consultas devem priorizar indices compostos:

- `(organization_id, status)`
- `(organization_id, created_at)`
- `(organization_id, user_id)`
- `(organization_id, course_id)`
- `(organization_id, read_at)`

## Politica de dados

Recomendacoes para producao:

- Nunca remover certificados emitidos; revogar por status.
- Nunca apagar logs sensiveis; arquivar por periodo.
- Manter trilha LGPD para exportacao/exclusao de dados pessoais.
- Separar uploads privados fora de `public`.
- Usar backups diarios e restore testado.

## Convencoes recomendadas

- IDs: `BIGINT UNSIGNED`.
- Charset: `utf8mb4_unicode_ci`.
- Timestamps: `created_at`, `updated_at`, `deleted_at` quando houver soft delete.
- Status: enums ou tabelas de dominio quando o status crescer.
- Valores financeiros: `DECIMAL(10,2)`.
- Metadados flexiveis: `JSON`, com indices gerados apenas quando necessario.
