# AzeriCard

A task management API: categories with subcategories, tasks with a priority, a
status and a repetition, and a user of the API who signs in with a token.

The repository holds two parts:

| Part | Path | What it is |
|---|---|---|
| The API | `src/be` | Laravel 12 on PHP 8.3, PostgreSQL, a token of Sanctum and a token of JWT |
| The frontend | `src/fe` | React with Vite, TypeScript, Tailwind and shadcn |

The API is described by the annotations of the controllers, and the
documentation of it is generated with `l5-swagger`:

- the names of the endpoints: <http://localhost:8000/api/documentation>
- the file of the documentation: `src/be/storage/api-docs/api-docs.json`

## Running it

```shell
mkdir -p var
cp .env.example var/.env
docker compose --env-file var/.env build
docker compose --env-file var/.env up -d
```

Then the migrations and the key of the application:

```shell
docker compose --env-file var/.env exec artisan migrate --force
docker compose --env-file var/.env exec artisan key:generate --show
```

The key has to be written into `var/.env` as `APP_KEY`, and the secret of the
token as `JWT_SECRET` (`openssl rand -base64 48` gives one).

Every command of the stack needs `--env-file var/.env`, because the variables
of the stack live in that file and not in the root of the project. The
password of the database has no default value: the stack refuses to start
until it is set.

Every bind mount of the stack lives under `var/`: the local environment file,
the data of the databases and the documents of the frontend. Named volumes are
not used, and every service builds from a Dockerfile of the repository, so no
service points at a registry image.

The application is reachable at `http://localhost:8000`, the database of the
API at `localhost:6432` and the database of the documents at `localhost:37017`.
`APP_PORT` in `var/.env` moves the application to another host port.

### The commands of the stack

```shell
docker compose --env-file var/.env exec artisan migrate --force
docker compose --env-file var/.env exec artisan l5-swagger:generate
docker compose --env-file var/.env run --rm composer install
docker compose --env-file var/.env run --rm --entrypoint npm node run build
```

## The API

| Method | Path | Auth | What it does |
|---|---|---|---|
| POST | `/api/v1/auth/register` | – | Creates a user and returns a token. |
| POST | `/api/v1/auth/login` | – | Returns a token. |
| POST | `/api/v1/auth/refresh` | token | Returns a new token. |
| POST | `/api/v1/auth/logout` | token | Invalidates the token. |
| GET | `/api/v1/auth/me` | token | The profile of the caller. |
| PUT | `/api/v1/auth/me` | token | Changes the profile of the caller. |
| GET | `/api/v1/tasks` | token | The tasks of the caller, a page at a time. |
| POST | `/api/v1/tasks` | token | Creates a task. |
| GET | `/api/v1/tasks/{id}` | token | One task. |
| PUT | `/api/v1/tasks/{id}` | token | Changes a task. |
| DELETE | `/api/v1/tasks/{id}` | token | Removes a task. |
| GET | `/api/v1/tasks/report` | token | The numbers of the tasks of the caller. |
| GET | `/api/v1/check/...` | – | The state of the databases and of the cache. |

The answer of the API holds the payload under the key `result` and the message
of a failure under the key `error`:

```json
{
  "timestamp": "2026-01-01T12:00:00+00:00",
  "path": "api/v1/tasks",
  "method": "GET",
  "error": null,
  "result": { }
}
```

A request that does not pass the validation is answered with the status 422, a
request without a token with the status 401, a request that the policy of the
model refuses with the status 403 and a request for something that is not
there with the status 404.

## Tests

Both gates run inside the project's own Docker images. Nothing has to be
installed on the machine.

```shell
docker compose --env-file var/.env --profile test run --rm test
docker compose --env-file var/.env --profile test run --rm --entrypoint ./vendor/bin/pint test --test
docker compose --env-file var/.env --profile test run --rm --entrypoint composer test audit --no-interaction --abandoned=report
```

The suite of the API runs against a database in memory, so it needs neither a
database nor a network. It covers the authentication, the profile, the tasks and
the answers of the refusals.

The linter is Laravel Pint. The rules are the ones of Laravel, and the
directories of the runtime state are excluded in `pint.json`.

The frontend has its own gates, which also run in a container:

```shell
docker compose --env-file var/.env run --rm --entrypoint npm node run lint
docker compose --env-file var/.env run --rm --entrypoint npm node run build
```

## CI/CD

`.github/workflows/ci.yml` runs the gates on every push and every pull
request, in two jobs:

| Job | Step | Gate |
|---|---|---|
| backend | `docker compose build test` | ✅ |
| backend | `docker compose --profile test run --rm test` | ✅ |
| backend | `pint --test` | ✅ |
| backend | `composer audit` | ✅ |
| frontend | `docker compose build node` | ✅ |
| frontend | `npm run build` | ✅ |
| frontend | `npm run lint` | ✅ |

The runner needs a Docker daemon and nothing else — no PHP and no Node.js
toolchain is installed on it. The workflow reads the repository only, never
prints a secret and never publishes anything, and `actions/checkout` is
pinned to a commit hash so the tag cannot move under the pipeline.

## The stack

| Part | Choice |
|---|---|
| Language | PHP 8.3, the image runs 8.3 |
| Framework | Laravel 11.57 |
| Database | PostgreSQL 16 and MongoDB 7 |
| Authentication | Sanctum for the session of the frontend, JWT for the token of the API |
| Documentation | l5-swagger 8.6, the annotations of the controllers |
| Tests | PHPUnit 11, Laravel Pint as the linter |
| Frontend | React 18, Vite 5, TypeScript, Tailwind, shadcn |

The base images are pinned by digest, so a rebuild of the project uses the
same runtime as the last one.

## Security notes

- **The credentials of the machine are not part of the repository.** The
  directory `secrets/` held the password of the database in plain text, and
  the template of the environment held the key of the application and the
  secret of the token. All of them are gone; the values are read from the
  environment.
- **The values that were in the history are compromised.** The password of the
  database, the key of the application and the secret of the token were
  committed before, and they are still in the history of the repository. They
  have to be changed, and the history has to be rewritten to remove them, which
  is a decision for the owner of the repository.
- **The writes ask for a token.** The reading of the catalogue is open, the
  writing is not.
- **The answer of the API never carries the path of a file** and never the
  details of an error, unless the application runs with the debug turned on.
- **The four advisories of the line of Laravel 11 that are still open** are
  listed in the manifest of the backend, each with a note in the record of the
  project. The version 12 of the framework has none of them, but it needs the
  version 11 of l5-swagger, and that version brought the version 6 of
  swagger-php with it, which has no analyser for the annotations of the
  docblocks any more. Every annotation of the project would have to become an
  attribute of PHP, which is a change of the whole documentation of the API.

## Known limitations

- **The `posts` of the course are not implemented.** The API knows the tasks,
  the categories and the check of the databases.
- **The database of the documents (MongoDB) is in the stack but unused.** The
  check endpoint answers for it.
- **The listing of the tasks has no search.** The tasks come in the order of
  their date, a page at a time.
- **The default branch of the repository is still `develop`.** The work is
  delivered into `master` and `test`; `develop` carries the open pull
  requests of the tools of the dependencies and is retired when they are
  merged.
- **No license file.**
