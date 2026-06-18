# TME — Theo Mind Educacional

Tecnologia, ensino e evolução em uma única plataforma.

A TME é uma base MVC própria em PHP para uma plataforma educacional moderna que mistura LMS, EAD, sistema escolar, comunidade acadêmica, gamificação, marketplace e gestão educacional.

## Tecnologias

- PHP 8+
- MySQL
- PDO com prepared statements
- HTML5, CSS3 e JavaScript puro
- MVC próprio, sem Laravel
- Sessões PHP para autenticação
- Estrutura reservada para módulos e integrações futuras em Python/IA

## Documentação técnica

- [INSTALL.md](INSTALL.md): instalação local e XAMPP.
- [DEPLOY.md](DEPLOY.md): checklist de publicação e hardening.
- [CHANGELOG.md](CHANGELOG.md): histórico de mudanças.
- [VERCEL.md](VERCEL.md): deploy serverless na Vercel.
- [docs/AUDIT_REPORT.md](docs/AUDIT_REPORT.md): auditoria técnica, riscos e roadmap.
- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md): arquitetura atual e arquitetura alvo.
- [docs/DATABASE.md](docs/DATABASE.md): documentação do banco e grupos de tabelas.
- [docs/API.md](docs/API.md): plano da API REST.
- [docs/openapi.yaml](docs/openapi.yaml): contrato OpenAPI inicial.

## Funcionalidades da primeira entrega

- Home pública e páginas institucionais: Sobre, Cursos, Eventos, Biblioteca, Comunidade, Login e Cadastro.
- Cadastro de aluno ou professor com status inicial `pendente`.
- Login permitido apenas para contas `aprovado`.
- Perfil completo em `/perfil` com personalização, biografia, estatísticas, badges e alteração de senha.
- Dashboards separados para aluno, professor, supervisor, administrador, secretaria e financeiro.
- Aprovação e recusa de contas por administrador ou supervisor.
- Tema claro/escuro e cor principal personalizável por usuário.
- Roles, permissões, instituições, cursos, turmas, atividades, comunidade, eventos, certificados, gamificação, financeiro, notificações e logs modelados no banco.
- Estrutura preparada para importações futuras do INEP e e-MEC.

## Experiencia autenticada e Portal TME

A vitrine pública e a área logada agora são experiências separadas:

- Visitantes continuam vendo Home, Sobre, Cursos, Eventos, Biblioteca, Comunidade, Login e Cadastro.
- Usuários autenticados são redirecionados para `/portal` depois do login aprovado.
- O seletor rapido de tema/cor saiu da navbar; tema, cor principal e preview ficam em `/perfil`.
- O Portal mostra XP, nível, moedas, streak e badges recentes.
- A rota `/inicio` também abre o Portal TME.
- A Home pública (`/`) redireciona usuários logados para o portal interno.
- O menu de usuários logados oculta Login/Cadastro e prioriza Início, Dashboard, Cursos, Meus cursos, Biblioteca, Eventos, Comunidade, Configurações/Tema e Sair.
- Alunos e professores acessam o catálogo em `/aluno/cursos` e a área neutra `/meus-cursos`.
- Professores também podem estudar como alunos, mantendo acesso a matrículas e progresso.
- Administradores e supervisores veem atalhos de administração, aprovações, cursos admin e matrículas.
- `/portal` e `/configuracoes` exigem autenticação.

## Módulo administrativo de cursos

O admin/supervisor acessa `Administração > Cursos admin` para gerenciar o catálogo acadêmico.

Recursos disponíveis:

- CRUD de cursos com título, descrição, categoria, nível, carga horária, preço, status, professor responsável e imagem opcional.
- Filtros de listagem por status, categoria e professor.
- Arquivamento de curso em vez de exclusão definitiva.
- CRUD de módulos vinculados ao curso.
- CRUD de aulas vinculadas ao curso e opcionalmente a um módulo.
- Campos de aula: título, descrição, tipo, vídeo/link, conteúdo textual, ordem, duração e status.
- Cadastro de materiais por aula, com PDF, imagem, link externo, arquivo, livro, apostila ou vídeo.
- Uploads salvos em `public/uploads/course-images` e `public/uploads/materials`.
- Logs em `logs` para criação, edição, remoção e arquivamento.
- CSRF, sessão, middleware de autenticação e proteção por role `administrador/supervisor`.

Alunos e professores aprovados acessam `Meus cursos` para ver cursos publicados, detalhes do curso, módulos, aulas e materiais ativos.

Para bancos existentes, aplique a migration:

```bash
mysql -u root -p < database/migrations/2026_05_23_admin_courses_module.sql
```

## Matrículas e progresso do aluno

Alunos e professores aprovados podem se matricular em cursos publicados pelo catálogo em `Catálogo`.

Recursos disponíveis:

- Botão `Matricular-se` no detalhe do curso publicado.
- Bloqueio de matrícula duplicada por aluno e curso.
- Página `Meus cursos` com cursos matriculados.
- Status de matrícula: `ativa`, `concluída` e `cancelada`.
- Registro de data de início, última atividade e conclusão.
- Progresso percentual calculado automaticamente pelas aulas publicadas concluídas.
- Marcação de aula como concluída com CSRF e validação de vínculo da matrícula.
- Mudança automática da matrícula para `concluída` ao atingir 100%.
- Visualização de materiais ativos disponíveis em cada aula.
- Área administrativa em `Administração > Matrículas`, com filtros por curso, aluno e status.
- Logs para matrícula criada, aula concluída e curso concluído.

Para bancos existentes, aplique também:

```bash
mysql -u root -p < database/migrations/2026_05_23_enrollments_progress_module.sql
```

## Atividades, entregas e notas

Professores, administradores e supervisores gerenciam atividades em `/admin/atividades`.
Alunos e professores matriculados acompanham tarefas em `/atividades` e notas em `/boletim`.

Recursos disponiveis:

- CRUD de atividades vinculadas a curso, módulo e aula, com base futura para turma/disciplina.
- Campos: título, descrição, tipo, pontuação máxima, prazo, status, instruções e anexo opcional.
- Tipos: `texto`, `arquivo`, `quiz`, `tarefa_pratica` e `projeto`.
- Entrega textual e/ou arquivo pelo aluno matriculado.
- Bloqueio de envio após prazo quando a atividade não permite atraso.
- Entregas atrasadas marcadas automaticamente quando permitido.
- Status de entrega: `pendente`, `enviada`, `atrasada`, `corrigida` e `devolvida`.
- Correção por professor/admin/supervisor com nota, feedback e status.
- Boletim simples por curso.
- Logs para criação, envio, encerramento e correção.
- Uploads em `public/uploads/activity-attachments` e `public/uploads/activity-submissions`.

## Biblioteca digital

A biblioteca pública/interna fica em `/biblioteca` e a gestão em `/admin/biblioteca`.
Ela e separada dos materiais de aulas, mas usa os mesmos padroes visuais, CSRF, PDO e logs.

Recursos disponiveis:

- CRUD administrativo de itens da biblioteca para administrador, supervisor e professor.
- Envio de materiais por aluno/professor em `/biblioteca/enviar`, sempre entrando como `pendente`.
- Campos: título, descrição, categoria, disciplina, tipo, visibilidade, autor, arquivo/link, capa e status.
- Tipos: PDF, livro, apostila, artigo, vídeo, link, apresentação, imagem e arquivo.
- Visibilidade: pública, somente logados, curso específico e privada/admin.
- Busca por título, categoria, disciplina e tipo.
- Favoritos por usuário em `/biblioteca/favoritos`.
- Histórico simples de acesso/leitura em `library_access_logs`.
- Moderação por administrador/supervisor com aprovação, recusa e arquivamento.
- Logs para criação, aprovação, recusa, visualização e favorito.
- Uploads em `public/uploads/library` e `public/uploads/library-covers`.

Para bancos existentes, aplique também:

```bash
mysql -u root -p < database/migrations/2026_05_23_activities_library_module.sql
```

## Certificados

Alunos e professores acessam `/certificados` para consultar certificados emitidos automaticamente. A validação pública fica em `/certificados/validar`.

Recursos disponiveis:

- Emissão automática quando uma matrícula chega a 100% de progresso.
- Código único no formato `TME-CUR-ANO-CODIGO`.
- Visualização HTML do certificado em `/certificados/ver/{codigo}`.
- Botao `Imprimir/Salvar PDF` usando o print do navegador.
- Validação pública por código, marcando certificados revogados como inválidos.
- Área administrativa em `/admin/certificados` para listar, filtrar e revogar certificados com motivo.
- Estrutura preparada para QR Code futuro.
- Logs para emissão, visualização, validação e revogação.

## Gamificação

A TME agora possui XP, niveis, moedas internas, streak, badges e ranking inicial.

Recursos disponiveis:

- Perfil de gamificação para cada usuário aprovado.
- Regras centralizadas em `app/services/GamificationService.php`.
- XP por login inicial, matrícula, aula concluída, curso concluído, atividade enviada, boa nota, favorito de biblioteca e certificado emitido.
- Badges iniciais: Primeiro Login, Primeiro Curso, Primeira Aula Concluída, Curso Finalizado, Explorador da Biblioteca e Aluno Dedicado.
- Ranking global e ranking filtrado por curso em `/ranking`.
- Portal e Perfil exibem XP, nível, moedas, streak e conquistas recentes.
- Eventos de XP evitam duplicidade por ação/referência e registram logs.

## Perfil e configurações

Preferências e dados do usuário foram centralizados em `/perfil` e `/configuracoes`.

Recursos disponiveis:

- Informações do usuário, instituição, área de interesse e biografia curta.
- Placeholder para foto de perfil futura.
- Tema claro/escuro e cor principal com preview antes de salvar.
- Estatísticas: XP, nível, cursos matriculados, cursos concluídos, atividades entregues, certificados e badges recentes.
- Alteração de senha com senha atual, confirmação e `password_hash`.
- Logout e área reservada para sessões futuras.

Para bancos existentes, aplique também:

```bash
mysql -u root -p < database/migrations/2026_05_23_certificates_gamification_profile.sql
```

## Comunidade acadêmica

A comunidade fica em `/comunidade` para usuários logados e a moderação fica em `/admin/comunidade`.

Recursos disponiveis:

- Feed acadêmico com posts aprovados e posts destacados.
- Criação de posts dos tipos: dúvida, artigo, projeto, material, conquista e aviso.
- Posts de alunos/professores entram como `pendente`; admin/supervisor pode aprovar, recusar, arquivar e destacar.
- Comentarios em posts aprovados.
- Curtir e salvar posts por usuário.
- Perfil do usuário exibe posts recentes e status de moderação.
- Logs para criação, aprovação, recusa, comentário e curtida/salvo.

## Eventos

Eventos publicados aparecem em `/eventos`; a administração fica em `/admin/eventos`.

Recursos disponiveis:

- Cadastro administrativo de eventos com título, descrição, tipo, data/hora, local/link, vagas, carga horária, status e imagem opcional.
- Tipos: palestra, workshop, aula ao vivo, simulado, olimpíada e hackathon.
- Usuário logado pode se inscrever, com bloqueio de inscrição duplicada.
- Admin visualiza inscritos, confirma presença e altera status do evento.
- Evento `encerrado`, com presença confirmada e certificado habilitado, pode gerar certificado de participação.
- Portal mostra eventos inscritos do usuário.
- Logs para criação, inscrição, presença e certificado.

## Turmas e disciplinas

A gestão fica em `/admin/turmas` e a visualização do aluno/professor fica em `/turmas`.

Recursos disponiveis:

- CRUD inicial de turmas com nome, descrição, instituição, período e status.
- Cadastro de disciplinas com nome, descrição, área, carga horária e status.
- Vínculo de alunos a turmas.
- Vínculo de professores a turmas e disciplinas.
- Detalhe da turma com disciplinas, alunos, professores e área preparada para materiais futuros.
- Estrutura preparada para calendário, frequência e ranking por turma.
- Logs de criação e vinculos.

Para bancos existentes, aplique também:

```bash
mysql -u root -p < database/migrations/2026_05_24_community_events_classes.sql
```

## Frequência

Admin, supervisor e professor registram chamada em `/frequencia`; alunos e professores acompanham seu histórico em `/minha-frequencia`.

Recursos disponiveis:

- Selecao de turma, disciplina e data.
- Marcação por aluno como `presente`, `falta`, `atraso` ou `justificado`.
- Observação individual por aluno.
- Relatório em `/frequencia/relatorio` por turma, disciplina, aluno e período.
- Percentual de frequência calculado automaticamente.
- Sem notificação ou alerta para responsáveis.
- Logs de chamada e alteracoes relevantes.

## Simulados e provas

A gestão fica em `/admin/provas`; alunos e professores acessam as avaliações em `/provas`.

Recursos disponiveis:

- Criação de provas com título, descrição, curso, turma, disciplina, tempo limite, período, tentativas, status e ranking opcional.
- Banco de questões com objetivas e discursivas.
- Alternativas, resposta correta e pontuação por questão.
- Tentativas com controle simples de tempo no navegador e validação no envio.
- Respostas salvas por tentativa.
- Correção automática de objetivas.
- Discursivas ficam como `pendente_correção` até correção manual.
- Resultado individual, desempenho por disciplina e ranking por prova quando habilitado.
- Logs de criação, tentativa, envio e correção.

## Chat interno

Usuários aprovados acessam `/chat`; administradores e supervisores podem auditar conversas em `/admin/chat` para segurança e moderação.

Recursos disponiveis:

- Mensagens privadas entre usuários aprovados.
- Grupos automaticos por turma para alunos e professores vinculados.
- Lista de conversas com indicador simples de não lidas.
- Leitura e envio de mensagens com CSRF e controle por permissão.
- Atualização simples por refresh periódico quando o usuário não está digitando.
- Bloqueio de envio para usuários pendentes ou recusados.
- Logs de envio, leitura/auditoria e moderação.

Para bancos existentes, aplique também:

```bash
mysql -u root -p < database/migrations/2026_05_24_attendance_exams_chat.sql
```

## Financeiro e assinaturas

Planos públicos ficam em `/planos`, histórico financeiro em `/financeiro` e a gestão administrativa em `/admin/planos`.

Recursos disponiveis:

- Planos gratuitos e premium com nome, descrição, preço, duração, benefícios e status.
- Assinatura de plano por usuário logado, com transação e assinatura persistidas.
- Status financeiros: `pendente`, `pago`, `cancelado`, `expirado` e `estornado`.
- Estrutura inicial para PIX/cartão via campos de gateway, referência, vencimento e expiração.
- Controle premium por plano e middleware `premium` para recursos futuros.
- Cursos podem ser marcados como `gratuito` ou `premium`; curso premium exige assinatura ativa.
- Histórico financeiro do usuário e carteira creator preparada para monetização 80/20.
- Moedas internas seguem integradas ao perfil de gamificação.
- Logs financeiros para criação/edição de planos e pedidos de assinatura.

## Notificações

A navbar autenticada possui ícone de notificações com contador e dropdown. A central completa fica em `/notificacoes`.

Recursos disponiveis:

- Notificações persistidas no banco com tipo, prioridade, link de ação e leitura.
- Servico central em `app/services/NotificationService.php`.
- Marcar notificação como lida/não lida e marcar todas como lidas.
- Eventos notificados: matrícula, curso concluído, certificado emitido, atividade corrigida, comentário em post, mensagem no chat, inscrição em evento, prova liberada e badge conquistada.
- Logs de envio em `logs` pela ação `notification.sent`.

## Analytics e dashboard avançado

Dashboards de aluno, professor e administrador agora exibem métricas reais e gráficos com Chart.js. Administradores e supervisores também acessam `/analytics`.

Recursos disponiveis:

- Admin: usuários ativos, matrículas, cursos populares, atividade da plataforma, crescimento, certificados e receita paga.
- Professor: alunos ativos, entregas pendentes, desempenho medio e progresso medio dos cursos.
- Aluno: progresso geral, frequência, XP semanal, desempenho em provas e certificados.
- Filtro por período em `/analytics`.
- Fallback em canvas simples quando o CDN do Chart.js não estiver disponível.

Para bancos existentes, aplique também:

```bash
mysql -u root -p < database/migrations/2026_05_24_finance_notifications_analytics.sql
```

## Organização visual e CSS

A interface foi reorganizada para uma base visual de plataforma SaaS educacional premium, mantendo as classes usadas pelas views e sem alterar rotas ou permissões.

Arquivos CSS principais:

- `assets/css/base.css`: variaveis globais, reset, tipografia, campos e tokens de tema.
- `assets/css/layout.css`: navbar, dropdowns, header, containers, rodape e mensagens.
- `assets/css/components.css`: botões, cards, métricas, tabelas, formulários, badges, notificações e gráficos.
- `assets/css/dashboard.css`: Portal, dashboards, perfil, ranking e paineis de dados.
- `assets/css/modules.css`: cursos, comunidade, financeiro, planos, biblioteca, eventos, provas, frequência e chat.
- `assets/css/responsive.css`: ajustes mobile/tablet, menu responsivo, grids e impressao.
- `assets/css/themes.css`: tema escuro e ajustes dependentes de tema.

`assets/css/style.css` permanece apenas como agregador por compatibilidade. O layout principal carrega os arquivos separados diretamente.

## Estrutura

```text
tme-platform/
├── app/
│   ├── controllers/
│   ├── core/
│   ├── helpers/
│   ├── middleware/
│   ├── models/
│   ├── services/
│   └── views/
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   └── icons/
├── config/
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── tme_initial.sql
├── modules/
├── ai/python/
├── public/
│   ├── index.php
│   └── uploads/
├── storage/
├── .env.example
├── .gitignore
└── README.md
```

## Instalação local

1. Copie `.env.example` para `.env`.
2. Ajuste as credenciais do MySQL no `.env`.
3. Crie/importe o banco usando `database/tme_initial.sql`.
4. Aponte o servidor web para a pasta `public/` ou acesse a aplicação pelo caminho `/public`.

Exemplo de `.env` para XAMPP:

```env
APP_URL=http://localhost/tme-plataform/public
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tme_platform
DB_USERNAME=root
DB_PASSWORD=
```

## Importar banco de dados

Pelo phpMyAdmin:

1. Abra `http://localhost/phpmyadmin`.
2. Vá em Importar.
3. Selecione `database/tme_initial.sql`.
4. Execute a importação.

Pelo terminal:

```bash
mysql -u root -p < database/tme_initial.sql
```

O SQL cria o banco `tme_platform`, as tabelas iniciais e um administrador demo.

Credenciais iniciais:

- E-mail: `admin@tme.local`
- Senha: `password`

Troque essa senha no primeiro uso em ambiente real.

## Rodar no XAMPP

1. Coloque a pasta do projeto dentro de `htdocs`.
2. Inicie Apache e MySQL no painel do XAMPP.
3. Importe `database/tme_initial.sql`.
4. Configure `.env`.
5. Acesse `http://localhost/tme-plataform/public`.

Para um ambiente mais limpo, crie um VirtualHost apontando o `DocumentRoot` para `public/`.

## Fluxo inicial

1. Usuário acessa Home e entra em Cadastro.
2. Cadastro aceita apenas `aluno` ou `professor`.
3. A conta é gravada como `pendente`.
4. Administrador ou supervisor acessa `Aprovações`.
5. Conta aprovada pode fazer login.
6. Após login, o usuário vai para o dashboard do próprio perfil.

## Segurança aplicada

- Senhas com `password_hash` e verificação com `password_verify`.
- Login com sessão e `session_regenerate_id`.
- Sessão configurada com `HttpOnly`, `SameSite`, modo estrito e opção `Secure` para HTTPS.
- Rate limit de login por IP/e-mail.
- Headers HTTP básicos de segurança via `Security.php`.
- Middleware de autenticação e middleware por role.
- PDO configurado com exceptions, fetch associativo e prepared statements.
- CSRF token nos formulários principais.
- Validação básica de cadastro.
- Proteção Apache contra execução de scripts em `public/uploads`.
- `.env` fora do versionamento.
- Arquivos internos ficam fora da pasta pública e possuem proteção contra acesso direto.

## Módulos planejados

- Cursos, aulas, módulos, materiais e progresso.
- Turmas, disciplinas, vínculos e calendário.
- Atividades, entregas, correções, notas e feedback.
- Simulados, banco de questões, tempo limite e correção automática futura.
- Biblioteca digital com materiais públicos, privados e favoritos.
- Eventos com certificados.
- Gamificação com XP, níveis, conquistas, badges, ranking e moedas internas.
- Certificados com código único, validação pública e QR Code futuro.
- Comunidade acadêmica com posts, comentários, projetos, portfólio e moderação.
- Chat por turma, grupos e mensagens privadas futuras.
- Financeiro com planos, assinaturas, mensalidades, histórico e marketplace 20%/80%.
- IA futura para tutor inteligente, correções, resumos, quizzes, recomendação, desempenho e plágio.

## Versionamento e auto-sync GitHub

O projeto está preparado para sincronização automática com o repositório:

- Remote: `https://github.com/NstiTheo/tme-platform.git`
- Branch de trabalho: `dev`
- Auto commit: ativado pelo script `tools/git-auto-sync.ps1`
- Auto push: ativado para `origin/dev`

Configurar o Git local, validar o projeto e iniciar o monitor em segundo plano:

```powershell
.\tools\setup-git-auto-sync.ps1 -StartWatcher
```

Executar uma sincronização única, útil antes de fechar o editor:

```powershell
.\tools\git-auto-sync.ps1 -Once
```

Manter o monitor aberto no terminal atual:

```powershell
.\tools\git-auto-sync.ps1
```

Antes de cada commit/push automático, a automação:

- valida sintaxe PHP com o PHP CLI do XAMPP;
- valida JavaScript com `node --check`, quando Node.js estiver instalado;
- bloqueia marcadores de conflito Git;
- verifica a estrutura MVC principal;
- confirma que `.env` continua fora do versionamento;
- cria backup local de arquivos críticos alterados em `.automation/backups/`;
- grava logs em `.automation/logs/git-auto-sync.log`;
- não usa `force push` e não sobrescreve remote já configurado com outra URL.

Arquivos estruturais novos ou mudanças de arquitetura devem vir acompanhados de atualização no README.
