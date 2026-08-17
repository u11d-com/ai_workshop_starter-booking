# AI Workshop: Booking App Starter

A minimal starter for an agentic AI coding workshop. The stack — bare PHP
backend, Vue frontend — matches a real production setup. Your task during the
workshop: turn this into a booking app (resources, time slots, reservations)
with the help of an AI coding agent.

## Stack

- **Backend**: bare PHP 8.3+, PSR-4 autoloading via Composer, a hand-rolled
  router, PDO + Postgres, Phinx migrations.
- **Frontend**: Vue 3 + TypeScript + Vite, Tailwind 4 + shadcn-vue.
- **Runtime**: Docker Compose (`php`, `node`, `postgres`). Your code lives on
  the host; only the runtime is containerized.

## Quick start

```
bin/setup
```

This builds the containers, installs backend and frontend dependencies, runs
migrations, installs a git pre-commit hook, and checks the backend is healthy.

Then open:

- Frontend: http://localhost:5173
- Backend health check: http://localhost:8080/api/health

## Windows / WSL2

**Clone this repo inside the WSL2 filesystem** (e.g. `~/code/...`), not under
`/mnt/c/...`. Cross-filesystem bind mounts are slow and file-watching (HMR)
breaks. Run all `bin/` commands from a WSL2 terminal, and open VSCode via
`code .` from inside WSL2 so it uses the WSL2 remote connection.

## No PHP installed on the host — that's expected

There is deliberately no PHP on your host machine; all backend tooling runs
in the `php` container via `bin/` scripts. VSCode's built-in PHP validator
still works though: `.vscode/settings.json` points
`php.validate.executablePath` at `bin/php-vscode`, a small wrapper that
translates the file path VSCode passes in and runs `php` inside the
container via `docker compose exec`. The Intelephense extension needs no PHP
binary at all (pure JS analysis).

## Everyday commands

See the table in [AGENTS.md](./AGENTS.md#environment) — the same commands
your AI agent uses (`bin/test`, `bin/analyse`, `bin/fmt`, etc.).

## What's here vs. what you build

This starter includes one working vertical slice — a `resources` table, a
`GET /api/resources` endpoint, and a Vue page listing them — so you can see
every layer wired up before you start. The booking domain itself (time slots,
reservations, availability) is **not built**: writing the spec and
implementing it, with an AI agent, is the workshop exercise.

See [.scratch/starter-repo/spec.md](./.scratch/starter-repo/spec.md) for how
this starter itself was designed, and [docs/adr/](./docs/adr/) for the
key architectural decision behind it.
