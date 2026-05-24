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

## Funcionalidades da primeira entrega

- Home pública e páginas institucionais: Sobre, Cursos, Eventos, Biblioteca, Comunidade, Login e Cadastro.
- Cadastro de aluno ou professor com status inicial `pendente`.
- Login permitido apenas para contas `aprovado`.
- Perfil completo em `/perfil` com personalizacao, biografia, estatisticas, badges e alteracao de senha.
- Dashboards separados para aluno, professor, supervisor, administrador, secretaria e financeiro.
- Aprovação e recusa de contas por administrador ou supervisor.
- Tema claro/escuro e cor principal personalizável por usuário.
- Roles, permissões, instituições, cursos, turmas, atividades, comunidade, eventos, certificados, gamificação, financeiro, notificações e logs modelados no banco.
- Estrutura preparada para importações futuras do INEP e e-MEC.

## Experiencia autenticada e Portal TME

A vitrine publica e a area logada agora sao experiencias separadas:

- Visitantes continuam vendo Home, Sobre, Cursos, Eventos, Biblioteca, Comunidade, Login e Cadastro.
- Usuarios autenticados sao redirecionados para `/portal` depois do login aprovado.
- O seletor rapido de tema/cor saiu da navbar; tema, cor principal e preview ficam em `/perfil`.
- O Portal mostra XP, nivel, moedas, streak e badges recentes.
- A rota `/inicio` tambem abre o Portal TME.
- A Home publica (`/`) redireciona usuarios logados para o portal interno.
- O menu de usuarios logados oculta Login/Cadastro e prioriza Inicio, Dashboard, Cursos, Meus cursos, Biblioteca, Eventos, Comunidade, Configuracoes/Tema e Sair.
- Alunos e professores acessam o catalogo em `/aluno/cursos` e a area neutra `/meus-cursos`.
- Professores tambem podem estudar como alunos, mantendo acesso a matriculas e progresso.
- Administradores e supervisores veem atalhos de administracao, aprovacoes, cursos admin e matriculas.
- `/portal` e `/configuracoes` exigem autenticacao.

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
- Status de matrícula: `ativa`, `concluida` e `cancelada`.
- Registro de data de início, última atividade e conclusão.
- Progresso percentual calculado automaticamente pelas aulas publicadas concluídas.
- Marcação de aula como concluída com CSRF e validação de vínculo da matrícula.
- Mudança automática da matrícula para `concluida` ao atingir 100%.
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

- CRUD de atividades vinculadas a curso, modulo e aula, com base futura para turma/disciplina.
- Campos: titulo, descricao, tipo, pontuacao maxima, prazo, status, instrucoes e anexo opcional.
- Tipos: `texto`, `arquivo`, `quiz`, `tarefa_pratica` e `projeto`.
- Entrega textual e/ou arquivo pelo aluno matriculado.
- Bloqueio de envio apos prazo quando a atividade nao permite atraso.
- Entregas atrasadas marcadas automaticamente quando permitido.
- Status de entrega: `pendente`, `enviada`, `atrasada`, `corrigida` e `devolvida`.
- Correcao por professor/admin/supervisor com nota, feedback e status.
- Boletim simples por curso.
- Logs para criacao, envio, encerramento e correcao.
- Uploads em `public/uploads/activity-attachments` e `public/uploads/activity-submissions`.

## Biblioteca digital

A biblioteca publica/interna fica em `/biblioteca` e a gestao em `/admin/biblioteca`.
Ela e separada dos materiais de aulas, mas usa os mesmos padroes visuais, CSRF, PDO e logs.

Recursos disponiveis:

- CRUD administrativo de itens da biblioteca para administrador, supervisor e professor.
- Envio de materiais por aluno/professor em `/biblioteca/enviar`, sempre entrando como `pendente`.
- Campos: titulo, descricao, categoria, disciplina, tipo, visibilidade, autor, arquivo/link, capa e status.
- Tipos: PDF, livro, apostila, artigo, video, link, apresentacao, imagem e arquivo.
- Visibilidade: publica, somente logados, curso especifico e privada/admin.
- Busca por titulo, categoria, disciplina e tipo.
- Favoritos por usuario em `/biblioteca/favoritos`.
- Historico simples de acesso/leitura em `library_access_logs`.
- Moderacao por administrador/supervisor com aprovacao, recusa e arquivamento.
- Logs para criacao, aprovacao, recusa, visualizacao e favorito.
- Uploads em `public/uploads/library` e `public/uploads/library-covers`.

Para bancos existentes, aplique tambem:

```bash
mysql -u root -p < database/migrations/2026_05_23_activities_library_module.sql
```

## Certificados

Alunos e professores acessam `/certificados` para consultar certificados emitidos automaticamente. A validacao publica fica em `/certificados/validar`.

Recursos disponiveis:

- Emissao automatica quando uma matricula chega a 100% de progresso.
- Codigo unico no formato `TME-CUR-ANO-CODIGO`.
- Visualizacao HTML do certificado em `/certificados/ver/{codigo}`.
- Botao `Imprimir/Salvar PDF` usando o print do navegador.
- Validacao publica por codigo, marcando certificados revogados como invalidos.
- Area administrativa em `/admin/certificados` para listar, filtrar e revogar certificados com motivo.
- Estrutura preparada para QR Code futuro.
- Logs para emissao, visualizacao, validacao e revogacao.

## Gamificacao

A TME agora possui XP, niveis, moedas internas, streak, badges e ranking inicial.

Recursos disponiveis:

- Perfil de gamificacao para cada usuario aprovado.
- Regras centralizadas em `app/services/GamificationService.php`.
- XP por login inicial, matricula, aula concluida, curso concluido, atividade enviada, boa nota, favorito de biblioteca e certificado emitido.
- Badges iniciais: Primeiro Login, Primeiro Curso, Primeira Aula Concluida, Curso Finalizado, Explorador da Biblioteca e Aluno Dedicado.
- Ranking global e ranking filtrado por curso em `/ranking`.
- Portal e Perfil exibem XP, nivel, moedas, streak e conquistas recentes.
- Eventos de XP evitam duplicidade por acao/referencia e registram logs.

## Perfil e configuracoes

Preferencias e dados do usuario foram centralizados em `/perfil` e `/configuracoes`.

Recursos disponiveis:

- Informacoes do usuario, instituicao, area de interesse e biografia curta.
- Placeholder para foto de perfil futura.
- Tema claro/escuro e cor principal com preview antes de salvar.
- Estatisticas: XP, nivel, cursos matriculados, cursos concluidos, atividades entregues, certificados e badges recentes.
- Alteracao de senha com senha atual, confirmacao e `password_hash`.
- Logout e area reservada para sessoes futuras.

Para bancos existentes, aplique tambem:

```bash
mysql -u root -p < database/migrations/2026_05_23_certificates_gamification_profile.sql
```

## Comunidade academica

A comunidade fica em `/comunidade` para usuarios logados e a moderacao fica em `/admin/comunidade`.

Recursos disponiveis:

- Feed academico com posts aprovados e posts destacados.
- Criacao de posts dos tipos: duvida, artigo, projeto, material, conquista e aviso.
- Posts de alunos/professores entram como `pendente`; admin/supervisor pode aprovar, recusar, arquivar e destacar.
- Comentarios em posts aprovados.
- Curtir e salvar posts por usuario.
- Perfil do usuario exibe posts recentes e status de moderacao.
- Logs para criacao, aprovacao, recusa, comentario e curtida/salvo.

## Eventos

Eventos publicados aparecem em `/eventos`; a administracao fica em `/admin/eventos`.

Recursos disponiveis:

- Cadastro administrativo de eventos com titulo, descricao, tipo, data/hora, local/link, vagas, carga horaria, status e imagem opcional.
- Tipos: palestra, workshop, aula ao vivo, simulado, olimpiada e hackathon.
- Usuario logado pode se inscrever, com bloqueio de inscricao duplicada.
- Admin visualiza inscritos, confirma presenca e altera status do evento.
- Evento `encerrado`, com presenca confirmada e certificado habilitado, pode gerar certificado de participacao.
- Portal mostra eventos inscritos do usuario.
- Logs para criacao, inscricao, presenca e certificado.

## Turmas e disciplinas

A gestao fica em `/admin/turmas` e a visualizacao do aluno/professor fica em `/turmas`.

Recursos disponiveis:

- CRUD inicial de turmas com nome, descricao, instituicao, periodo e status.
- Cadastro de disciplinas com nome, descricao, area, carga horaria e status.
- Vinculo de alunos a turmas.
- Vinculo de professores a turmas e disciplinas.
- Detalhe da turma com disciplinas, alunos, professores e area preparada para materiais futuros.
- Estrutura preparada para calendario, frequencia e ranking por turma.
- Logs de criacao e vinculos.

Para bancos existentes, aplique tambem:

```bash
mysql -u root -p < database/migrations/2026_05_24_community_events_classes.sql
```

## Frequencia

Admin, supervisor e professor registram chamada em `/frequencia`; alunos e professores acompanham seu historico em `/minha-frequencia`.

Recursos disponiveis:

- Selecao de turma, disciplina e data.
- Marcacao por aluno como `presente`, `falta`, `atraso` ou `justificado`.
- Observacao individual por aluno.
- Relatorio em `/frequencia/relatorio` por turma, disciplina, aluno e periodo.
- Percentual de frequencia calculado automaticamente.
- Sem notificacao ou alerta para responsaveis.
- Logs de chamada e alteracoes relevantes.

## Simulados e provas

A gestao fica em `/admin/provas`; alunos e professores acessam as avaliacoes em `/provas`.

Recursos disponiveis:

- Criacao de provas com titulo, descricao, curso, turma, disciplina, tempo limite, periodo, tentativas, status e ranking opcional.
- Banco de questoes com objetivas e discursivas.
- Alternativas, resposta correta e pontuacao por questao.
- Tentativas com controle simples de tempo no navegador e validacao no envio.
- Respostas salvas por tentativa.
- Correcao automatica de objetivas.
- Discursivas ficam como `pendente_correcao` ate correcao manual.
- Resultado individual, desempenho por disciplina e ranking por prova quando habilitado.
- Logs de criacao, tentativa, envio e correcao.

## Chat interno

Usuarios aprovados acessam `/chat`; administradores e supervisores podem auditar conversas em `/admin/chat` para seguranca e moderacao.

Recursos disponiveis:

- Mensagens privadas entre usuarios aprovados.
- Grupos automaticos por turma para alunos e professores vinculados.
- Lista de conversas com indicador simples de nao lidas.
- Leitura e envio de mensagens com CSRF e controle por permissao.
- Atualizacao simples por refresh periodico quando o usuario nao esta digitando.
- Bloqueio de envio para usuarios pendentes ou recusados.
- Logs de envio, leitura/auditoria e moderacao.

Para bancos existentes, aplique tambem:

```bash
mysql -u root -p < database/migrations/2026_05_24_attendance_exams_chat.sql
```

## Financeiro e assinaturas

Planos publicos ficam em `/planos`, historico financeiro em `/financeiro` e a gestao administrativa em `/admin/planos`.

Recursos disponiveis:

- Planos gratuitos e premium com nome, descricao, preco, duracao, beneficios e status.
- Assinatura de plano por usuario logado, com transacao e assinatura persistidas.
- Status financeiros: `pendente`, `pago`, `cancelado`, `expirado` e `estornado`.
- Estrutura inicial para PIX/cartao via campos de gateway, referencia, vencimento e expiracao.
- Controle premium por plano e middleware `premium` para recursos futuros.
- Cursos podem ser marcados como `gratuito` ou `premium`; curso premium exige assinatura ativa.
- Historico financeiro do usuario e carteira creator preparada para monetizacao 80/20.
- Moedas internas seguem integradas ao perfil de gamificacao.
- Logs financeiros para criacao/edicao de planos e pedidos de assinatura.

## Notificacoes

A navbar autenticada possui icone de notificacoes com contador e dropdown. A central completa fica em `/notificacoes`.

Recursos disponiveis:

- Notificacoes persistidas no banco com tipo, prioridade, link de acao e leitura.
- Servico central em `app/services/NotificationService.php`.
- Marcar notificacao como lida/nao lida e marcar todas como lidas.
- Eventos notificados: matricula, curso concluido, certificado emitido, atividade corrigida, comentario em post, mensagem no chat, inscricao em evento, prova liberada e badge conquistada.
- Logs de envio em `logs` pela acao `notification.sent`.

## Analytics e dashboard avancado

Dashboards de aluno, professor e administrador agora exibem metricas reais e graficos com Chart.js. Administradores e supervisores tambem acessam `/analytics`.

Recursos disponiveis:

- Admin: usuarios ativos, matriculas, cursos populares, atividade da plataforma, crescimento, certificados e receita paga.
- Professor: alunos ativos, entregas pendentes, desempenho medio e progresso medio dos cursos.
- Aluno: progresso geral, frequencia, XP semanal, desempenho em provas e certificados.
- Filtro por periodo em `/analytics`.
- Fallback em canvas simples quando o CDN do Chart.js nao estiver disponivel.

Para bancos existentes, aplique tambem:

```bash
mysql -u root -p < database/migrations/2026_05_24_finance_notifications_analytics.sql
```

## Organizacao visual e CSS

A interface foi reorganizada para uma base visual de plataforma SaaS educacional premium, mantendo as classes usadas pelas views e sem alterar rotas ou permissoes.

Arquivos CSS principais:

- `assets/css/base.css`: variaveis globais, reset, tipografia, campos e tokens de tema.
- `assets/css/layout.css`: navbar, dropdowns, header, containers, rodape e mensagens.
- `assets/css/components.css`: botoes, cards, metricas, tabelas, formularios, badges, notificacoes e graficos.
- `assets/css/dashboard.css`: Portal, dashboards, perfil, ranking e paineis de dados.
- `assets/css/modules.css`: cursos, comunidade, financeiro, planos, biblioteca, eventos, provas, frequencia e chat.
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
- Middleware de autenticação e middleware por role.
- PDO configurado com exceptions, fetch associativo e prepared statements.
- CSRF token nos formulários principais.
- Validação básica de cadastro.
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
