# AGENTS.md

This file provides guidance to AI agents (Claude Code, Cursor, GitHub Copilot, etc.) when working
with code in this repository.

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

Space is a **Platform as a Service (PaaS)** application - a continuous integration/delivery/
deployment solution built on Teknoo East PaaS, Teknoo Kubernetes Client, and Symfony. It's a
multi-account, multi-users, multi-projects system that builds and deploys IT projects on
containerized platforms.

### Native Support

Space natively supports **Kubernetes** clusters and **Buildah** for OCI image building, but the
architecture is designed to be extensible. With appropriate drivers, Space can support other
deployment targets (e.g., Docker Swarm, Ansible) and build tools.

### Key Technologies

- PHP 8.4+
- Symfony 7.4+/8+
- Doctrine MongoDB ODM (for persistence)
- AMQP (RabbitMQ for inter-component communication)
- Mercure (for real-time web updates)
- Buildah (default OCI image builder)
- Kubernetes (default deployment target, via driver system)

---

## Code Architecture

### Directory Structure

The main application code is in the `appliance/` directory:

- **`domain/`** - Domain layer (business logic, core models)
  - `Object/` - Domain entities and value objects
  - `Recipe/` - Recipe-based workflow definitions (Plans and Steps)
  - `Contracts/` - Interfaces and contracts
  - `Loader/`, `Writer/`, `Query/` - Data access patterns
  - `Service/` - Domain services
  - `Middleware/` - Middleware components

- **`src/`** - Application layer (minimal, contains only Kernel.php)

- **`infrastructures/`** - Infrastructure layer (framework integrations)
  - `Doctrine/` - Database/ODM implementations
  - `Kubernetes/` - Kubernetes client integrations
  - `Symfony/` - Symfony-specific code (forms, controllers, etc.)
  - `Twig/` - Template extensions

- **`extensions/`** - Extension system for adding features (e.g., Enterprise edition)

- **`config/`** - PHP-DI and Symfony configuration
  - `di.*.php` files define dependency injection containers
  - Configuration is heavily environment-variable driven

- **`tests/`** - Test suites (PHPUnit unit tests + Behat behavioral tests)

### Architectural Patterns

1. **Recipe Pattern**: Space uses the Teknoo Recipe pattern extensively. Recipes are workflow
   definitions composed of:
   - **Plans** - High-level workflow orchestration (in `domain/Recipe/Plan/`)
   - **Steps** - Individual workflow steps (in `domain/Recipe/Step/`)

2. **DDD (Domain-Driven Design)**: Clear separation between domain, application, and
   infrastructure layers

3. **Immutability**: Uses Teknoo/Immutable and Teknoo/States for immutable objects

4. **PHP-DI**: Dependency injection configured via PHP files in `config/di.*.php`

5. **Extension System**: Provided by Teknoo East Foundation, allows modifying behavior without
   editing core code

---

## API & Routes

### REST API Structure

The API is organized in versioned endpoints under `/api/v1/`:

```
config/routes/api/v1/
├── unauthenticated/       # Public endpoints (login)
│   └── space.api.v1.login.yaml
├── authenticated/         # User endpoints (requires JWT)
│   ├── space.api.v1.account.yaml
│   ├── space.api.v1.project.yaml
│   ├── space.api.v1.job.yaml
│   ├── space.api.v1.jwt.yaml
│   └── space.api.v1.settings.yaml
└── admin/                 # Admin endpoints
    ├── space.api.v1.account.yaml
    ├── space.api.v1.project.yaml
    ├── space.api.v1.job.yaml
    └── space.api.v1.user.yaml
```

### Web Routes Organization

Web routes are organized by domain in `config/routes/`:

| Route File                   | Purpose                   |
|------------------------------|---------------------------|
| `space.account.yaml`         | Account management        |
| `space.project.yaml`         | Project management        |
| `space.job.yaml`             | Job/deployment management |
| `space.dashboard.yaml`       | Dashboard views           |
| `space.settings.yaml`        | User settings             |
| `space.subscription.yaml`    | Subscription management   |
| `space.admin.*.yaml`         | Admin interfaces          |
| `east.paas.overwrite.*.yaml` | East PaaS route overrides |

### API Authentication

**JWT Token Flow:**

1. **Generate tokens** from WebUI in user account settings, or
2. **Call** `/api/v1/login` endpoint with user credentials
3. **Use token** in HTTP Authorization header: `Bearer {token}`

**Configuration:**

- Tokens are configured via environment variables prefixed with `SPACE_JWT_*`
- Token regeneration available at `/api/v1/jwt/create-token`
- API Keys can only be generated from the WebUI

### Rendering with Twig

| Template Type | Extension    | Location                 |
|---------------|--------------|--------------------------|
| **HTML**      | `.html.twig` | `appliance/templates/`   |
| **JSON API**  | `.json.twig` | `appliance/templates/`   |

---

## Common Commands

All commands should be run from the `appliance/` directory, or use the `./space.sh` script from
the project root.

### Installation & Setup

```bash
./space.sh install              # Production install (no dev dependencies)
./space.sh dev-install          # Development install (with dev dependencies)
./space.sh update               # Update dependencies (production)
./space.sh dev-update           # Update dependencies (development)
./space.sh config               # Interactive configuration wizard
./space.sh create-admin email=user@example.com password=secret
```

### Testing

```bash
./space.sh test                 # Full test suite with coverage (multi-threaded)
./space.sh test-without-coverage # Faster tests without coverage
make test-mono-thread           # Single-threaded tests (from appliance/)
```

**Test types:**
- PHPUnit unit tests: `vendor/bin/phpunit -c phpunit.xml`
- Behat behavioral tests: `vendor/bin/behat`
- Multi-threaded execution via `NB_THREADS` env var (default: 4)

**Running a single test:**

```bash
cd appliance
vendor/bin/phpunit tests/path/to/TestFile.php
vendor/bin/behat features/path/to/feature.feature
```

### Quality Assurance

```bash
./space.sh qa                   # Full QA suite (lint, phpstan, phpcs, audit)
./space.sh qa-offline           # QA without composer audit
./space.sh lint                 # PHP syntax check
./space.sh phpstan              # Static analysis (max level)
./space.sh phpcs                # PSR-12 code style check
./space.sh audit                # Security audit
```

### Cache & Cleanup

```bash
./space.sh clean                # Remove vendors, cache, logs
./space.sh warmup               # Clear and warm up cache
```

### Docker Development

```bash
./space.sh build                # Build Docker images
./space.sh start                # Start Docker stack
./space.sh stop                 # Stop Docker stack
./space.sh restart              # Restart Docker stack
```

### Extensions

```bash
./space.sh extension-list       # List available extensions
./space.sh extension-enable name=ExtensionName
./space.sh extension-disable name=ExtensionName
```

### Worker Commands

Workers handle asynchronous job processing:

```bash
bin/console messenger:consume new_job       # Prepare new deployment jobs
bin/console messenger:consume history_sent  # Persist job histories
bin/console messenger:consume job_done      # Persist final job results
bin/console messenger:consume execute_job   # Execute deployment jobs
```

---

## Development Guidelines

### Code Standards

- **PSR-12** coding standard (enforced via phpcs)
- **PHPStan** at max level (with specific ignores in phpstan.neon)
- **90% test coverage** required for new contributions
- All new features must include tests

### Code Style Rules

```php
<?php

declare(strict_types=1);

namespace Teknoo\Space\Domain\Object;

use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

final class Example implements ImmutableInterface
{
    use ImmutableTrait;

    public function __construct(
        private readonly string $name,
        private readonly int $value,
    ) {
        $this->uniqueConstructorCheck();
    }

    public function getName(): string
    {
        return $this->name;
    }
}
```

### Key Conventions

| Convention              | Requirement                                         |
|-------------------------|-----------------------------------------------------|
| **Strict typing**       | Always use `declare(strict_types=1);`               |
| **Type declarations**   | Full type hints for all parameters and return types |
| **Readonly properties** | Use `readonly` where applicable                     |
| **Immutability**        | Use `Teknoo\Immutable` pattern where possible       |
| **Property promotion**  | Use constructor property promotion                  |

### Editor Configuration

| Setting                 | Value                        |
|-------------------------|------------------------------|
| **Charset**             | UTF-8                        |
| **Line endings**        | LF (Unix)                    |
| **Indentation (PHP)**   | 4 spaces                     |
| **Indentation (YAML)**  | 2 spaces                     |
| **Final newline**       | Yes                          |
| **Trailing whitespace** | Trimmed (except `.md` files) |

### Working with Recipes

When adding new workflow logic:

1. Create Steps in `domain/Recipe/Step/` (granular operations)
2. Compose Plans in `domain/Recipe/Plan/` (workflow orchestration)
3. Register in `config/di.recipe.*.php`

### Configuration System

Configuration uses environment variables extensively. Key files:

- `config/di.variables.from.envs.php` - Maps env vars to DI container
- `.env.local` - Local environment configuration (not committed)
- `.env.local.dist` - Template for local config

### Testing Approach

- **Unit tests**: Test domain logic in isolation
- **Behat tests**: Test full workflows and user scenarios
- Use `APP_ENV=test` for test execution
- JWT keypair auto-generated for tests (from `appliance/` directory):
  `bin/console lexik:jwt:generate-keypair --skip-if-exists`

### Working with Deployment Targets

Space uses a **driver-based architecture** for deployment targets. While Kubernetes is natively
supported and the primary target, the system is designed to support other platforms through
custom drivers.

**Kubernetes (native support):**

- Each account gets its own namespace (prefixed with `SPACE_KUBERNETES_ROOT_NAMESPACE`)
- Registry namespaces separate (prefixed with `SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE`)
- Kubernetes transcribers in `infrastructures/Kubernetes/` convert PaaS models to K8s resources

**Extensibility:**

With appropriate driver development, Space can deploy to Docker Swarm, Ansible, or other
orchestration platforms.

### Extension Development

Extensions allow customizing Space without modifying core:

- Can add Symfony bundles, routes, templates
- Can decorate Recipe Plans/Steps
- Can extend PaaS compiler (hooks, container libraries, etc.)
- Configure via `TEKNOO_EAST_EXTENSION_*` environment variables
- Enterprise edition is implemented as an extension

---

## Key Concepts

### Multi-tenancy Model

- **Account** - Top-level entity (company, service, individual)
- **User** - Human users belonging to accounts
- **Project** - Git repositories owned by accounts
- **Job** - Represents a single deployment
- **Environment** - Cluster namespaces per account

### Deployment Flow

1. User creates Job → `new_job` worker prepares it
2. `execute_job` worker clones Git repo, runs PaaS compilation
3. OCI images built (default: Buildah, configurable via `SPACE_IMG_BUILDER_CMD`)
4. Resources deployed to cluster (default: Kubernetes, extensible via drivers)
5. `history_sent` and `job_done` workers persist results

### PaaS Compilation

Projects define deployments in `.paas.yaml` files. The compiler:

- Parses YAML configuration
- Applies hooks (composer, npm, pip, make, etc.)
- Builds OCI images (via configurable builder tool)
- Generates deployment manifests for target platform (Kubernetes by default, extensible via
  drivers)
- Supports "extends" mechanism for reusable components (BigBang library in Enterprise)

The compilation process is **platform-agnostic** at the domain level, with platform-specific
transcribers handling the actual resource generation.

---

## Contributing

### Branch Strategy

- ✅ Create PRs from `hotfix/` or `feature/` branches
- ❌ Never submit PRs directly from `master`

### Contribution Checklist

| Requirement                      | Status     |
|----------------------------------|------------|
| Code follows PSR-12 style        | ✅ Required |
| All type declarations present    | ✅ Required |
| PHPStan passes (level max)       | ✅ Required |
| Tests for new functionality      | ✅ Required |
| 90% code coverage maintained     | ✅ Required |
| Behavior tests for user features | ✅ Required |

### Code Examples

Detailed code examples are available in a separate file for better readability:

**See [.agents/EXAMPLES.md](.agents/EXAMPLES.md)** for complete examples including:

- [Extension implementation](.agents/EXAMPLES.md#extension-example)
- [Teknoo States pattern](.agents/EXAMPLES.md#teknoo-states-example)
- [Recipe Plan](.agents/EXAMPLES.md#recipe-plan-example)
- [Recipe Step](.agents/EXAMPLES.md#recipe-step-example)

---

## Workflow Orchestration

**Note**: This section references optional project files (`.agents/tasks/*.md`) and required feedback files (`.agents/feedback/*.md`). The feedback system is mandatory for knowledge retention across sessions; task tracking files are optional per-session tools.

### 0. Session Start

- **Always read** `.agents/feedback/INDEX.md` to learn from past challenges
- Review recent feedback entries (last 5-10) for common patterns
- Check if any documented issues are relevant to your current task
- Check `.agents/tasks/lessons.md` if it exists for project-specific quick reference
- Apply lessons learned to avoid repeating past mistakes

### 1. Plan Mode Default

- Enter plan mode for ANY non-trivial task (3+ steps or architectural decisions)
- If something goes sideways, STOP and re-plan immediately - don't keep pushing
- Use plan mode for verification steps, not just building
- Write detailed specs upfront to reduce ambiguity

### 2. Subagent Strategy

Keep main context window clean:

- Offload research, exploration, and parallel analysis to subagents
- For complex problems, throw more compute at it via subagents
- One task per subagent for focused execution

### 3. Self-Improvement Loop

- After ANY correction from the user: document the pattern in `.agents/feedback/`
- For project-specific patterns: optionally maintain `.agents/tasks/lessons.md` for quick reference
- Write rules for yourself that prevent the same mistake
- Ruthlessly iterate on these lessons until mistake rate drops
- Review `.agents/feedback/INDEX.md` at session start for all projects

### 4. Verification Before Done

- Never mark a task complete without proving it works
- Diff behavior between main and your changes when relevant
- Ask yourself: "Would a staff engineer approve this?"
- Run tests, check logs, demonstrate correctness

### 5. Demand Elegance (Balanced)

- For non-trivial changes: pause and ask "is there a more elegant way?"
- If a fix feels hacky: "Knowing everything I know now, implement the elegant solution"
- Skip this for simple, obvious fixes - don't over-engineer
- Challenge your own work before presenting it

### 6. Autonomous Bug Fixing

- When given a bug report: just fix it. Don't ask for hand-holding
- Point at logs, errors, failing tests → then resolve them
- Zero context switching required from the user
- Go fix failing CI tests without being told how

---

## Task Management

1. **Plan First**: Write plan to `.agents/tasks/todo.md` with checkable items (optional, session-specific)
2. **Verify Plan**: Check in before starting implementation
3. **Track Progress**: Mark items complete as you go
4. **Explain Changes**: High-level summary at each step
5. **Document Results**: Add review to `.agents/tasks/todo.md` (if used)
6. **Capture Lessons**: Write feedback to `.agents/feedback/` (required) and optionally maintain `.agents/tasks/lessons.md` for quick project-specific reference

---

## Feedback Loop

When completing a task, **always**:

1. Write feedback summary to `.agents/feedback/{YYYY-MM-DD-task-name}.md`
2. Update `.agents/feedback/INDEX.md` with the new entry

**Purpose**: The `.agents/feedback/` directory serves as a persistent knowledge base that improves future task execution across all agents and sessions. Unlike `.agents/tasks/lessons.md` (which is optional and session/project-specific), feedback entries are required and provide structured historical context.

### What to Document

- **Missing Context**: What information/precision was missing that would have helped?
- **Assumptions Made**: What did you assume that needed clarification?
- **Blockers Encountered**: What slowed you down or required user intervention?
- **Improvement Suggestions**: How could the instructions/codebase be clearer?
- **Lessons Learned**: Patterns or gotchas discovered during implementation

### Feedback File Format

```markdown
# Feedback: [Task Name] - YYYY-MM-DD

## Task Summary
Brief description of what was accomplished

## Missing Precision
- [ ] Needed: More details about X
- [ ] Unclear: Whether to use approach A or B
- [ ] Assumed: Default behavior should be Y

## Blockers
- Configuration file location not documented
- Required environment variables not listed

## Suggestions
- Add section to AGENTS.md about X
- Document Y pattern in architecture section
```

### Updating the Index

After creating a feedback file, add an entry to `.agents/feedback/INDEX.md`:

```markdown
### YYYY-MM-DD - [Task Name](YYYY-MM-DD-task-name.md)
**Status**: ⚠️ Needs attention / ✅ Resolved / 📝 Documented

Brief one-line summary of key missing precision or lesson learned.
```

This feedback helps improve future task execution and documentation quality.

---

## Core Principles

- **Simplicity First**: Make every change as simple as possible. Impact minimal code.
- **No Laziness**: Find root causes. No temporary fixes. Senior developer standards.
- **Minimal Impact**: Changes should only touch what's necessary. Avoid introducing bugs.
