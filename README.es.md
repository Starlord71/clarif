# Clarif

**Read this in English: [README.md](README.md)**

Clarif ingiere reportes crudos [SARIF](https://docs.oasis-open.org/sarif/sarif/v2.1.0/sarif-v2.1.0.html)
(CodeQL, ESLint, Semgrep), normaliza cada hallazgo en un modelo único, los guarda, y compara dos
ejecuciones para mostrar qué es **nuevo**, qué se **resolvió** y qué **persiste**.

Es una herramienta de un solo usuario (sin autenticación), construida con Laravel 13 y PostgreSQL.

## Estado

El proyecto se construye por fases. La **Fase 0 (bootstrap) está completa**; el modelo de datos, la
ingesta, la UI y el diffing todavía no están implementados. Ver el [roadmap](#roadmap).

## Requisitos

- PHP 8.3+ (desarrollado con 8.4) con las extensiones `pdo_pgsql` y `pgsql` habilitadas.
- [Composer](https://getcomposer.org/).
- Node.js 20+ y npm.
- [Docker](https://www.docker.com/) para levantar la instancia de PostgreSQL de desarrollo.

## Primeros pasos (local)

```sh
# 1. Dependencias de PHP
composer install

# 2. Archivo de entorno
cp .env.example .env
php artisan key:generate
```

Apuntá la configuración de base de datos en `.env` al contenedor de desarrollo (ver
[Base de datos de desarrollo](#base-de-datos-de-desarrollo-docker)):

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5434
DB_DATABASE=clarif
DB_USERNAME=clarif
DB_PASSWORD=secret

QUEUE_CONNECTION=database
```

Luego corré las migraciones y compilá los assets del frontend:

```sh
php artisan migrate

npm install
npm run build
```

Levantá la app, Vite y el worker de cola juntos:

```sh
composer dev
```

## Base de datos de desarrollo (Docker)

La instancia local de PostgreSQL corre como un único contenedor Docker (todavía sin
`docker-compose.yml`; eso llega en la Fase 6):

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

Notas:

- El contenedor se expone en el puerto host **5434** (no en el 5432 por defecto) para no chocar con
  un servicio de PostgreSQL instalado localmente.
- Los datos se guardan en el volumen nombrado `clarif-postgres-data`.
- Para detenerlo / arrancarlo: `docker stop clarif-postgres` y `docker start clarif-postgres`.

## Colas

Los jobs en background usan el driver de cola `database` (sin Redis). Las tablas `jobs`,
`job_batches` y `failed_jobs` vienen con las migraciones por defecto, y `QUEUE_CONNECTION=database`
está seteado en `.env`. Para procesar jobs:

```sh
php artisan queue:work
```

## Comandos frecuentes

| Comando | Descripción |
| --- | --- |
| `composer install` | Instala las dependencias de PHP. |
| `composer dev` | Levanta app, Vite, worker de cola y logs juntos. |
| `composer test` | Limpia config y corre la suite de PHPUnit. |
| `npm run dev` | Levanta Vite en modo watch. |
| `npm run build` | Compila los assets de Tailwind/Vite. |
| `php artisan migrate` | Corre las migraciones pendientes. |
| `php artisan migrate:fresh` | Borra todas las tablas y vuelve a migrar. |
| `php artisan queue:work` | Procesa los jobs de la cola. |
| `php artisan test` | Corre la suite de tests (PHPUnit). |
| `vendor/bin/phpunit` | Corre PHPUnit directamente. |

## Decisiones técnicas

- **Solo SARIF.** Es el único formato de entrada soportado.
- **Parsing en streaming.** Los reportes se leen con
  [`halaxa/json-machine`](https://github.com/halaxa/json-machine) usando generators, nunca un
  `json_decode` completo, y los hallazgos se insertan en lotes de alrededor de 500 filas.
- **PostgreSQL con JSONB.** El hallazgo SARIF crudo se guarda tal cual en una columna `payload`
  JSONB; `codeFlows` no se normaliza a tablas relacionales.
- **Cola en base de datos.** Driver `database` en lugar de Redis.
- **Enum de severidad.** El `level` de SARIF se normaliza a un enum de severidad propio, con
  fallback a `warning`.

## Alcance de v1

- Solo se procesa `runs[0]` de cada archivo SARIF (un run por archivo).
- `codeFlows` se conserva dentro de `payload` pero no se normaliza.
- El diffing agrupa hallazgos por un fingerprint de `rule_id | file_path | línea`. La conocida
  **"line drift problem"** (cuando el número de línea se corre al agregar o quitar código no
  relacionado por encima de un hallazgo) se documenta como una limitación consciente de v1, no como
  un bug.

## Roadmap

| Fase | Alcance | Estado |
| --- | --- | --- |
| 0 | Bootstrap: Boost, `halaxa/json-machine`, PostgreSQL, cola | Completa |
| 1 | Modelo de datos: `reports`, `findings`, enum de severidad, modelos, factories | Pendiente |
| 2 | Ingesta: endpoint de subida, job de parsing en streaming, batching | Pendiente |
| 3 | UI de consulta: listado, formulario de subida, tabla de findings con filtros | Pendiente |
| 4 | Diffing entre runs: nuevos / resueltos / persistentes | Pendiente |
| 5 | Tests y fixtures SARIF | Pendiente |
| 6 | Dockerización para distribución (`docker compose up --build`) | Pendiente |

## Licencia

Publicado bajo la [licencia MIT](https://opensource.org/licenses/MIT).
