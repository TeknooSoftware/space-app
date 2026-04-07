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

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Code Architecture](#code-architecture)
3. [API & Routes](#api--routes)
4. [Common Commands](#common-commands)
5. [Development Guidelines](#development-guidelines)
6. [Key Concepts](#key-concepts)
7. [Contributing](#contributing)
8. [Workflow Orchestration](#workflow-orchestration)
9. [Task Management](#task-management)
10. [Feedback Loop](#feedback-loop)
11. [Core Principles](#core-principles)

---

## Project Overview

Space is a **Platform as a Service (PaaS)** application — a CI/CD/deployment solution built on Teknoo East PaaS,
Teknoo Kubernetes Client, and Symfony. Multi-account, multi-users, multi-projects system that builds and deploys
IT projects on containerized platforms.

**Key Technologies**: PHP 8.4+, Symfony 7.4+/8+, Doctrine MongoDB ODM, AMQP (RabbitMQ), Mercure,
Buildah (OCI image builder), Kubernetes (default deployment target via driver system).

**Extensibility**: Driver-based architecture supports other targets (Docker Swarm, Ansible) and build tools.

## Code Architecture

```
appliance/
├── domain/         # Business logic — Object/, Recipe/, Contracts/, Loader/, Writer/, Query/, Service/, Middleware/
├── src/            # Application layer — Kernel.php only
├── infrastructures/ # Framework integrations — Doctrine/, Kubernetes/, Symfony/, Twig/
├── extensions/     # Extension system (e.g. Enterprise edition)
├── config/         # PHP-DI + Symfony config — di.*.php files, env-var driven
└── tests/          # PHPUnit unit tests + Behat behavioral tests
```

### Architectural Patterns

1. **Recipe Pattern**: Workflows composed of **Plans** (`domain/Recipe/Plan/`) and **Steps** (`domain/Recipe/Step/`)
2. **DDD**: Clear separation between domain, application, and infrastructure layers
3. **Immutability**: Uses Teknoo/Immutable and Teknoo/States
4. **PHP-DI**: Dependency injection via `config/di.*.php`
5. **Extension System**: From Teknoo East Foundation — modify behavior without editing core code

## API & Routes

```
config/routes/api/v1/
├── unauthenticated/    # Public endpoints (login)
├── authenticated/      # User endpoints (JWT required): account, project, job, jwt, settings
└── admin/              # Admin endpoints: account, project, job, user
```

Web routes in `config/routes/`: `space.account.yaml`, `space.project.yaml`, `space.job.yaml`,
`space.dashboard.yaml`, `space.settings.yaml`, `space.subscription.yaml`, `space.admin.*.yaml`,
`east.paas.overwrite.*.yaml`.

**JWT Auth**: Generate from WebUI account settings or `POST /api/v1/login`. Use `Authorization: Bearer {token}`.
Config via `SPACE_JWT_*` env vars. Templates: `.html.twig` (HTML) and `.json.twig` (API) in `appliance/templates/`.

## Common Commands

All commands from project root via `./space.sh` or from `appliance/`.

```bash
# Install & Setup
./space.sh install              # Production install
./space.sh dev-install          # With dev dependencies
./space.sh config               # Interactive config wizard
./space.sh create-admin email=user@example.com password=secret

# Testing
./space.sh test                 # Full suite with coverage (multi-threaded)
./space.sh test-without-coverage
vendor/bin/phpunit tests/path/to/TestFile.php
vendor/bin/behat features/path/to/feature.feature

# Quality Assurance
./space.sh qa                   # lint + phpstan + phpcs + audit
./space.sh phpstan
./space.sh phpcs

# Cache
./space.sh warmup               # Clear and warm up cache

# Docker
./space.sh build && ./space.sh start

# Extensions
./space.sh extension-list
./space.sh extension-enable name=ExtensionName

# Workers (async job processing)
bin/console messenger:consume new_job        # Prepare deployments
bin/console messenger:consume execute_job    # Execute deployments
bin/console messenger:consume history_sent   # Persist histories
bin/console messenger:consume job_done       # Persist final results
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
| Immutability        | Use `Teknoo\Immutable` pattern where possible  |
| Property promotion  | Use constructor property promotion             |

### Editor Config

| Setting             | Value                  |
|---------------------|------------------------|
| Charset             | UTF-8                  |
| Line endings        | LF                     |
| PHP indent          | 4 spaces               |
| YAML indent         | 2 spaces               |
| Final newline       | Yes                    |
| Trailing whitespace | Trimmed (except `.md`) |

### Working with Recipes

1. Create Steps in `domain/Recipe/Step/` (granular operations)
2. Compose Plans in `domain/Recipe/Plan/` (workflow orchestration)
3. Register in `config/di.recipe.*.php`

See [.agents/EXAMPLES.md](.agents/EXAMPLES.md) for Plan and Step examples.

### Configuration System

- `config/di.variables.from.envs.php` — maps env vars to DI container
- `.env.local` — local config (not committed); `.env.local.dist` — template

### Testing

- Unit tests: test domain logic in isolation; Behat: full workflow/user scenarios
- `APP_ENV=test` for test execution
- JWT keypair for tests: `bin/console lexik:jwt:generate-keypair --skip-if-exists`

### Extension Development

- Can add Symfony bundles, routes, templates; decorate Recipe Plans/Steps
- Can extend PaaS compiler (hooks, container libraries)
- Configure via `TEKNOO_EAST_EXTENSION_*` env vars
- See [.agents/EXAMPLES.md#extension-example](.agents/EXAMPLES.md#extension-example)

### Contribution Requirements

- PSR-12 style ✅ · Type declarations ✅ · PHPStan max ✅
- Tests for new functionality ✅ · 90% coverage ✅ · Behat for user features ✅
- Branches: `hotfix/` or `feature/` — never PR directly from `master`

## Key Concepts

### Multi-tenancy Model

- **Account** — top-level entity (company/service/individual)
- **User** — human users belonging to accounts
- **Project** — Git repositories owned by accounts
- **Job** — represents a single deployment
- **Environment** — cluster namespaces per account

### Deployment Flow

1. User creates Job → `new_job` worker prepares it
2. `execute_job` worker clones Git repo, runs PaaS compilation
3. OCI images built (default: Buildah, configurable via `SPACE_IMG_BUILDER_CMD`)
4. Resources deployed to cluster (default: Kubernetes, extensible via drivers)
5. `history_sent` / `job_done` workers persist results

### PaaS Compilation

Projects define deployments in `.paas.yaml`. The compiler: parses YAML → applies hooks
(composer, npm, pip, make, etc.) → builds OCI images → generates deployment manifests.
Platform-agnostic at domain level; platform-specific transcribers in `infrastructures/Kubernetes/`.
Supports "extends" for reusable components (BigBang library in Enterprise).

## Workflow Orchestration

### 0. Session Start

- Read `.agents/feedback/INDEX.md` — learn from past challenges
- Check `.agents/tasks/lessons.md` if it exists — project-specific quick reference
- Apply lessons to avoid past mistakes

### 1. Plan Mode Default

- Enter plan mode for any non-trivial task (3+ steps or architectural decisions)
- If something goes sideways: STOP and re-plan — don't keep pushing
- Use plan mode for verification steps, not just building

### 2. Subagent Strategy

- Offload research, exploration, and parallel analysis to subagents
- One task per subagent for focused execution; throw more compute at complex problems

### 3. Self-Improvement Loop

- After any user correction: document pattern in `.agents/feedback/`
- Optionally maintain `.agents/tasks/lessons.md` for quick project-specific reference
- Review `.agents/feedback/INDEX.md` at every session start

### 4. Verification Before Done

- Never mark a task complete without proving it works
- Run tests, check logs, demonstrate correctness
- Ask: "Would a staff engineer approve this?"

### 5. Demand Elegance (Balanced)

- For non-trivial changes: pause and ask "is there a more elegant way?"
- Skip for simple obvious fixes — don't over-engineer

### 6. Autonomous Bug Fixing

- When given a bug report: just fix it — no hand-holding needed
- Point at logs, errors, failing tests → resolve them

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
