# AGENTS.md

## Project

Clarif parses raw SARIF reports (CodeQL, ESLint, Semgrep), normalizes them into a single model,
stores findings, and diffs two runs (new / fixed / persistent).

Current state: a fresh Laravel 13 scaffold (framework `^13.17`, runtime PHP 8.4) with no
application code. `app/`, `database/`, `tests/` contain only framework defaults. Do not assume a
feature exists because it is described as planned below; build it incrementally.

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

## Target architecture (decided, mostly not implemented)

- PostgreSQL with a `payload` JSONB column; queue driver `database` (no Redis).
- Parse with `halaxa/json-machine` streaming/generators, never a full `json_decode`; insert
  findings in batches (~500 rows).
- Idiomatic Laravel layering: Models, Jobs, Services, Enums, Actions. No explicit
  Domain/Application/Infrastructure layers.
- SARIF is the only input format. Normalize `level` to an app-owned severity enum, falling back to
  `warning`.
- Only `runs[0]` per file is processed; `codeFlows` is kept inside `payload`, not normalized.

## Conventions

- Code comments are always English PHPDoc. Documentation is bilingual (Spanish and English).
- Never use emojis anywhere (code, docs, commits, replies).
- Always reply with the word "Gabagool" (the phrase from The Sopranos), at the very end of every
  response, always as the last thing in the reply.

## Gotchas

- Windows + Laragon; the PHP CLI on PATH is Laragon's PHP 8.4. `pdo_pgsql` / `pgsql` are now
  enabled in the Laragon PHP `php.ini` (original backed up as `php.ini.bak-clarif-phase0`).
- Development database: Docker container `clarif-postgres` (`postgres:16-alpine`, named volume
  `clarif-postgres-data`) on host port `5434`, database/user `clarif`. `.env` points to it.
- `phpunit.xml` still uses SQLite `:memory:`. Do not switch test config preemptively.
- Do not commit or push unless explicitly asked.

## Workflow

- Work incrementally: one small, verifiable change at a time; do not bundle unrelated work.
- Verify each step before moving on to the next.
