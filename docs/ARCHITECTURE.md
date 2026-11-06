# Architecture

Analyzed 2026-11-04 (Phase 3, auditor). Builds on Phase 2's `projects/info/deps/AC-Task.md` (dependency inventory) — not repeated here except where directly relevant. Static code reading only: no container was built or run in this phase (Phase 3 is docs-only per `CLAUDE.md`/`PROMPT.md`).

## What this is

A full-stack personal/team task manager, branded "AzeriCard" in the Docker network/compose naming and "Personal Task Manager" in `ExampleREADME.md` (the file that actually documents the app — the top-level `README.md` is a grab-bag of ad-hoc Docker/Artisan command notes, see `docs/REPOSITORY_AUDIT.md`). A user registers, logs in with JWT, and manages their own tasks (title, description, due date, status, priority, repeat schedule, tags) with a reporting/analytics endpoint. It is a monorepo with two independently-managed sub-projects under one Docker Compose stack:

- **`src/be`** — PHP 8.3 / Laravel 11 REST API (JWT auth, Postgres).
- **`src/fe`** — TypeScript / React 19 SPA (Vite, Tailwind, Radix UI, TanStack Query, Zustand, react-hook-form + zod).

They are not npm/composer-linked to each other in any way (confirmed in Phase 2 — no cross-package dependency) — the only coupling is the REST API contract described below, consumed over HTTP.

## `src/be` — backend

### Request flow

`nginx` (container `nginx`, built from `nginx.dockerfile` + `server/nginx/default.conf`) is the only public entry point (ports `8000`/`8443` in `docker-compose.yml`), fronting `php` (container `php`, `php:8.3-fpm`). Laravel's own router (`bootstrap/app.php`) wires `routes/api.php`, which applies `throttle:60,1`, prefixes everything `v1`, and includes three route files:

- `routes/api/check.php` — `/check/{db,health,static,ip,clear_cache}`, **no auth middleware on any of them** (see `docs/REPOSITORY_AUDIT.md`).
- `routes/api/auth.php` — `/auth/{register,login}` (public), `/auth/{me,updateMe,refresh,logout}` (behind `auth:api`).
- `routes/api/tasks.php` — `/tasks` CRUD + `/tasks/reports`, all behind `auth:api`.

`auth:api` resolves to `config/auth.php`'s `'api'` guard, whose `driver` is `'jwt'` — i.e. `php-open-source-saver/jwt-auth`'s guard, not Laravel's Sanctum or its built-in `token` guard. `laravel/sanctum` is installed and its stateful middleware (`EnsureFrontendRequestsAreStateful`) is loaded globally in `bootstrap/app.php`, but no `'sanctum'` entry exists in `config/auth.php`'s `guards` array and no route uses `auth:sanctum` — Sanctum is present but currently unused for authentication. This matters for how exploitable the Phase 2 critical CVE actually is here; see `docs/REPOSITORY_AUDIT.md`.

### Code organization — Controller-rooted, not framework-rooted

The PSR-4 root is `App/` (capital A, mapped in `composer.json`: `"App\\": "App/"`) — functionally fine, cosmetically non-standard (Laravel's convention is lowercase `app/`). Inside it, the **Tasks** feature is organized as a self-contained vertical slice under `App/Http/Controllers/Tasks/`, not Laravel's default flat `app/Models`, `app/Http/Requests`, etc.:

```
App/Http/Controllers/Tasks/
├── TaskController.php
├── DTOs/TaskDTO.php            # readonly DTO, builds + validates from request array
├── Enums/{TaskStatus,TaskPriority,TaskRepeatTypeEnum}.php
├── Filters/TaskFilters.php     # extends App/Filters/ApiFilter — generic query-param -> SQL filter DSL
├── Models/Task.php
├── Policies/TaskPolicy.php
├── Repositories/{TaskRepository,TaskRepositoryInterface}.php
├── Requests/{Store,Update}TaskRequest.php
├── Resources/{TaskCollection,TaskResource}.php
├── Services/{TaskService,TaskServiceInterface}.php
└── Traits/{TaskPaginationLinksTrait,TaskRequestTrait}.php
```

`Auth` and `Users` follow the same Controller→Services/Repositories/Resources/Requests shape (`App/Http/Controllers/Auth/`, `App/Http/Controllers/Users/`). This is a light Repository + Service layering (interfaces for both `TaskRepository` and `TaskService`, bound presumably in a service provider — not independently confirmed which provider does the binding) — a real, if partial, step toward Hexagonal/Clean separation: controllers depend on `TaskServiceInterface`/service depends on `TaskRepositoryInterface`, not concrete classes. There is no separate domain layer (entities/value objects distinct from Eloquent models) and no DDD bounded-context split beyond "Tasks" vs "Auth" vs "Users" as folders — see `docs/adr/` candidates in `IMPROVEMENT_ROADMAP.md` for whether deeper DDD is warranted (short answer: the domain is simple CRUD + reporting, so probably not, per CLAUDE.md's KISS/YAGNI-wins rule).

Every controller action is wrapped in `App\Helpers\TransactionHelper::handleWithTransaction()`, which — despite the name — does **not** open a database transaction; it is a `try/catch` that maps `AuthorizationException` → 403, `ModelNotFoundException`/`NotFoundHttpException` → 404, anything else → 500, and wraps the return value in `SuccessApiResponse`. The actual DB transactions are opened separately and only in `TaskService::createTask/updateTask/deleteTask` via `DB::transaction()`. The naming overlap between this helper and real DB transactions is a documentation/clarity debt, not a functional one — see `docs/TECHNICAL_DEBT.md`.

### Database layer

Five migrations, in order: the default Laravel `users`/`password_reset_tokens`/`sessions` tables, `cache`, `jobs` (queue table — present but `QUEUE_CONNECTION` is not confirmed to run a worker process anywhere in `docker-compose.yml`; no `queue:work` entrypoint exists among the compose services), `personal_access_tokens` (Sanctum's table, created but unused per the guard analysis above), `tasks`, then a later migration adding `priority`/`repeat_type`/`repeat_end_date`/`tags` to `tasks`.

Schema as it stands:

- `users` — `id`, `name`, `email` (unique), `password` (hashed cast), `remember_token`, timestamps. One `HasMany` to `tasks` (`User::tasks()`).
- `tasks` — `id`, `user_id` (FK → `users.id`, `onDelete('cascade')`), `title`, `description`, `due_date`, `status` (enum, default `pending`), `priority` (enum, default `medium`), `repeat_type` (nullable enum), `repeat_end_date` (nullable date), `tags` (nullable JSON), timestamps. One `BelongsTo` back to `users` (`Task::user()`).
- Indexes: `fulltext(['title','description'])` (added in the first `tasks` migration, for search), `index(['user_id','status'])` and `index(['priority','status'])` (added in the second migration) — both composite indexes line up with the actual query shapes in `TaskRepository` (`where(['user_id' => ..., 'status' => ...])`, `TaskFilters`'s `status`/`priority` filters), i.e. these aren't speculative indexes, they match real access patterns.
- `personal_access_tokens` — Sanctum's standard polymorphic-token table (`morphs('tokenable')`), present but currently unconsumed (see guard analysis above).
- No soft deletes anywhere (`deleted_at` absent from every table) — `TaskRepository::delete()` is a hard delete.

**Transactions:** `TaskService`'s three mutating methods each wrap their single repository call in its own `DB::transaction()`. Each of those repository calls is itself a single Eloquent statement (`create`, `update`, `delete`) — a transaction around one statement is a no-op for atomicity (Postgres already makes a single statement atomic) but is harmless and future-proofs against the method growing a second write later. No transaction spans multiple business steps today (there is no multi-step write path in this codebase yet).

**N+1 / query shape:** `TaskRepository::getFilteredTasks()` paginates with `->paginate($perPage)` (capped at `$maxPerPage = 100` server-side, good) and conditionally does `$query->with(['user'])` when the caller passes `include_tags=true` — the flag name and the eager-loaded relation don't match (`tags` is a plain JSON column on `tasks`, not a relation at all; eager-loading `user` doesn't relate to tags). This looks like leftover/confused code rather than a real N+1 risk (there's no per-row relation access in `TaskResource` that would need it either way) — tracked in `docs/TECHNICAL_DEBT.md`, not fixed here. `getTasksReport()` runs four independent aggregate queries (`status`/`priority`/`repeat_type` group-by counts + a total count) via `$query->clone()` on a shared base query — reasonable, no N+1, though four separate round-trips could be one query with conditional aggregation if report latency ever becomes a measured problem (YAGNI for now — no evidence this is slow).

**Backup/recovery:** none found. `pgsql`'s data directory is bind-mounted to `./db/postgres` (gitignored) for local persistence only — no dump/backup script, no `pg_dump` cron, no documented restore procedure anywhere in the repo (`scripts/` contains only `load-secrets.sh`). This is a real gap for anything beyond a local dev box; flagged in `docs/TECHNICAL_DEBT.md` for Phase 9.

**MongoDB:** `docker-compose.yml` defines a `mongodb` service (`mongo:latest`), but grepping all of `src/be` and `src/fe` for any Mongo client/driver/connection-string usage returns zero hits. It is also the only service not attached to the `AzeriCard` compose network and has no `restart:` policy — consistent with being leftover infrastructure that was never wired into the application. Treated as dead weight, not part of the "real" database layer; see `docs/TECHNICAL_DEBT.md` and the Phase 7 plan.

## `src/fe` — frontend

Vite + React 19 + TypeScript, organized as a **feature-folder** structure under `src/`:

```
src/features/{auth,dashboard,tasks}/{api,components,pages,store,types,...}
src/shared/{components,hooks,lib,types,utils}
```

Each feature owns its own `api/` (axios calls), `store/` (Zustand), `types/`, and UI; `shared/` holds the cross-feature axios instance, UI primitives (wrapping Radix), and route guards. This is a reasonable, fairly standard modern React layout — no architectural red flags found in the structure itself.

**API client** (`src/shared/lib/api.ts`): one shared axios instance, base URL from `config.apiUrl` (env-driven, not hardcoded — good). A request interceptor attaches `Authorization: Bearer <token>` from `localStorage`. A response interceptor retries once on `401` by calling `/auth/refresh` and swapping the stored token, redirecting to `/login` if the refresh itself fails — this is a reasonable token-refresh pattern, structurally. Two things in the same file are flagged in `docs/REPOSITORY_AUDIT.md`/`docs/TECHNICAL_DEBT.md`: the token lives in `localStorage` (no httpOnly cookie option used), and every request is `console.log`'d including its already-attached `Authorization` header.

**State/data:** TanStack Query (`@tanstack/react-query`) for server state, Zustand for client/auth state, `react-hook-form` + `zod` for form validation — a conventional, non-redundant split (no second global-state library competing with Query for the same data).

## How the two sides connect

SPA (served by Vite dev server on `:5173`/`:3000` depending on which compose entry is used, or a static `build` in a real deployment — no production static-serving story is wired into `nginx.dockerfile` today, see `docs/TECHNICAL_DEBT.md`) talks to the API at whatever `VITE_*`-style env var `config.apiUrl` resolves to, expected to be `nginx`'s `:8000` → `/api/v1/*`. Auth is bearer-JWT only (no shared session/cookie auth despite Sanctum's stateful middleware being loaded) — the frontend is a pure API client, not a Sanctum-authenticated same-site SPA. `AzeriCard.postman_collection.json` at the repo root is a hand-maintained Postman collection mirroring these same endpoints — a second, manually-kept source of API-contract truth alongside the l5-swagger/OpenAPI annotations in the controllers (`@OA\...` blocks) — duplication flagged in `docs/TECHNICAL_DEBT.md`.

## Docker / service wiring

`docker-compose.yml` defines 9 services on one user network (`AzeriCard`), minus `mongodb` (not attached, see above): `nginx`, `php`, `composer` (one-shot `composer:latest`, for `composer install`), `artisan` (one-shot, entrypoint `php artisan`), `phpunit` (one-shot test runner), `pgsql`, `mongodb` (unused), `npmnx` (Vite dev server container, port `3000:5173`), `npm`/`npx` (one-shot Node tool runners, `node:current-alpine`). A commented-out `redis` service and a commented-out `secrets:` top-level block (referencing the tracked `secrets/db_*` files, see `docs/REPOSITORY_AUDIT.md`) are present but inert. `server/nginx/default.conf`'s `location /` block proxies to `http://php:9000` with `proxy_pass` (an HTTP directive) even though `php:9000` is `php-fpm` speaking FastCGI, not HTTP — a likely config bug, unverified at runtime this phase (no container was started). None of the three Dockerfiles are multi-stage, none has a `test` stage or `HEALTHCHECK`, and several base images float (`postgres:latest`, `mongo:latest`, `composer:latest`, `node:current-alpine` vs `node:23-alpine`, `php:8.3-fpm`, `nginx:stable-alpine`) — full detail in `docs/TECHNICAL_DEBT.md` and Phase 2's `deps/AC-Task.md`.

## Not present

- No CI/CD pipeline (`.github/workflows/` does not exist — confirmed Phase 2, reconfirmed here).
- No real backend test coverage beyond the unmodified Laravel skeleton (`tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php`) — zero tests for Auth or Tasks.
- No frontend test runner configured at all (no `vitest`/`jest`/testing-library in `package.json`).
- No static analysis for PHP (no PHPStan/Psalm; only `laravel/pint`, a formatter, is present).
- No queue worker service, despite a `jobs` table existing and `QUEUE_CONNECTION` being configurable.
- No caching layer wired up (the `redis` compose service is commented out; Laravel falls back to its database/array cache driver).
- No observability: no structured logging config beyond Laravel defaults, no metrics, no error tracking, no `/health/live`/`/health/ready` split (there's `/check/health`, unauthenticated, trivial — see `docs/REPOSITORY_AUDIT.md`).
- No `openapi.yaml`/`AsyncAPI` file in the repo — the OpenAPI spec exists only as `@OA\...` annotations inside controllers, generated on demand by l5-swagger, not committed as a static contract file.
