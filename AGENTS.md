# AGENTS.md

**Universal standards and documentation for all AI agents working on Space.**

This file is the **primary reference** for AI agents (Claude Code, Cursor, GitHub Copilot, etc.) working on this
project. It contains comprehensive documentation on architecture, code standards, workflows, and development practices.

**Multi-Agent Environment**: Multiple AI agents may work on this project. All agents must follow the standards defined
here to ensure consistency and quality.

**Related Files**:

- [CLAUDE.md](CLAUDE.md) - Claude Code specific guidance and quick start
- [.agents/README.md](.agents/README.md) - Overview of the .agents/ coordination system
- [.agents/EXAMPLES.md](.agents/EXAMPLES.md) - Detailed code examples
- [documentation/README.md](documentation/README.md) — Index of the deep-dive docs: requirements, installation,
  configuration, architecture, domain model, infrastructure, workers, API, development

**Extension Directives**: Each enabled extension ships its own `appliance/extensions/*/AGENTS.md`, and may add a
`documentation/` directory beside it for deep-dives. Those files — not this one — describe what the extension
does: they extend or refine the standards below for extension-specific behavior (recipes, steps, hooks,
container libraries, routes, etc.), and in case of conflict they take precedence for that extension's code.
Read them before working on or with an extension. **This documentation stays about the appliance itself and
never describes an extension's features.**

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Code Architecture](#code-architecture)
3. [API & Routes](#api--routes)
4. [Common Commands](#common-commands)
5. [Development Guidelines](#development-guidelines)
6. [Key Concepts](#key-concepts)
7. [Workflow Orchestration](#workflow-orchestration)
8. [Task Management & Feedback Loop](#task-management--feedback-loop)
9. [Core Principles](#core-principles)

---

## Project Overview

Space is a **Platform as a Service (PaaS)** application — a CI/CD/deployment solution built on Teknoo East PaaS,
Teknoo Kubernetes Client, and Symfony. Multi-account, multi-users, multi-projects system that builds and deploys
IT projects on containerized platforms.

**Key Technologies**: PHP 8.5+, Symfony (both maintained branches — 7.4 LTS and 8.x — are supported; constraints
are per component), Doctrine MongoDB ODM, AMQP (RabbitMQ), Valkey (sessions), Mercure,
Buildah (OCI image builder), Kubernetes (default), Docker Compose + Ansible + Traefik v3 (alternate target).

**Deployment targets**: selected per cluster by the `type` field — `kubernetes` (Kubernetes API) or
`docker-compose` (Docker host over SSH, Ansible applies Compose/Traefik stack). The job API and
`RunJob` recipe are target-agnostic.

**Extensibility**: Driver-based architecture supports further targets and build tools. Extensions ship from
their own repositories and are mounted at runtime under `appliance/extensions/`, never committed here.

## Code Architecture

```
appliance/
├── bin/            # console + config.sh
├── domain/         # Business logic — Object/, Recipe/, Contracts/, Loader/, Writer/, Query/,
│                   #   Cluster/, Configuration/, Liveness/, Middleware/, Service/
├── src/            # Application layer — Kernel.php only
├── infrastructures/ # Framework integrations — Doctrine/, Kubernetes/, AnsibleDockerCompose/,
│                   #   Symfony/, Twig/, Endroid/, Recipe/ (ProvisioningPlanBowl)
├── extensions/     # Mounted extensions — gitignored, each self-contained and self-documented
├── config/         # PHP-DI + Symfony config — di.*.php files, env-var driven
├── features/       # Behat .feature files (NOT in tests/)
├── templates/      # Twig templates (.html.twig and api/*.json.twig)
├── translations/   # Symfony translation catalogues
├── public/         # Web entry point
└── tests/          # PHPUnit unit tests + Behat contexts, traits and fixtures
```

### Architectural Patterns

1. **Recipe Pattern**: Workflows composed of **Plans** (`domain/Recipe/Plan/`, plus the async tasks in
   `domain/Recipe/Plan/Task/`) and **Steps** (`domain/Recipe/Step/`, one subdirectory per category).
   See [`.agents/recipes.md`](.agents/recipes.md) for details.
2. **DDD**: Clear separation between domain, application, and infrastructure layers.
3. **PHP-DI**: Dependency injection via `config/di.*.php`. See [
   `documentation/architecture.md`](documentation/architecture.md).
4. **Extension System**: From Teknoo East Foundation — modify behavior without editing core code. An extension
   is mounted from its own repository and must never require a change here.

## API & Routes

```
config/routes/api/v1/
├── unauthenticated/    # Public endpoints (login)
├── authenticated/      # User endpoints (JWT required): account, project, job, jwt, settings
└── admin/              # Admin endpoints: account, project, job, user
```

Route files are prefixed: `space.api.v1.<name>.yaml`. The `/api/v1` and `/api/v1/admin` prefixes come from the
loader `config/routes/api.yaml`, not from the file names.

Web routes: 10 YAML files (`space.*.yaml`) in `config/routes/` with 48 `path:` entries total. That directory
also holds the framework/vendor route files (`api.yaml`, `connect.oauth.yaml`, `east.*`, `scheb_2fa.yaml`,
`symfony.framework.yaml`, `web_profiler.yaml`).

**JWT Auth**: Generate from WebUI account settings or `POST /api/v1/login`. Use `Authorization: Bearer {token}`.
Config via `SPACE_JWT_*` env vars. Templates: `.html.twig` (HTML) and `.json.twig` (API).

See [`documentation/api.md`](documentation/api.md) for full API docs; [`.agents/api.md`](.agents/api.md) for structure.

## Common Commands

All commands from project root via `./space.sh` or from `appliance/`. `./space.sh <target>` delegates to
`appliance/Makefile`; `./space.sh help` lists every target.

```bash
# Install & Setup
./space.sh install              # Production install
./space.sh dev-install          # With dev dependencies
./space.sh update               # Update dependencies (DEPENDENCIES=lowest for the lowest set)
./space.sh config               # Interactive config wizard
./space.sh config-dry-run       # Preview the files the wizard would write
./space.sh create-admin email=user@example.com password=secret

# Extensions
./space.sh extension-list
./space.sh extension-enable name=<ExtensionName>
./space.sh extension-disable name=<ExtensionName>
./space.sh ext <extension> <target>   # Run a target of an extension's own Makefile/space.sh

# Testing (NB_THREADS=4 by default)
./space.sh test                 # All tests (units + behavior, with coverage)
./space.sh test-without-coverage
./space.sh test-mono-thread
./space.sh units-tests           # Unit tests
./space.sh units-tests-without-coverage
./space.sh behavior-test         # Behat features
./space.sh behavior-test-mono-thread

# Quality
./space.sh qa                   # lint + phpstan + phpcs + audit
./space.sh qa-offline           # Same without the network `audit` — the CI-safe variant
./space.sh lint | phpstan | phpcs | audit   # Individually, much faster than full qa
./space.sh verify               # clean → dev-install → test-without-coverage → qa-offline → clean → install

# Cache, database & Docker
./space.sh warmup               # Clear and warm up cache
./space.sh clean                # Remove vendors, caches, logs
./space.sh build && ./space.sh start
./space.sh stop | restart
./space.sh db-backup | db-restore

# Workers (async job processing)
bin/console messenger:consume new_task | execute_job | history_sent | job_done
```

Four Messenger transports: `new_task`, `execute_job`, `history_sent`, `job_done`. An enabled extension may
declare transports of its own; its own documentation says which, and which consumers to run.

## Development Guidelines

### Code Standards

- **PSR-12** (enforced via `phpcs --standard=PSR12`)
- **PHPStan at max level** — the real configuration (level, paths, ignores) lives in `appliance/phpstan.baseline.neon`.
  `appliance/phpstan.neon` is a gitignored stub copied from `phpstan.dist.neon` by the Makefile.
- **Test coverage**: the suite has been at 100% line coverage since 2026-09-12 — do not lower it.
  No threshold is enforced by `phpunit.dist.xml`, so this is a discipline, not a gate.
- **Zero deprecations**: `phpunit.dist.xml` sets `failOnDeprecation="true"`. A deprecation warning is a defect to
  track down to its call site, never noise to silence.
- All new features must include tests

**CI**: the real pipeline is GitLab CI (`.gitlab-ci.yml`) — PHP 8.5, one lowest-dependencies job and one
upper-dependencies job, each running `make test` and `make qa`. `.github/` contains only `FUNDING.yml`; there are
no GitHub Actions workflows.

### Key Conventions

| Convention          | Requirement                                    |
|---------------------|------------------------------------------------|
| Strict typing       | Always `declare(strict_types=1);`              |
| Type declarations   | Full type hints on all params and return types |
| Readonly properties | Use `readonly` where applicable                |
| Property promotion  | Use constructor property promotion             |

### Configuration

- [`documentation/configuration.md`](documentation/configuration.md) — full environment variable reference table
- `.env.local` — local config (not committed); `.env.local.dist` — template

### Recipes, Testing, Forms, Security

See [`.agents/recipes.md`](.agents/recipes.md) · [`.agents/testing.md`](.agents/testing.md) ·
[`.agents/forms.md`](.agents/forms.md) · [`.agents/security.md`](.agents/security.md)

### Branches

Work branches are named `feature/…` or `hotfix/…`. They are merged into **`dev`**, and `dev` is merged into **`main`**
for a release. There is no `master` branch — never branch from or target one.

## Key Concepts

### Multi-tenancy Model

- **Account** — top-level entity (company/service/individual)
- **User** — human users belonging to accounts
- **Project** — Git repositories owned by accounts
- **Job** — represents a single deployment
- **Environment** — per-account cluster namespaces (Kubernetes) or compose namespaces (docker-compose)

### Deployment Flow

1. User creates Job → `new_task` worker prepares it (the same worker also applies the account provisioning
   tasks: registry / environment install or reinstall, quota refresh)
2. `execute_job` worker clones Git repo, runs PaaS compilation, builds images, deploys
3. `history_sent` / `job_done` workers persist results

### PaaS Compilation

Projects define deployments in `.paas.yaml`. The compiler: parses YAML → applies hooks (composer, npm, pip, make,
etc.) → builds OCI images → generates deployment manifests.
Platform-agnostic at domain level; platform-specific transcribers come from East PaaS.
Supports "extends" for reusable components via container libraries. Since PaaS file `v1.2`, `services` can be
declared inside a container and `ingress` inside a service (expose shortcuts), compiled to the same deployment as
explicit `services`/`ingresses`.

## Workflow Orchestration

### Session Start

- Read `.agents/feedback/INDEX.md` — learn from past challenges

### Plan Mode

- Enter plan mode for any non-trivial task (3+ steps or architectural decisions)
- If something goes sideways: STOP and re-plan
- Use plan mode for verification steps, not just building

### Verification Before Done

- Never mark a task complete without proving it works
- Run tests, check logs, demonstrate correctness

## Task Management & Feedback Loop

1. **Plan First**: Write plan to `.agents/tasks/todo.md` with checkable items (optional)
2. **Verify Plan**: Check in before starting implementation
3. **Track Progress**: Mark items complete as you go
4. **Document Results**: After completing any task, write feedback to `.agents/feedback/`

### After Every Task (Required)

1. Create `.agents/feedback/YYYY-MM-DD-task-name.md` with:
    - **Task Summary** — what was accomplished
    - **Missing Precision** — what info would have helped
    - **Blockers** — what slowed you down
    - **Suggestions** — how to improve docs/codebase
    - **Lessons Learned** — patterns or gotchas discovered
2. Add entry to `.agents/feedback/INDEX.md`

See [.agents/feedback/INDEX.md](.agents/feedback/INDEX.md) for format reference and past entries.

## Core Principles

- **Simplicity First**: Make every change as simple as possible. Minimal code impact.
- **No Laziness**: Find root causes. No temporary fixes. Senior developer standards.
- **Minimal Impact**: Changes should only touch what's necessary. Avoid introducing bugs.
