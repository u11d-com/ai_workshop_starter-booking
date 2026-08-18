# Spec: Workshop starter repo (`u11d-com/ai_workshop-booking`)

Status: done

> Meta note: this spec describes the *starter repo itself* — the artifact handed to
> workshop participants. It is intentionally left visible in the template so
> participants can see how the repo was designed. It does not describe the booking
> application; writing that spec is the participants' task.

## Purpose

A minimal, working starter project for an agentic AI coding workshop. Participants
(devs on macOS and Windows+WSL2, using VSCode + opencode with an Anthropic
subscription) will implement a booking-like webapp (resources, time slots,
reservations) on top of it. The starter must prove the whole toolchain works within
minutes and give agents a copyable pattern for every layer — while leaving the
booking domain entirely unbuilt.

## Decisions and rationale

| # | Decision | Alternatives rejected | Why |
|---|----------|----------------------|-----|
| 1 | **Hybrid topology**: code on host, runtime in Docker via compose, opencode on host, container access via `bin/` docker-exec wrappers | Full devcontainer (opencode auth pain, flakiness across OSes); host-native (install burden, drift) | See ADR-0001 |
| 2 | **Bare PHP** 8.3+, Composer, PSR-4 (`App\`), hand-rolled ~30-line router | Laravel (agents know it well, but hides the instructive layers and abandons "match participants' real stack"); Slim/micro (worst of both) | Matches participants' production stack; agent work stays visible |
| 3 | **Vue 3 + Vite SPA**, Vite dev proxy `/api` → PHP container | PHP-served pages + CDN Vue | Matches real stacks; no CORS pain; HMR |
| 4 | **Postgres 17** in compose | SQLite (simpler, but workshop goal is "more moving parts for agents to absorb"); MySQL | Real DB friction (healthcheck, env vars, wait-for) is authentic agent material |
| 5 | **Phinx** for migrations | Hand-rolled runner | Agents know Phinx cold; hand-rolling is pre-workshop yak-shaving |
| 6 | **Working vertical slice**: one entity end-to-end (migration → PDO → API → Vue → tests) | Empty skeleton; health-only | Proves every layer before start; copyable pattern |
| 7 | **bash `bin/` scripts** as the only task runner | justfile (needs install), Makefile (hostile syntax) | Zero install on macOS/WSL2; wrappers needed for opencode anyway — one uniform mechanism |
| 8 | Ship **`.vscode/`**, skip `.devcontainer/` | Optional devcontainer | Second supported path doubles mid-workshop support surface |
| 9 | Ship **`opencode.json` + minimal `AGENTS.md`** (env mechanics only) | Pre-filled conventions/CONTEXT.md | Writing agent docs is itself workshop content |
| 10 | **No booking spec/tickets in repo** | docs/spec.md, tiered tickets | Spec-writing with agents is the participants' first exercise |
| 11 | **GitHub template repo** distribution | Fork | Clean history, no lineage; "Use this template" button |
| 12 | Tooling: **PHPUnit 11, php-cs-fixer, PHPStan; vitest, eslint, prettier, vue-tsc** — latest stable versions at build time | — | Standard; all invoked through container wrappers so hosts stay clean |
| 13 | **Tailwind 4 + shadcn-vue** preinstalled, 1–2 components used in slice | Tailwind-only (participants add shadcn via agent) | Component setup is boilerplate, not instructive; ready pattern beats exercise |
| 14 | **TypeScript** frontend, `vue-tsc` type check in verification | Plain JS | Matches modern stacks; stricter agent feedback loop |
| 15 | **Strict modern PHP**: `declare(strict_types=1)`, full type coverage, enums/readonly/promotion; **PHPStan level max** + php-cs-fixer `@PER-CS` + strict rules | Looser levels | Greenfield — no legacy excuse; max is achievable from day one and agents handle strictness fine |
| 16 | **lefthook** pre-commit hooks, installed as a standalone binary downloaded by `bin/_install-lefthook` (not an npm devDependency) into `.bin/`, wired via a versioned `.githooks/pre-commit` script (`core.hooksPath`): format + lint + PHPStan + vue-tsc + unit tests, run in parallel | npm devDependency + `lefthook install` (pulls in a host Node dependency for one binary); Husky (npm-centric); no hooks | Go binary, no host Node/npm required at all — matches the "runtime only in containers" principle; keeps commits aligned with rules. Unit tests fast enough in parallel; browser/e2e tests excluded from hooks |

## Repo structure

```
ai_workshop-booking/
├── docker-compose.yml          # services: php, node, postgres
├── lefthook.yml                # pre-commit: fmt + lint + phpstan + vue-tsc + unit tests (parallel)
├── .githooks/pre-commit        # versioned hook script, execs .bin/lefthook (core.hooksPath)
├── bin/
│   ├── setup                   # compose up → composer install → npm ci → lefthook binary → migrate → curl health
│   ├── _install-lefthook       # downloads pinned lefthook release binary into .bin/ (no npm)
│   ├── php, composer, npm      # docker compose exec wrappers
│   ├── phinx, psql
│   ├── test, test-fe           # phpunit / vitest
│   ├── analyse, typecheck      # phpstan / vue-tsc
│   └── fmt, fmt-fe             # php-cs-fixer / prettier+eslint
├── backend/
│   ├── composer.json           # deps: robmorgan/phinx; dev: phpunit, php-cs-fixer, phpstan
│   ├── phinx.php
│   ├── phpstan.neon            # level: max
│   ├── .php-cs-fixer.php       # @PER-CS + strict rules
│   ├── public/index.php        # front controller
│   ├── src/                    # Router, Database (PDO), Resource repository + handlers — strict_types everywhere
│   ├── db/migrations/          # create_resources
│   └── tests/                  # ResourcesTest
├── frontend/                   # Vite + Vue 3 + TS; Tailwind 4 + shadcn-vue; vite.config proxies /api;
│                               # ResourceList.vue (uses shadcn Card/Table); 1 vitest test; lefthook devDep
├── .vscode/
│   ├── extensions.json         # Intelephense, Volar, ESLint, Prettier
│   └── settings.json
├── opencode.json               # formatters wired to bin/fmt*, LSP defaults
├── AGENTS.md                   # env mechanics only: run / test / format / migrate
└── README.md                   # quick start + WSL2 warning (clone inside WSL2 fs, not /mnt/c)
```

## Vertical slice definition

1. Phinx migration creates `resources` (id, name) with seed rows.
2. PDO repository; endpoints `GET /api/health` and `GET /api/resources` (JSON).
3. Vue page lists resources through the Vite proxy, rendered with shadcn-vue components (Card/Table) + Tailwind.
4. One PHPUnit test and one vitest test, both green via `bin/test` / `bin/test-fe`.
5. `bin/analyse` (PHPStan max), `bin/typecheck` (vue-tsc), `bin/fmt --check`, `bin/fmt-fe --check` all pass.
6. Pre-commit hook (lefthook) runs 5's checks + unit tests in parallel; fails with a clear "containers not running — run bin/setup" message when compose is down.

## Container detail

- **php**: `php:8.3-cli` (or newer stable) running `php -S 0.0.0.0:8080 -t public` — dev-grade on purpose, no nginx/fpm split.
- **node**: runs `vite dev --host`; port 5173 exposed.
- **postgres**: 17, healthcheck; `bin/setup` waits on health before migrating.
- Source mounted as volumes; vendor/node_modules live in the containers' mounted dirs.

## Deliberately absent (workshop exercises)

Booking domain code, task spec, tickets, `CONTEXT.md`, agent conventions beyond mechanics.

## Verification

From a clean clone: `bin/setup` → open `localhost:5173`, see the resource list →
`bin/test`, `bin/test-fe`, `bin/analyse`, `bin/typecheck` pass → a test commit
triggers lefthook and passes. Must be verified on macOS and Windows+WSL2
before the workshop.

## Comments

Implementation complete. `bin/setup` runs clean end-to-end (docker
presence/running checks, compose up, composer install, migrations, npm ci,
lefthook binary install, health check); the full check suite (`bin/test`,
`bin/analyse` — PHPStan max, `bin/typecheck`, `bin/test-fe`, `bin/lint-fe`,
`bin/fmt`, `bin/fmt-fe`) passes; a real `git commit` exercises the
`.githooks/pre-commit` → `.bin/lefthook` hook chain successfully. Frontend
uses the real `shadcn-vue` CLI output (not hand-rolled components). Host
requires only Docker, bash, and git — no PHP or Node needed on the host
itself (see `docs/adr/0001-hybrid-docker-topology.md` for the full
rationale, including the standalone lefthook binary and the
`bin/php-vscode` wrapper for VSCode's built-in PHP validator).

Still outstanding before the actual workshop: a real dry run on
Windows+WSL2 (only macOS has been exercised so far in this environment).

