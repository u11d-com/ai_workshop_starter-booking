# Hybrid Docker topology: code and opencode on host, runtime in containers

Status: accepted

The workshop starter runs its PHP/Node/Postgres runtime in Docker Compose, while
source code, VSCode, and the opencode agent stay on the host (WSL2 filesystem on
Windows). All runtime commands go through thin `bin/` wrapper scripts that
`docker compose exec` into the containers.

## Why not the alternatives

- **Full devcontainer** (everything, including opencode, inside the container):
  requires re-authorizing opencode or mounting the user's config/auth dirs into
  the container — fragile across macOS and WSL2, and devcontainer startup issues
  are the classic workshop time-sink.
- **Host-native** (everyone installs PHP + Node locally): install burden across
  three OS setups and inevitable version drift.

## Consequences

- Intelephense (PHP LSP) runs on the host without a PHP binary — it is pure JS
  analysis — so editor tooling works with zero host PHP install.
- Formatters, tests, and migrations must always be invoked via `bin/` wrappers,
  never directly; `opencode.json` and `AGENTS.md` wire agents to those wrappers.
- VSCode's *built-in* PHP validator (separate from Intelephense) does want a
  host-executable `php` binary. Rather than disable it, `bin/php-vscode`
  wraps `docker compose exec` and translates the host file path into the
  container's, and `.vscode/settings.json` points `php.validate.executablePath`
  at it — so any tool expecting a local `php` executable can be pointed at
  the same wrapper pattern.
- On Windows, the repo must be cloned inside the WSL2 filesystem (not `/mnt/c`)
  or bind-mount file watching and performance degrade badly.
- Git hooks are the one thing that must run directly on the host (git invokes
  them outside any container). `lefthook` is installed as a standalone binary
  downloaded by `bin/_install-lefthook` into `.bin/` — not as an npm
  devDependency — specifically to avoid pulling in a host Node/npm
  requirement for something that's otherwise pure Docker + bash. A versioned
  `.githooks/pre-commit` script (wired via `core.hooksPath`) invokes it.
