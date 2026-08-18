## Agent skills

### Issue tracker

Local markdown — issues live as files under `.scratch/<feature>/`. See `docs/agents/issue-tracker.md`.

### Triage labels

Default canonical roles (`needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`). See `docs/agents/triage-labels.md`.

### Domain docs

Single-context — `CONTEXT.md` + `docs/adr/` at the repo root. See `docs/agents/domain.md`.

## Environment

This project runs in Docker Compose (`php`, `node`, `postgres`). Code lives on
the host; `opencode` and VSCode also run on the host. Never run `php`,
`composer`, `npm`, `phpunit`, `phpstan`, etc. directly — always go through the
`bin/` wrapper scripts, which `docker compose exec` into the right container.

First time in a fresh clone: `bin/setup`.

| Command | What it does |
|---|---|
| `bin/setup` | Build + start containers, install deps, migrate, install git hooks |
| `bin/php`, `bin/composer` | Run `php` / `composer` inside the `php` container |
| `bin/npm` | Run `npm` inside the `node` container |
| `bin/phinx` | Run migrations (`bin/phinx migrate`, `bin/phinx create MigrationName`) |
| `bin/psql` | Open a psql shell / run SQL against the `postgres` container |
| `bin/test` | Run PHPUnit (backend) |
| `bin/test-fe` | Run vitest (frontend) |
| `bin/analyse` | Run PHPStan (level max) |
| `bin/typecheck` | Run `vue-tsc` |
| `bin/fmt [paths...]` | Run php-cs-fixer (backend) |
| `bin/fmt-fe [paths...]` | Run prettier (frontend) |
| `bin/lint-fe` | Run eslint (frontend) |

Backend lives in `backend/` (bare PHP 8.3+, PSR-4 `App\`, Postgres via PDO,
Phinx migrations). Frontend lives in `frontend/` (Vue 3 + TypeScript + Vite,
Tailwind 4 + shadcn-vue, proxying `/api` to the PHP container).

To add a UI component, use the `shadcn-vue` CLI (`bin/npm exec shadcn-vue add
<component>` from `frontend/`) — don't hand-roll components under
`components/ui/`. It generates styled Vue components (Tailwind + `cva`
variants) into `frontend/src/components/ui/<name>/`. Interactive components
(dialog, dropdown, popover, etc.) use `reka-ui` underneath for behavior
(focus trap, ARIA, positioning); simple presentational ones (Card, Table) are
plain markup styled via `cn()`. `reka-ui` is a dependency the generated code
uses, not a tool you invoke directly.

A pre-commit hook (lefthook) runs formatting, PHPStan, vue-tsc, and unit tests
on staged files automatically — see `lefthook.yml`. If containers aren't
running, the `bin/` scripts fail with a clear message telling you to run
`bin/setup` or `docker compose up -d`.

Before writing backend code: `declare(strict_types=1)`, full type coverage
(typed properties/params/returns, enums, readonly, constructor promotion).
Code must pass `bin/analyse` (PHPStan max) and `bin/fmt` (php-cs-fixer) clean.
PHP has no project-wide setting for `strict_types` — it's a per-file declare,
required as the first statement in every file. Don't add it by hand: the
`declare_strict_types` php-cs-fixer rule (`.php-cs-fixer.php`) inserts it
automatically on `bin/fmt` / pre-commit, so just write the file and let the
formatter add it.

See `.scratch/starter-repo/spec.md` for the full rationale behind this setup,
and `docs/adr/0001-hybrid-docker-topology.md` for why the environment is
shaped this way.
