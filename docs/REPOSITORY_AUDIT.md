# Repository audit

Performed 2026-11-04 (Phase 3, auditor). Backup already exists: `projects/info/backups/AC-Task.git` (`git clone --mirror`, Phase 1). Working clone: `projects/repos/AC-Task/` (branch `develop`).

## How each finding below was verified

This phase is **docs-only** — no code was changed, no container was built or started, no git ref was created/merged/deleted. Verification here means: reading the actual tracked source (`App/`, `routes/`, `database/migrations/`, `config/`, Dockerfiles, `docker-compose.yml`, `package.json` files) in the working tree, plus `git log`/`git branch` for history and branch-state facts. Where a finding is a *prediction of runtime behavior from reading PHP/JS source* rather than something actually executed, it is marked **(static, unverified)** below — per `CLAUDE.md`'s honesty rule, a behavior claim that wasn't run is not reported as confirmed fact. Vulnerability/audit-tool output (`composer audit`, `npm audit`) is **not re-run here** — it was already run in disposable containers during Phase 2 and is cited from `projects/info/deps/AC-Task.md`, not duplicated.

```bash
# Facts gathered this phase (read-only):
git -C projects/repos/AC-Task branch -a
git -C projects/repos/AC-Task log origin/develop..origin/<branch> --oneline   # per non-bot branch
grep -rniE "(password|secret|api[_-]?key|token|private[_-]?key|BEGIN RSA|BEGIN PRIVATE|AKIA[0-9A-Z]{16})" <tracked source, excluding secrets/, .env.example, vendor/, node_modules/, .git/>
grep -rni "mongo" src/be/App src/be/config src/be/database src/be/composer.json src/fe/src   # confirm Mongo is unused
```

## Carried forward from Phase 2 (not re-discovered, cited)

- **Critical CVE-2026-114509** — Laravel TokenGuard auth-bypass, present on the locked `laravel/framework` v11.44.1, unfixable within the 11.x line. See "Exploitability" below for a code-level refinement Phase 2 explicitly left open.
- **7 tracked secret files**: `secrets/db_connection`, `db_database`, `db_host`, `db_password`, `db_port`, `db_root_password`, `db_username` — all committed to git history. Not opened here either (location only, per task scope). Treated as **compromised** per `CLAUDE.md`; rotation + history removal already requested of Rasim by the Orchestrator. History removal is **Approval Gate 1**, not Phase 4 branch cleanup.
- `composer audit`: 48 advisories / 13 packages, incl. the CVE above. `npm audit`: 25 advisories, 17 high.
- No `.github/workflows/` — no CI at all.
- 29 open PRs / many local dependabot/renovate/snyk branches.

## New findings this phase

### 1. Exploitability refinement on the critical CVE — still P0, but the primary auth path may not hit `TokenGuard` directly

`config/auth.php`'s `'api'` guard has `'driver' => 'jwt'` (the `php-open-source-saver/jwt-auth` package's own guard class), not Laravel's built-in `token` driver (`Illuminate\Auth\TokenGuard`, the class CVE-2026-114509 targets). `laravel/sanctum` is installed and its stateful middleware is globally loaded in `bootstrap/app.php`, but no `'sanctum'` guard is registered in `config/auth.php` and no route uses `auth:sanctum` — grepped the whole of `App/` for `Sanctum`/`TokenGuard`: no direct usage found anywhere. **This does not clear the finding** — `composer audit` correctly flags the package version regardless of which guard a given app happens to configure, exploitability through less-obvious paths (framework internals, other packages invoking `TokenGuard` indirectly) was not exhaustively ruled out in a docs-only phase, and the only real fix either way is the same major-version bump already required by standing decision 1. Still P0; this note exists so Phase 6/9 don't over- or under-state urgency.

### 2. `UserSeeder.php` has two non-bracketed `namespace` statements — expected fatal parse error **(static, unverified)**

`src/be/database/seeders/UserSeeder.php` opens with:

```php
namespace database\seeders;

namespace Database\Seeders;
```

PHP only allows a single non-bracketed `namespace X;` statement per file, and it must be the first statement. A second one is a compile-time fatal error ("Namespace declaration statement has to be the very first statement or after any declare call in the script"). If this is accurate — and it follows directly from PHP's grammar, not from guessing application behavior — `UserSeeder` cannot be parsed at all, which means `php artisan db:seed` / `migrate --seed` (`DatabaseSeeder::run()` calls `UserSeeder::class` directly) fails outright. `ExampleREADME.md`'s own documented setup path (`php artisan migrate --seed`, step 4) would not work as written. Not executed this phase (docs-only) — Phase 5's baseline run or Phase 11 should confirm with a real `php -l` lint pass in the container and record the result, but this is about as close to certain as a static read gets.

### 3. `TaskController::show()` likely fails every call — inconsistent `authorize()` target **(static, unverified)**

```php
// show() — src/be/App/Http/Controllers/Tasks/TaskController.php
$task = $this->taskService->getTaskById($id);
$this->authorize("view", Task::class);     // <-- class name, not $task
```

`update()` and `destroy()` in the same file correctly call `$this->authorize('update'/'delete', $task)` with the fetched instance. `TaskPolicy::view(User $user, Task $task): bool` returns `$user->id === $task->user_id` — it needs a real `Task` to compare against. Passed a bare class-name string instead, `$task->user_id` inside the policy is a property access on a string, which in PHP 8 is null (with a warning), so the comparison is `$user->id === null`, which is false for any real user — the policy denies, `AuthorizationException` is thrown, and `TransactionHelper` maps that to HTTP 403. Net effect: `GET /tasks/{id}` is expected to return 403 for every request, regardless of ownership — not a data leak (the opposite — it denies everyone, including the rightful owner), but a broken feature. Unverified at runtime this phase; flagged for a Phase 11 regression test before anyone calls this "fixed."

### 4. `TaskRepository::findOrFail()` returns a string instead of throwing — breaks the documented 404 contract **(static, unverified)**

```php
public function findOrFail(int $id): Task|string
{
    $task = $this->taskModel->find($id);
    if (!$task) {
        return "Task not found with ID: {$id}";   // string, not an exception
    }
    return $task;
}
```

The OpenAPI annotations on `show()`/`update()`/`destroy()` all document a `404` response for a missing task. Given the return type is `Task|string`, a miss produces a string, which then flows into `$this->authorize(...)` (same mechanism as finding #3) — the authorization check degenerate-fails on a string argument, producing **403**, not 404, for any non-existent task ID across `show`, `update`, and `destroy`. Same unverified-at-runtime caveat as above; same Phase 11 recommendation (write a test against a real non-existent ID and see what actually comes back before marking this resolved).

### 5. Unauthenticated operational endpoints, including one that triggers cache-clearing artisan commands

`routes/api/check.php`'s entire group (`Route::group(['prefix' => 'check'], ...)`, no `middleware` key at all beyond the global `throttle:60,1` applied to all of `api.php`) exposes `/check/db`, `/check/health`, `/check/static`, `/check/ip`, and `/check/clear_cache` with **no authentication**. `CheckController::clearCache()` runs `Artisan::call('optimize:clear')`, `config:cache`, `clear-compiled`, `cache:clear`, `route:cache`, `view:clear` — six artisan commands — on an unauthenticated `GET`. Repeated anonymous calls are a cheap, repeatable cache-thrash/DoS vector against the running app. `/check/ip` additionally makes an unauthenticated outbound HTTP call to a third-party service (`https://ipapi.co/json/`) on every hit — low severity (no user-controlled input, so not SSRF in the classic sense), but still an anonymous-triggerable outbound call with no rate-limit beyond the blanket `60,1`.

### 6. Sensitive data logged to the browser console on every API request

`src/fe/src/shared/lib/api.ts`'s request interceptor does:

```ts
const token = localStorage.getItem('token');
if (token) config.headers.Authorization = `Bearer ${token}`;
console.log('API Request:', { url: config.url, method: config.method, headers: config.headers });
```

The `console.log` runs *after* the `Authorization` header is attached, so the bearer JWT is printed to the browser console on every single API call. Combined with storing the token in `localStorage` (not an httpOnly cookie), the access token is exposed to: anything reading browser devtools, any injected/XSS script, and any error-tracking/session-replay tool that captures console output. `CLAUDE.md`: "Logs, error reports, and traces never contain secrets." This is a logging statement, not a hardcoded secret, so it's a Phase 9 hardening fix, not a Gate-1/compromised-credential situation — but it is a real, currently-shipping token-exposure path.

### 7. Docker standard violations (all three Dockerfiles)

- `php.dockerfile`: single stage, no `test` target, no `HEALTHCHECK`. Installs `build-essential`, `libpng-dev`, `libxml2-dev`, etc. (compiler toolchain) and keeps them in the only (= runtime) image. Installs `pdo_mysql`/`mysqli` PHP extensions despite the app only using Postgres (`pdo_pgsql`) — unused attack surface/image bloat. Does `chmod -R 777 storage bootstrap/cache` (should be a narrower mode with correct ownership, not world-writable). Ends `USER www-data` — the one thing it gets right.
- `node.dockerfile`: single stage, no non-root `USER`, `CMD ["npm","run","dev"]` — ships the Vite **dev server** as the container's command, not a production build artifact served statically.
- `nginx.dockerfile` + `server/nginx/default.conf`: `location /`'s `proxy_pass http://php:9000;` sends plain HTTP to `php:9000`, which is `php-fpm` listening for **FastCGI**, not HTTP — the `.php$` location block further down correctly uses `fastcgi_pass php:9000;`. The `location /` block looks like it was meant to be a `try_files` fallback into the `.php` block, not its own `proxy_pass`. Not started/tested this phase; flagged for Phase 5's actual `make verify` run to confirm whether this breaks non-PHP-file requests (e.g. static assets, the SPA-style catch-all) in practice.

### 8. A sixth floating image tag Phase 2's inventory didn't list

`docker-compose.yml`'s `composer` service uses `image: composer:latest` — a fully floating tag, in addition to the five already flagged in `projects/info/deps/AC-Task.md` (`php:8.3-fpm`, `nginx:stable-alpine`, `postgres:latest`, `mongo:latest`, and the `node:23-alpine` vs `node:current-alpine` split).

### 9. Dead infrastructure: `mongodb` compose service

Grepped all of `src/be` (`App/`, `config/`, `database/`, `composer.json`) and all of `src/fe/src` for `mongo` (case-insensitive): zero hits anywhere in application code. The `mongodb` service is also the only one in `docker-compose.yml` with no `networks:` entry (so it can't reach, or be reached by, `php`/`nginx` even if something tried) and no `restart:` policy, unlike every other service. Dead weight, not a live part of the database layer described in `docs/ARCHITECTURE.md`.

### 10. Duplicate `'api'` middleware-group registration

`bootstrap/app.php` calls `$middleware->group('api', [...])` **twice**, with two different arrays:

```php
$middleware->group('api', ['throttle:api', EnsureFrontendRequestsAreStateful::class]);
// ... a few lines later ...
$middleware->group('api', [ThrottleRequests::class.':60,1', EnsureFrontendRequestsAreStateful::class, CompressResponse::class]);
```

Laravel's `Middleware::group()` assigns (overwrites), it does not merge — so the first registration is dead, superseded by the second. Separately, `routes/api.php` *also* wraps every route in `Route::middleware(['throttle:60,1'])` at the route-group level — meaning throttling is configured via two independent mechanisms that happen to agree on the same rate today, a duplication/clarity debt rather than a functional bug.

### 11. Secret scan of the working tree (redacted)

Beyond the already-known `secrets/db_*` files (not opened, location only) and the already-known plaintext `POSTGRES_PASSWORD`/`MONGO_INITDB_ROOT_PASSWORD` values in `docker-compose.yml` (lines ~85-87, ~99-100 — both literal `AzeriCard`, local-dev-only, still a hardcoded-credential pattern per `CLAUDE.md`'s Docker standard), a manual pattern grep (no `gitleaks` binary available on this host — confirmed `command not found`; manual regex substitute used) across all tracked `*.php`/`*.js`/`*.ts`/`*.tsx`/`*.json`/`*.yml`/`*.conf`/`*.nix` files, excluding `secrets/`, `.env.example`, `vendor/`, `node_modules/`, found **no additional hardcoded secrets** — the only other hits were legitimate runtime token *handling* code (`auth('api')->login()`, `localStorage.getItem('token')`), not hardcoded values. `config/cors.php`'s `allowed_origins` hardcodes one specific, session-scoped Google Project IDX / Firebase Studio cloud-workstation URL (`https://8000-idx-ac-task-<session-id>.cluster-....cloudworkstations.dev`) — not a secret, but a stale environment-specific value baked into committed config instead of env-driven (`CORS_ALLOWED_ORIGINS` or similar).

### 12. Backend test suite is the unmodified framework skeleton

`src/be/tests/` contains exactly `Unit/ExampleTest.php` and `Feature/ExampleTest.php` — Laravel's default scaffold, never replaced. Zero tests exist for `AuthController`, `TaskController`, `TaskService`, `TaskRepository`, `TaskPolicy`, or any Enum/DTO logic. Separately, `phpunit.xml`'s `DB_CONNECTION`/`DB_DATABASE` env overrides are **commented out**:

```xml
<!-- <env name="DB_CONNECTION" value="sqlite"/> -->
<!-- <env name="DB_DATABASE" value=":memory:"/> -->
```

With no override, a test run falls through to whatever `.env` configures — for this repo, that is Postgres (per `docker-compose.yml`'s `pgsql` service and the app's own config). Running the suite as currently configured risks hitting a real, shared Postgres instance rather than an isolated in-memory/sqlite DB. Not executed this phase; flagged as a precondition to fix (or at minimum confirm which DB a test run actually touches) before Phase 5 adopts any test run as its baseline.

### 13. No frontend test tooling at all

`src/fe/package.json`'s full dependency list (checked against Phase 2's inventory) contains no `vitest`, `jest`, `@testing-library/*`, or any other test runner/library — not "tests exist but are thin," no test runner is configured at all.

## Branch landscape — a significant out-of-scope finding for Phase 4

`git branch -a` lists 39 remote branches beyond `develop` (the default) and `test`. Breaking them down (counts from `git log <branch>..develop`/`develop..<branch>`, subjects only — diffs not opened, per this phase's docs-only scope):

| Branch | Ahead of `develop` | Behind `develop` | Note |
|---|---|---|---|
| `master` | 1 (a merge commit) | 186 | Stale legacy default-branch candidate; `develop` is already the active default, so this is **not** a Gate 4 "change the default branch" case — `master` itself looks like leftover from before `develop` became primary. Review-then-delete candidate. |
| `TransactionHelper` | 1 (`fix: lettle problem`) | 146 | Very stale, one small unique commit. Review-then-delete/Gate 11 candidate. |
| `refactor/readability-improvements` | 2 | 26 | Generic "Refactor: Improve code readability" commits, somewhat stale. Review-then-merge-or-close candidate. |
| **`feature/security-docker-ci-and-tests`** | **14** | **1** | **Nearly caught up to `develop`** (one commit behind) with 14 unique commits whose subjects directly target several items in this audit: `fix(security): ...`, `fix(ci): the pipeline gives the database password a value of its own`, `ci: run the docker quality gates on every push and pull request`, `build(docker): build every service from a local image`, `test: cover the api of the tasks and the authentication`, `chore(deps): update the dependencies and pin the platform`, `chore(security): take the credentials out of the repository`, `fix(fe): align the versions of the frontend and repair the build`. |

**This last branch needs Phase 4's first look, before anything else.** Subject lines alone suggest it may already contain a working CI pipeline, Docker hardening, real Task/Auth tests, dependency pinning, a frontend react/react-dom version fix, and a credentials-removal commit — i.e., a working draft of large parts of Phases 5/6/9/10/11. **Caveat, stated plainly:** only commit *subjects* were read (`git log --oneline`) — no diff was opened, so none of this is confirmed, and "take the credentials out of the repository" removing files from the current tree is **not** the same thing as purging them from git history (that remains Approval Gate 1 regardless of what this branch does). Phase 4 (`git-steward`) should diff this branch against `develop` first and report what it actually contains before Phases 5-11 start duplicate work on this repo.

Plus the previously-known bot branches: 4 `dependabot/composer/*`, 7 `dependabot/npm_and_yarn/*`, 13 `renovate/*`, 9 `snyk-{fix,upgrade}-*` — consistent with the 29-open-PR count in `REPOS_INDEX.md`.

## License / legal

No change from Phase 2: repo `LICENSE` is MIT; direct PHP deps MIT/BSD-3-Clause, direct JS deps MIT/ISC/Apache-2.0 — no conflicts found.
