# Technical debt

Tracked 2026-11-04 (Phase 3). See `docs/REPOSITORY_AUDIT.md` for how each finding was verified and `projects/info/analysis/AC-Task-plan.md` for the phase this will actually be addressed in. **Nothing in this table is fixed** — Phase 3 is docs-only; "Status" below means "where this goes," not "done." Items marked **(static, unverified)** were found by reading source, not by running it (no Docker build/run happened this phase).

## `src/be` (Laravel API)

| # | Severity | Item | Where | Planned phase |
|---|---|---|---|---|
| 1 | **P0** | CVE-2026-114509 (Laravel TokenGuard auth-bypass) on locked `laravel/framework` 11.44.1 — unfixable within 11.x. Exploitability nuance: the app's `'api'` guard driver is `jwt`, not Laravel's `token` driver, and Sanctum is loaded but has no registered guard/route — reduces but does not eliminate priority (see `REPOSITORY_AUDIT.md` #1). | `composer.lock`, `config/auth.php` | Phase 6 (major bump to ≥12.68 or ≥13.29 closes it) |
| 2 | **P0** | 7 `secrets/db_*` files tracked in git — compromised credentials. Rotation + history removal already requested of Rasim. | `secrets/db_*` (git history) | **Approval Gate 1** (not Phase 4's normal branch cleanup) |
| 3 | **P0** | `UserSeeder.php` has two non-bracketed `namespace` statements — PHP fatal parse error expected, breaking `migrate --seed` and the documented setup path. **(static, unverified)** | `src/be/database/seeders/UserSeeder.php:1-5` | Phase 7 (fix) — confirm first with `php -l` in Docker (Phase 5) |
| 4 | **P1** | `TaskController::show()` passes `Task::class` instead of the fetched `$task` to `authorize('view', ...)`, unlike `update()`/`destroy()`. Expected to deny every call (403) regardless of ownership. **(static, unverified)** | `src/be/App/Http/Controllers/Tasks/TaskController.php` (`show()`) | Phase 7/9 (fix) + Phase 11 (regression test to confirm actual behavior before/after) |
| 5 | **P1** | `TaskRepository::findOrFail()` returns a string on a miss instead of throwing `ModelNotFoundException` (`Task\|string` return type) — breaks the documented 404 contract for `show`/`update`/`destroy` on a non-existent ID, likely surfacing as 403 via the same mechanism as #4. **(static, unverified)** | `src/be/App/Http/Controllers/Tasks/Repositories/TaskRepository.php::findOrFail` | Phase 7 (fix) + Phase 11 (test) |
| 6 | **P1** | `routes/api/check.php` has no auth middleware at all; `clear_cache` runs 6 artisan commands (`optimize:clear`, `config:cache`, `clear-compiled`, `cache:clear`, `route:cache`, `view:clear`) on an unauthenticated `GET` — cheap repeatable DoS/cache-thrash vector. `/check/ip` makes an unauthenticated outbound call to a third party on every hit. | `src/be/routes/api/check.php`, `App/Http/Controllers/Check/CheckController.php` | Phase 9 (hardening) |
| 7 | **P1** | All 3 Dockerfiles violate the project's Docker standard: no multi-stage build, no `test` stage, no `HEALTHCHECK`. `php.dockerfile` keeps the compiler toolchain (`build-essential` etc.) and unused `pdo_mysql`/`mysqli` extensions in the runtime image, and does `chmod -R 777` on `storage`/`bootstrap/cache`. `node.dockerfile` has no non-root `USER` and runs the Vite **dev server** (`npm run dev`) as its `CMD`. | `php.dockerfile`, `node.dockerfile` | Phase 5 (container baseline rebuild) |
| 8 | **P1** | `server/nginx/default.conf`'s `location /` uses `proxy_pass http://php:9000` (HTTP) against `php-fpm`, which only speaks FastCGI — likely a copy-paste bug; the `.php$` block below correctly uses `fastcgi_pass`. **(static, unverified — not started this phase)** | `server/nginx/default.conf` | Phase 5 (verify by actually running `make verify`; fix if confirmed) |
| 9 | **P1** | `bootstrap/app.php` registers the `'api'` middleware group twice — the first registration (`throttle:api` + stateful-Sanctum) is dead, silently overwritten by the second. `routes/api.php` separately re-applies `throttle:60,1` at the route-group level, duplicating rate-limiting via two mechanisms. | `src/be/bootstrap/app.php` | Phase 7 (dead-code cleanup) |
| 10 | **P1** | Backend test suite is the unmodified Laravel skeleton (`tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php`) — zero real coverage. `phpunit.xml`'s `DB_CONNECTION`/`DB_DATABASE` overrides are commented out, so a test run's actual DB target is unconfirmed/possibly a real Postgres instance rather than an isolated one. | `src/be/tests/`, `src/be/phpunit.xml` | Phase 5 (confirm DB isolation before baselining) + Phase 11 (real tests) |
| 11 | **P2** | No PHPStan/Psalm configured — only `laravel/pint` (a formatter) exists for `src/be`. | `src/be/composer.json` (`require-dev`) | Phase 9 (quality tooling) |
| 12 | **P2** | `src/be/package.json` (Vite/Tailwind asset build) has no committed lockfile — unreproducible, unaudited. | `src/be/package.json` | Phase 5/6 |
| 13 | **P2** | `config/cors.php`'s `allowed_origins` hardcodes one specific, session-scoped Google Project IDX cloud-workstation URL alongside localhost origins, instead of sourcing from env. | `src/be/config/cors.php` | Phase 9 |
| 14 | **P2** | Non-standard PSR-4 root casing: `App/` (capital A) instead of Laravel's conventional lowercase `app/`. Cosmetic, harmless functionally. | `src/be/composer.json`, directory layout | Phase 7 (align to upgraded-framework convention) |
| 15 | **P3** | `TransactionHelper`'s name suggests it opens a DB transaction; it only catches exceptions and maps them to HTTP responses. The real `DB::transaction()` calls live separately in `TaskService`. Naming/clarity debt, not a functional bug. | `src/be/App/Helpers/TransactionHelper.php` | Phase 8 (architecture pass, if renamed) |
| 16 | **P3** | `TaskRepository::getFilteredTasks()`'s `$includeTags` flag eager-loads the `user` relation, not anything related to `tags` (`tags` is a plain JSON column, not a relation) — flag name and effect don't match; looks like leftover/confused code. | `src/be/App/Http/Controllers/Tasks/Repositories/TaskRepository.php::getFilteredTasks` | Phase 7 |
| 17 | **P3** | `CheckController::clearCache()` passes `now()` (a Carbon instance) as the first argument to PHP's `date()`, which expects an integer timestamp — relies on loose coercion, fragile. | `src/be/App/Http/Controllers/Check/CheckController.php::clearCache` | Phase 7 |
| 18 | **P3** | No backup/recovery story for Postgres data beyond a local bind-mount (`./db/postgres`, gitignored) — no dump script, no documented restore procedure. | n/a (missing) | Phase 9 (document at minimum; automate if Rasim wants it productionized) |

## `src/fe` (React SPA)

| # | Severity | Item | Where | Planned phase |
|---|---|---|---|---|
| 19 | **P0** | Axios request interceptor logs the full request config — including the already-attached `Authorization: Bearer <JWT>` header — to the browser console on every API call. Token also persisted in `localStorage` (no httpOnly cookie). | `src/fe/src/shared/lib/api.ts` | Phase 9 (hardening) |
| 20 | **P2** | `react` `^19.1.0` vs `react-dom` `^18.3.1` — major-version mismatch within the same package.json (carried from Phase 2, re-confirmed). | `src/fe/package.json` | Phase 6 |
| 21 | **P2** | No frontend test runner configured at all (no `vitest`/`jest`/testing-library in dependencies). | `src/fe/package.json` | Phase 11 |
| 22 | **P3** | `@types/recharts` is deprecated upstream (recharts ships its own types) — removal candidate, not an upgrade target. | `src/fe/package.json` | Phase 7 |

## Cross-cutting / repo-root

| # | Severity | Item | Where | Planned phase |
|---|---|---|---|---|
| 23 | **P2** | A sixth floating Docker image tag Phase 2's inventory didn't list: `composer:latest` (the `composer` compose service), beyond the five already flagged (`php:8.3-fpm`, `nginx:stable-alpine`, `postgres:latest`, `mongo:latest`, `node:current-alpine` vs `node:23-alpine`). | `docker-compose.yml` (`composer` service) | Phase 5 |
| 24 | **P2** | `mongodb` compose service is dead infrastructure — zero usage found anywhere in `src/be` or `src/fe`; also the only service not on the `AzeriCard` network and without a `restart:` policy. | `docker-compose.yml` | Phase 7 (remove, unless Rasim flags a planned future use) |
| 25 | **P1** | Plaintext `POSTGRES_PASSWORD`/`POSTGRES_USER`/`MONGO_INITDB_ROOT_USERNAME`/`MONGO_INITDB_ROOT_PASSWORD` set directly as compose environment values (local-dev-only credential, but the exact hardcoded-secret pattern `CLAUDE.md`'s Docker standard asks to avoid). A dormant, already-written `secrets:` block + `scripts/load-secrets.sh` exist but are commented out/unused. | `docker-compose.yml`, `scripts/load-secrets.sh` | Phase 9 |
| 26 | **P3** | Two overlapping README files: `README.md` (ad-hoc Docker/Artisan command notes, including a `DOCKER_BUILDKIT=0` workaround aside) and `ExampleREADME.md` (the actual business/API description — "Personal Task Manager"). Should be one real `README.md`. | repo root | Phase 15 (flagged now so it isn't lost) |
| 27 | **P3** | `AzeriCard.postman_collection.json` is a second, hand-maintained source of API-contract truth alongside the `@OA\...` annotations (l5-swagger). No `openapi.yaml` is committed as a static contract file. | repo root | Phase 9 (openapi.yaml) / Phase 15 |

## Not treated as debt

- The feature-folder structure in both `src/be/App/Http/Controllers/Tasks/` and `src/fe/src/features/` is a deliberate, reasonable organizational choice — not flagged for restructuring.
- `DB::transaction()` wrapping single-statement writes in `TaskService` is harmless (not a bug) even though it adds no atomicity benefit today — kept as-is, not debt.
- The Repository+Service+Interface split for `Task`/`Auth` is a real (if partial) step toward Hexagonal/Clean separation, not over-engineering for this domain's current size — see `docs/ARCHITECTURE.md`.
