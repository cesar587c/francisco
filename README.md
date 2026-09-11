# Painel de Pesquisa (Laravel)

Sistema Laravel construído do zero, focado apenas no **dashboard** que exibe
os dados de uma pesquisa por WhatsApp: estatísticas, lista de respondentes e
relatório do espectro político. A coleta das respostas continua sendo feita
por um bot externo (Node.js/WhatsApp), que alimenta este sistema através da
API de ingestão. As duas perguntas da pesquisa são: "O que você mais te
incomoda em Águas Claras ou no DF?" (`resposta_1`) e "Você se considera de
esquerda, centro ou direita?" (`resposta_2`).

## Características

- **Acesso único**: não há cadastro de usuários. Um único administrador é
  criado/atualizado pelo `DatabaseSeeder` a partir de `ADMIN_EMAIL` /
  `ADMIN_PASSWORD` no `.env`.
- **Dashboard em Blade** (`resources/views/dashboard.blade.php`): visão
  geral, respostas com busca/exclusão e relatório — tudo em PHP + JS puro,
  sem build step.
- **API de ingestão** (`/api/ingest/*`), protegida pelo header
  `X-Ingest-Token`, usada pelo bot para criar respondentes, salvar respostas
  e atualizar o andamento da conversa.
- **Relatório sem IA** (`app/Services/SurveyReportService.php`): classifica
  o espectro político procurando as palavras "esquerda"/"direita"/"centro"
  na resposta 2 (a própria pergunta já pede autodeclaração) e monta um
  relatório com estatísticas, palavras mais citadas na resposta 1 e uma
  amostra de respostas. Não depende de nenhuma API externa.

## Setup

1. Instale as dependências:
   ```
   composer install
   ```
2. Copie `.env.example` para `.env` e configure:
   - `DB_*`: crie previamente o banco MySQL indicado em `DB_DATABASE`.
   - `ADMIN_EMAIL` / `ADMIN_PASSWORD`: única conta que poderá logar.
   - `INGEST_API_TOKEN`: precisa ser igual ao token configurado no bot.
3. Rode as migrations e o seeder:
   ```
   php artisan migrate --seed
   ```
4. Suba o servidor:
   ```
   php artisan serve
   ```
5. Acesse `/login` com as credenciais de `ADMIN_EMAIL` / `ADMIN_PASSWORD`.

Rodar `php artisan db:seed` novamente atualiza a senha do admin se você
alterar `ADMIN_PASSWORD` no `.env`.

## Rotas

| Método | Rota | Descrição |
|---|---|---|
| GET | `/login` | Login (acesso único) |
| GET | `/dashboard` | Painel (requer autenticação) |
| GET | `/dashboard-data` | JSON com estatísticas, respondentes e último relatório |
| POST | `/dashboard-data` | Gera um novo relatório do espectro político |
| DELETE | `/respondents/{id}` | Remove um respondente e suas respostas |
| POST | `/api/ingest/respondents` | Cria/atualiza respondente por telefone |
| POST | `/api/ingest/responses` | Salva uma resposta |
| PATCH | `/api/ingest/respondents/{phone}/state` | Atualiza passo/conclusão da conversa |

As três rotas de `/api/ingest/*` exigem o header
`X-Ingest-Token: <INGEST_API_TOKEN>`.
