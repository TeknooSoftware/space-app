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
- [documentation/](documentation/) — Deep-dive docs: architecture, domain model, API, configuration, development,
  workers, infrastructure, installation, requirements

**Extension Directives**: Each enabled extension may ship its own `appliance/extensions/*/AGENTS.md`
and `.agents/*.md` documentation. These files extend or refine the standards above for extension-specific
behavior (recipes, steps, hooks, container libraries, routes, etc.). In case of conflict,
extension-specific directives take precedence for that extension's code. Always read them before
working on or with extensions.

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

**Key Technologies**: PHP 8.4+, Symfony 7.4+/8+, Doctrine MongoDB ODM, AMQP (RabbitMQ), Mercure,
Buildah (OCI image builder), Kubernetes (default), Docker Compose + Ansible + Traefik v3 (alternate target).

**Deployment targets**: selected per cluster by the `type` field — `kubernetes` (Kubernetes API) or
`docker-compose` (Docker host over SSH, Ansible applies Compose/Traefik stack). The job API and
`RunJob` recipe are target-agnostic.

**Extensibility**: Driver-based architecture supports further targets and build tools. Enterprise extensions
are mounted from a separate repository (`space-app-enterprise`), not in the main `space-app` repo.

## Code Architecture

```
appliance/
├── domain/         # Business logic — Object/, Recipe/, Contracts/, Loader/, Writer/, Query/
├── src/            # Application layer — Kernel.php only
├── infrastructures/ # Framework integrations — Doctrine/, Kubernetes/, AnsibleDockerCompose/, Symfony/
├── extensions/     # Extension system (e.g. Enterprise edition)
├── config/         # PHP-DI + Symfony config — di.*.php files, env-var driven
└── tests/          # PHPUnit unit tests + Behat behavioral tests
```

### Architectural Patterns

1. **Recipe Pattern**: Workflows composed of **Plans** (`domain/Recipe/Plan/`, 25 plans) and **Steps** (`domain/Recipe/Step/`, 17 categories). See [`.agents/recipes.md`](.agents/recipes.md) for details.
2. **DDD**: Clear separation between domain, application, and infrastructure layers.
3. **PHP-DI**: Dependency injection via `config/di.*.php` (11 config files). See [`documentation/architecture.md`](documentation/architecture.md).
4. **Extension System**: From Teknoo East Foundation — modify behavior without editing core code. Enterprise extensions are mounted from a separate repo.

## API & Routes

```
config/routes/api/v1/
├── unauthenticated/    # Public endpoints (login)
├── authenticated/      # User endpoints (JWT required): account, project, job, jwt, settings
└── admin/              # Admin endpoints: account, project, job, user
```

Web routes: 10 YAML files (`space.*.yaml`) in `config/routes/` with 48 `path:` entries total.

**JWT Auth**: Generate from WebUI account settings or `POST /api/v1/login`. Use `Authorization: Bearer {token}`.
Config via `SPACE_JWT_*` env vars. Templates: `.html.twig` (HTML) and `.json.twig` (API).

See [`documentation/api.md`](documentation/api.md) for full API docs; [`.agents/api.md`](.agents/api.md) for structure.

## Common Commands

All commands from project root via `./space.sh` or from `appliance/`.

```bash
# Install & Setup
./space.sh install              # Production install
./space.sh dev-install          # With dev dependencies
./space.sh config               # Interactive config wizard
./space.sh create-admin email=user@example.com password=secret

# Testing
./space.sh test                 # All tests (units + behavior, with coverage)
./space.sh units-tests           # Unit tests
./space.sh behavior-test         # Behat features
./space.sh qa                   # lint + phpstan + phpcs + audit

# Cache & Docker
./space.sh warmup               # Clear and warm up cache
./space.sh build && ./space.sh start

# Workers (async job processing)
bin/console messenger:consume new_task | execute_job | history_sent | job_done
```

## Development Guidelines

### Code Standards

- **PSR-12** (enforced via phpcs)
- **PHPStan** at max level (ignores in `phpstan.neon`)
- **90% test coverage** required
- All new features must include tests

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

`hotfix/` or `feature/` — never PR directly from `master`.

## Key Concepts

### Multi-tenancy Model

- **Account** — top-level entity (company/service/individual)
- **User** — human users belonging to accounts
- **Project** — Git repositories owned by accounts
- **Job** — represents a single deployment
- **Environment** — per-account cluster namespaces (Kubernetes) or compose namespaces (docker-compose)

### Deployment Flow

1. User creates Job → `new_task` worker prepares it
2. `execute_job` worker clones Git repo, runs PaaS compilation, builds images, deploys
3. `history_sent` / `job_done` workers persist results

### PaaS Compilation

Projects define deployments in `.paas.yaml`. The compiler: parses YAML → applies hooks
(composer, npm, pip, make, etc.) → builds OCI images → generates deployment manifests.
Platform-agnostic at domain level; platform-specific transcribers come from East PaaS.
Supports "extends" for reusable components via container libraries.

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
