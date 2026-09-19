# Clarif

**Leer en español: [README.es.md](README.es.md)**

Clarif ingests raw [SARIF](https://docs.oasis-open.org/sarif/sarif/v2.1.0/sarif-v2.1.0.html) reports
(CodeQL, ESLint, Semgrep), normalizes every finding into a single model, stores them, and diffs two
runs to surface what is **new**, **fixed**, and **persistent**.

It is a single-user tool (no authentication) built with Laravel 13 and PostgreSQL.

## Status

Uploading and parsing SARIF reports into a normalized, queryable model is implemented, together with
the query UI and the per-report finding filters. Diffing two runs (new, resolved, and persistent
findings) is implemented as well. A one-command Docker setup (app, PostgreSQL, and the queue worker)
is available for distribution.

## Requirements

- PHP 8.4+ (required by the locked Symfony 8.x dependencies) with the `pdo_pgsql` and `pgsql`
  extensions enabled.
- [Composer](https://getcomposer.org/).
- Node.js 20+ and npm.
- [Docker](https://www.docker.com/) with Compose to run the full stack or just the development
  PostgreSQL instance.

## Getting started (Docker, recommended)

With Docker running, a single command builds the images and starts the application, PostgreSQL, and
the queue worker:

```sh
docker compose up --build
```

Then open [http://localhost:8000](http://localhost:8000) and upload a SARIF file. No manual setup is
required: the containers wait for PostgreSQL, run the migrations, and the worker processes uploads
in the background.

- Configuration lives in the committed [`.env.docker`](.env.docker) file. It holds **demo/local
  values** (including a non-secret `APP_KEY` and database credentials) and must not be reused for a
  real production deployment.
- `APP_DEBUG=false` is set on purpose, so error pages never leak stack traces.
- Data is persisted in named volumes: `postgres-data` (database), `app-storage` (uploaded SARIF
  files, shared by the app and the worker), and `app-logs` (application and `sarif` logs). Stopping
  the stack with `docker compose down` keeps them; `docker compose up` brings everything back.
- To wipe all data, run `docker compose down -v`.

## Getting started (local)

```sh
# 1. PHP dependencies
composer install

# 2. Environment file
cp .env.example .env
php artisan key:generate
```

Point the database settings in `.env` at the development container (see
[Development database](#development-database-docker)):

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5434
DB_DATABASE=clarif
DB_USERNAME=clarif
DB_PASSWORD=secret

QUEUE_CONNECTION=database
```

Then run the migrations and build the frontend assets:

```sh
php artisan migrate

npm install
npm run build
```

Start the app, Vite, and the queue worker together:

```sh
composer dev
```

## Development database (single container)

For local development against the source (without the full Docker stack), the PostgreSQL instance
runs as a single Docker container. When using `docker compose up --build`, this is not needed: the
`postgres` service of the compose file provides the database.

The first time (the container does not exist yet), create it:

```sh
docker run -d --name clarif-postgres \
  -e POSTGRES_DB=clarif \
  -e POSTGRES_USER=clarif \
  -e POSTGRES_PASSWORD=secret \
  -p 5434:5432 \
  -v clarif-postgres-data:/var/lib/postgresql/data \
  --restart unless-stopped \
  postgres:16-alpine
```

On later runs the container already exists, so just start it (do **not** use `docker run --name
clarif-postgres` again, it would fail with `container name already in use`):

```sh
docker start clarif-postgres
```

Notes:

- The container is exposed on host port **5434** (not the default 5432) to avoid clashing with a
  locally installed PostgreSQL service.
- Data is kept in the named volume `clarif-postgres-data`, so stopping or removing the container
  does not delete it; starting it again reuses the same data.
- Thanks to `--restart unless-stopped`, the container starts automatically when Docker Desktop
  starts, so you usually do not need to start it manually. Use
  `docker stop clarif-postgres` / `docker start clarif-postgres` only after stopping it yourself.

## Queues

Background jobs use the `database` queue driver (no Redis). The `jobs`, `job_batches`, and
`failed_jobs` tables ship with the default migrations, and `QUEUE_CONNECTION=database` is set in
`.env`. To process jobs:

```sh
php artisan queue:work
```

## Common commands

| Command | Description |
| --- | --- |
| `docker compose up --build` | Build and start the app, PostgreSQL, and the queue worker. |
| `docker compose down` | Stop the stack (keeps the data volumes). |
| `docker compose down -v` | Stop the stack and delete the data volumes. |
| `composer install` | Install PHP dependencies. |
| `composer dev` | Run the app server, Vite, the queue worker, and logs together. |
| `composer test` | Clear config and run the PHPUnit test suite. |
| `npm run dev` | Start Vite in watch mode. |
| `npm run build` | Compile Tailwind/Vite assets. |
| `php artisan migrate` | Run pending migrations. |
| `php artisan migrate:fresh` | Drop all tables and re-run migrations. |
| `php artisan queue:work` | Process queued jobs. |
| `php artisan test` | Run the test suite (PHPUnit). |
| `vendor/bin/phpunit` | Run PHPUnit directly. |

## Technical decisions

- **SARIF only.** It is the single supported input format.
- **Streaming parsing.** Reports are read with
  [`halaxa/json-machine`](https://github.com/halaxa/json-machine) using generators, never a full
  `json_decode`, and findings are inserted in batches of about 500 rows.
- **PostgreSQL with JSONB.** The raw SARIF finding is stored as-is in a `payload` JSONB column;
  `codeFlows` is not normalized into relational tables.
- **Database queue.** `database` driver instead of Redis.
- **Severity labels.** SARIF `level` is normalized to an app-owned enum and shown as a clearer,
  business-friendly label (High/Medium/Low/Info), falling back to `warning` when nothing is declared.
- **Repository pattern.** Controllers and services depend on repository contracts (`app/Contracts`)
  implemented by Eloquent repositories (`app/Repositories`); all query building lives there and the
  queue jobs stay thin. See [docs/architecture.md](docs/architecture.md) for the layering and
  diagrams.

## Supported tools and severity levels

Clarif accepts any valid SARIF 2.1.0 document, so it is not tied to a specific producer. It is designed
and tested around **CodeQL, ESLint, Semgrep and OWASP ZAP**.

SARIF only defines four abstract levels. Clarif stores the original value (keeping it traceable and
available on hover) but displays a clearer label:

| SARIF level | Clarif label | Meaning |
| --- | --- | --- |
| `error` | High | The tool flagged a definite problem. |
| `warning` | Medium | A potential problem worth reviewing. |
| `note` | Low | An informational observation. |
| `none` | Info | The tool did not assign a level. |

The label is derived from the SARIF level only. Finer numeric risk scores, such as CodeQL's
`properties.security-severity`, are **not** interpreted in v1. See
[docs/scope.md](docs/scope.md) for the full scope and limitations.

## v1 scope

See [docs/scope.md](docs/scope.md) for the complete scope and [docs/diffing.md](docs/diffing.md) for
the comparison details. In short:

- Only `runs[0]` of each SARIF file is processed (one run per file).
- `codeFlows` is preserved inside `payload` but not normalized.
- Diffing groups findings by a fingerprint of `rule_id | file_path | line`. If unrelated changes add
  or remove lines above a finding, its line shifts and the fingerprint stops matching even though the
  issue is the same. This is the known **"line drift problem"**; tools such as SonarQube solve it with
  fingerprints based on the surrounding code context, while Clarif deliberately does not address it
  in v1. It is a conscious limitation, not a bug.

## License

Released under the [MIT license](https://opensource.org/licenses/MIT).
