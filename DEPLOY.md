# Deploy da TME

## Objetivo

Este documento descreve os requisitos minimos para publicar a TME com mais seguranca. A plataforma ainda deve passar por hardening completo antes de ambiente enterprise.

## Servidor recomendado

Opcoes suportadas:

- Vercel com runtime comunitario `vercel-php`.
- Linux com Nginx/Apache e PHP-FPM 8.2+.

Requisitos em qualquer modelo:

- MySQL 8 ou MariaDB estavel remoto.
- HTTPS obrigatorio.
- Backup automatico.
- Monitoramento de logs.

Para Vercel, veja [VERCEL.md](VERCEL.md).

## Checklist antes do deploy

- `APP_DEBUG=false`.
- `SESSION_SECURE=true`.
- HTTPS ativo.
- Banco com usuario exclusivo e senha forte.
- Permissoes restritas em `.env`.
- `storage` gravavel pelo PHP.
- `public/uploads` sem execucao de scripts.
- Backups configurados.
- Rotina de restore testada.
- Logs centralizados.
- E-mail transacional configurado quando modulo de automacao for ativado.

## Document root

O document root do servidor deve apontar para:

```text
public/
```

Nao exponha a raiz completa do projeto na web.

## Permissoes

Diretorios gravaveis:

```text
storage/logs
storage/cache
storage/temp
public/uploads
```

Arquivos sensiveis:

```text
.env
database/
app/
config/
```

nao devem ser servidos publicamente.

## Banco de dados

1. Criar banco com `utf8mb4`.
2. Importar `database/tme_initial.sql`.
3. Aplicar migrations em ordem.
4. Conferir indices e FKs.
5. Criar rotina de backup.

## Seguranca HTTP

A aplicacao ja envia headers basicos via `Security.php`, mas o servidor tambem deve reforcar:

- HTTPS redirect.
- HSTS.
- Limite de tamanho de upload.
- Bloqueio de execucao em uploads.
- Compressao gzip/brotli.
- Cache de assets.

## Observabilidade

Minimo recomendado:

- Logs de erro PHP.
- Logs de acesso HTTP.
- Logs de banco.
- Alertas para erro 500.
- Alertas para uso de disco.
- Monitoramento de fila quando implementada.

## Escala

Para milhares de usuarios:

- Separar banco em servidor proprio.
- Usar Redis para cache/sessao/fila.
- Usar storage externo para arquivos.
- Usar CDN para assets e imagens publicas.
- Rodar workers para automacoes.
- Criar replicas de leitura quando relatorios crescerem.

## Pendencias antes de producao comercial

- Runner de migrations.
- Testes automatizados.
- 2FA.
- Recuperacao de senha.
- Politica completa de uploads privados.
- API versionada.
- Queue worker.
- TenantResolver obrigatorio.
- Logs de auditoria consolidados.
- Politica LGPD.
