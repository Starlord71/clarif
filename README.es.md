# Clarif

**Read this in English: [README.md](README.md)**

Clarif ingiere reportes crudos [SARIF](https://docs.oasis-open.org/sarif/sarif/v2.1.0/sarif-v2.1.0.html)
(CodeQL, ESLint, Semgrep), normaliza cada hallazgo en un modelo único, los guarda, y compara dos
ejecuciones para mostrar qué es **nuevo**, qué se **resolvió** y qué **persiste**.

Es una herramienta de un solo usuario (sin autenticación), construida con Laravel 13 y PostgreSQL.

## Estado

La subida y el parsing de reportes SARIF a un modelo normalizado y consultable está implementado,
junto con la UI de consulta y los filtros de hallazgos por reporte. La comparación de dos ejecuciones
(hallazgos nuevos, resueltos y persistentes) también está implementada. El trabajo restante es la
distribución con Docker.

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
`docker-compose.yml`; eso llega en la Fase 6).

La primera vez (cuando el contenedor no existe todavía), créalo:

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

En las siguientes veces el contenedor ya existe, así que solo hay que arrancarlo (no uses de nuevo
`docker run --name clarif-postgres`, fallaría con `container name already in use`):

```sh
docker start clarif-postgres
```

Notas:

- El contenedor se expone en el puerto host **5434** (no en el 5432 por defecto) para no chocar con
  un servicio de PostgreSQL instalado localmente.
- Los datos se guardan en el volumen nombrado `clarif-postgres-data`, así que detener o eliminar el
  contenedor no los borra; arrancarlo de nuevo reutiliza la misma data.
- Gracias a `--restart unless-stopped`, el contenedor se inicia automáticamente cuando arranca
  Docker Desktop, por lo que normalmente no necesitas arrancarlo a mano. Usa
  `docker stop clarif-postgres` / `docker start clarif-postgres` solo después de detenerlo tú.

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
- El diffing agrupa hallazgos por un fingerprint de `rule_id | file_path | línea`. Si cambios no
  relacionados agregan o quitan líneas por encima de un hallazgo, su línea se desplaza y la huella
  deja de coincidir aunque el problema sea el mismo. Es el conocido **"line drift problem"**;
  herramientas como SonarQube lo resuelven con huellas basadas en el contexto de código que rodea al
  hallazgo, mientras que Clarif decide no abordarlo en v1. Es una limitación consciente, no un bug.

## Licencia

Publicado bajo la [licencia MIT](https://opensource.org/licenses/MIT).
