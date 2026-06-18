# Instalacao local da TME

## Requisitos

- PHP 8.2 ou superior.
- MySQL ou MariaDB.
- Apache com `mod_rewrite`.
- Navegador moderno.
- Git.
- XAMPP 8.2 recomendado para ambiente Windows local.

## Passo a passo com XAMPP

1. Clone ou copie o projeto para:

```text
C:\xampp\htdocs\tme-plataform
```

2. Inicie Apache e MySQL no XAMPP.

3. Crie o banco:

```sql
CREATE DATABASE tme_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Importe o SQL inicial:

```text
database/tme_initial.sql
```

5. Importe as migrations em ordem:

```text
database/migrations/*.sql
```

6. Copie `.env.example` para `.env`.

7. Ajuste as variaveis:

```env
APP_URL=http://localhost/tme-plataform/public
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_DATABASE=tme_platform
DB_USERNAME=root
DB_PASSWORD=
```

8. Abra:

```text
http://localhost/tme-plataform/public
```

## Variaveis de seguranca

```env
SESSION_NAME=TMESESSID
SESSION_SECURE=false
SESSION_SAMESITE=Lax
SESSION_LIFETIME=7200
CSP_ENABLED=true
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_SECONDS=900
```

Em producao com HTTPS, use:

```env
SESSION_SECURE=true
APP_DEBUG=false
```

## Validacao local

Execute:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File tools\validate-project.ps1
```

Esse script verifica estrutura, PHP lint, CSS e arquivos principais.

## Problemas comuns

### Tela 404 em rotas internas

Verifique se o Apache esta com `mod_rewrite` ativo e se o `.htaccess` esta sendo respeitado.

### Erro de conexao com banco

Confirme `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env`.

### Upload nao aparece

Verifique permissoes em:

```text
public/uploads
storage
```

### Login bloqueado por tentativas

O rate limit local fica em:

```text
storage/cache/rate-limit
```

Aguarde o tempo configurado em `LOGIN_DECAY_SECONDS` ou limpe o cache em ambiente local.
