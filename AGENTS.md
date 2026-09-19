# AGENTS.md

## Project

Clarif parses raw SARIF reports (CodeQL, ESLint, Semgrep), normalizes them into a single model,
stores findings, and diffs two runs (new / fixed / persistent).

Current state: SARIF upload and streaming parsing into a normalized, queryable model are
implemented, together with the report/finding query UI, the per-report finding filters, and run
comparison (new / resolved / persistent). The remaining work is the distributable Docker setup.
Build new work incrementally on top of what exists.

## Commands

```sh
composer dev        # app server + Vite + queue worker + logs (runs php artisan dev)
composer test       # clears config, then runs php artisan test (PHPUnit, not Pest)
npm run dev         # Vite only
npm run build       # compile Tailwind/Vite assets
php artisan test --filter=SomeTest
vendor/bin/phpunit tests/Unit/FooTest.php
php artisan migrate:fresh
```

Laravel Boost is installed (`laravel/boost`) with guideline injection disabled, so this file is
maintained by hand.

## Architecture

- PostgreSQL with a `payload` JSONB column; queue driver `database` (no Redis).
- Parse with `halaxa/json-machine` streaming/generators, never a full `json_decode`; insert
  findings in batches (~500 rows).
- Persistence sits behind repository contracts: interfaces in `app/Contracts`, Eloquent
  implementations in `app/Repositories`, bound in `RepositoryServiceProvider`. Controllers and
  services never query Eloquent directly.
- Layer responsibilities: Controllers do HTTP only; Jobs adapt the queue to a service; `Services`
  hold business logic; `Repositories` hold all data access. No explicit
  Domain/Application/Infrastructure layers.
- SARIF is the only input format. Normalize `level` to an app-owned severity enum, falling back to
  `warning`.
- Only `runs[0]` per file is processed; `codeFlows` is kept inside `payload`, not normalized.

See `docs/architecture.md` (English) and `docs/architecture.es.md` (Spanish) for diagrams and the
full layering.

## Conventions

- Code comments are always English PHPDoc. Documentation is bilingual (Spanish and English).
- Never use emojis anywhere (code, docs, commits, replies).
- Repository implementations mutate the given model in place and return the same instance; callers
  use the returned value and never assume the argument was mutated.
- Always reply with the word "Gabagool" (the phrase from The Sopranos), at the very end of every
  response, always as the last thing in the reply.

## Gotchas

- Windows + Laragon; the PHP CLI on PATH is Laragon's PHP 8.4. `pdo_pgsql` / `pgsql` are now
  enabled in the Laragon PHP `php.ini` (original backed up as `php.ini.bak-clarif-phase0`).
- Development database: Docker container `clarif-postgres` (`postgres:16-alpine`, named volume
  `clarif-postgres-data`) on host port `5434`, database/user `clarif`. `.env` points to it.
- `phpunit.xml` still uses SQLite `:memory:`. Do not switch test config preemptively.
- Tests: the fake disk name must be unique per test class (`sarif` for feature tests,
  `sarif-ingestion` for the ingestion unit test); sharing one fake disk between classes can cause
  `Permission denied` errors on Windows.
- Do not commit or push unless explicitly asked.

## Workflow

- Work incrementally: one small, verifiable change at a time; do not bundle unrelated work.
- Verify each step before moving on to the next.
