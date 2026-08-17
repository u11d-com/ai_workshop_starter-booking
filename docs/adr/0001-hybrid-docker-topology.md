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
- On Windows, the repo must be cloned inside the WSL2 filesystem (not `/mnt/c`)
  or bind-mount file watching and performance degrade badly.
