# Agent Guidelines for Space

## Table of Contents

- [Project Overview](#project-overview)
- [Technology Stack](#technology-stack)
    - [Backend](#backend)
    - [Infrastructure](#infrastructure)
    - [Frontend](#frontend)
    - [Development & QA Tools](#development--qa-tools)
- [Architecture](#architecture)
    - [Hexagonal Architecture](#hexagonal-architecture-ports--adapters)
    - [Domain-Driven Design](#domain-driven-design-ddd)
    - [Recipe Pattern](#recipe-pattern)
- [API & Routes](#api--routes)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Quality Assurance](#quality-assurance)
- [Contributing](#contributing)
- [Security](#security)
- [CLI Tool (space.sh)](#cli-tool-spacesh)
- [Extension System](#extension-system)
- [Code Examples](#code-examples)
- [Key Design Patterns](#key-design-patterns)
- [Contact](#contact)

---

## Project Overview

**Space** is a Platform as a Service (PaaS) / Platform as a Code application providing continuous integration,
delivery, and deployment capabilities. Built on **Teknoo East PaaS**, **Teknoo Kubernetes Client**, and **Symfony**
components, it supports multi-account, multi-user, and multi-project deployments on clusters, including Kubernetes.

### Quick Start

| For                                | See Section                           |
|------------------------------------|---------------------------------------|
| **Understanding the architecture** | [Architecture](#architecture)         |
| **Writing code**                   | [Coding Standards](#coding-standards) |
| **Running tests**                  | [Testing](#testing)                   |
| **Using the CLI**                  | [CLI Tool](#cli-tool-spacesh)         |
| **Building extensions**            | [Extension System](#extension-system) |
| **Code examples**                  | [Code Examples](#code-examples)       |

## Technology Stack

### Backend

- **PHP 8.4+** with strict typing
- **Symfony 8+** (or 7.4+) framework
- **Doctrine ODM 3.5+** for MongoDB persistence
- **Teknoo Libraries**:
    - `teknoo/east-paas` - PaaS orchestration engine
    - `teknoo/kubernetes-client` - Kubernetes API integration
    - `teknoo/recipe` - Workflow orchestration (Recipe pattern)
    - `teknoo/states` - State pattern implementation
    - `teknoo/immutable` - Immutable object pattern
    - `teknoo/east-foundation` - Extension system and Recipe pattern
    - `teknoo/east-common` - Shared components

### Infrastructure

- **MongoDB** - Primary database
- **RabbitMQ (AMQP)** - Message broker for worker communication
- **Mercure** - Real-time updates via Server-Sent Events
- **Buildah** - OCI image builder
- **Kubernetes 1.30+** - Container orchestration

### Frontend

- **Twig** - Server-side templating for HTML and JSON API responses
- **Symfony Forms** - Form generation and validation

### Development & QA Tools

| Tool                | Purpose                                   |
|---------------------|-------------------------------------------|
| **PHPUnit**         | Unit testing framework                    |
| **Behat**           | Behavior-driven development (BDD) testing |
| **PHPStan**         | Static analysis tool (level max)          |
| **PHP_CodeSniffer** | Code style checker (PSR-12)               |
| **Composer Audit**  | Security vulnerability scanner            |

**Behat Extensions:**

- `FriendsOfBehat/SymfonyExtension` - Symfony integration
- `DMarynicz/BehatParallelExtension` - Parallel test execution

## Architecture

### Hexagonal Architecture (Ports & Adapters)

The project follows a clean hexagonal architecture with clear separation:

```
appliance/
├── domain/              # Domain Layer (DDD)
│   ├── Contracts/       # Interfaces/Ports
│   ├── Object/          # Entities, DTOs, Value Objects
│   ├── Query/           # Query objects
│   └── Recipe/          # Workflows (Plans and Steps)
├── infrastructures/     # Infrastructure Layer (Adapters)
│   ├── Doctrine/        # ODM repositories
│   ├── Kubernetes/      # K8s integration
│   ├── Symfony/         # Symfony adapters
│   └── Twig/            # Template extensions
├── src/                 # Application Layer
├── config/              # Configuration
│   ├── doctrine/        # ODM mappings (XML)
│   ├── packages/        # Bundle configs
│   ├── routes/          # Routing
│   └── serializer/      # Serialization
├── templates/           # Twig templates
└── tests/               # Tests
```

### Domain-Driven Design (DDD)

**Bounded Contexts:**

| Context                 | Responsibilities                        |
|-------------------------|-----------------------------------------|
| **Account Management**  | Tenants, settings, access control       |
| **User Management**     | Authentication, authorization, profiles |
| **Project Management**  | Applications, metadata, configurations  |
| **Job Management**      | Deployment jobs lifecycle               |
| **Cluster Management**  | Kubernetes and other clusters           |
| **Variable Management** | Persisted variables and secrets         |

**Key Aggregates:**

- `Account` - Root aggregate containing Users, Projects, and Environments
- `Project` - Contains Metadata and Variables
- `Job` - Manages deployment execution lifecycle

### Recipe Pattern

Workflows are implemented using the Recipe pattern from Teknoo East Foundation:

- **Plans**: High-level workflows combining multiple steps
- **Steps**: Individual operations implementing specific use cases
- **EditablePlan**: Dynamic plans modifiable through extensions

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

| Template Type | Extension    | Location     |
|---------------|--------------|--------------|
| **HTML**      | `.html.twig` | `templates/` |
| **JSON API**  | `.json.twig` | `templates/` |

## Coding Standards

### PHP Standards

- **PSR-12** coding style (checked with PHP_CodeSniffer)
- **PSR-4** autoloading
- **PHPStan** for static analysis (level max)

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
| **Final classes**       | By default, unless inheritance is required          |
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

## Testing

### Test Requirements

| Requirement         | Description                                 |
|---------------------|---------------------------------------------|
| **Code Coverage**   | Minimum 90% for new contributions           |
| **Test Conditions** | All additional conditions must be tested    |
| **Bug Reports**     | Unconfirmed issues need a failing test case |

### Running Tests

| Command                            | Purpose                    |
|------------------------------------|----------------------------|
| `./space.sh test`                  | All tests with coverage    |
| `./space.sh test-without-coverage` | All tests without coverage |

### Test Structure

| Type               | Location              | Framework       |
|--------------------|-----------------------|-----------------|
| **Unit Tests**     | `appliance/tests/`    | PHPUnit         |
| **Behavior Tests** | `appliance/features/` | Behat (Gherkin) |

### Writing Unit Tests

```php
namespace Teknoo\Space\Tests\Unit\Domain\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\Space\Domain\Object\MyClass;

class MyClassTest extends TestCase
{
    public function testConstructor(): void
    {
        $object = new MyClass('value');
        
        self::assertInstanceOf(MyClass::class, $object);
    }
}
```

### Writing Behavior Tests

```gherkin
Feature: Project Management
  As a user
  I want to manage projects
  So that I can deploy applications

  Scenario: Create a project
    Given I am logged in as "user@example.com"
    When I create a project with:
      | name       | My Project                          |
      | repository | https://github.com/example/repo.git |
    Then the project should be created
```

## Quality Assurance

### Running QA Checks

| Command                 | Description                 |
|-------------------------|-----------------------------|
| `./space.sh qa`         | Full QA suite (all checks)  |
| `./space.sh qa-offline` | QA suite without audit      |
| `./space.sh lint`       | PHP syntax linting          |
| `./space.sh phpstan`    | Static analysis (level max) |
| `./space.sh phpcs`      | Code style check (PSR-12)   |
| `./space.sh audit`      | Security vulnerability scan |
| `./vendor/bin/phpcbf`   | Auto-fix code style issues  |

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

## Security

### Supported Versions

| Version | Supported |
|---------|-----------|
| 2.x     | ✅         |
| 1.2.x   | ✅         |
| < 1.2.x | ❌         |

### Reporting Vulnerabilities

Send an email to `richard@teknoo.software` with:

- Vulnerability description
- Proof of concept of the exploit

## CLI Tool (space.sh)

All commands are executed via `./space.sh <command>` from the project root.

### Command Reference

| Category          | Command                 | Description                                                  |
|-------------------|-------------------------|--------------------------------------------------------------|
| **Installation**  | `install`               | Production install (no dev dependencies)                     |
|                   | `dev-install`           | Development install (with dev dependencies)                  |
|                   | `update`                | Update dependencies                                          |
| **Docker**        | `build`                 | Build Docker images                                          |
|                   | `start`                 | Start Docker stack                                           |
|                   | `stop`                  | Stop Docker stack                                            |
|                   | `restart`               | Restart Docker stack                                         |
| **Configuration** | `config`                | Configure Space                                              |
|                   | `create-admin`          | Create admin user (requires `email=<email> password=<pass>`) |
| **Extensions**    | `extension-list`        | List available extensions                                    |
|                   | `extension-enable`      | Enable extension (requires `name=<extension>`)               |
|                   | `extension-disable`     | Disable extension (requires `name=<extension>`)              |
| **QA & Testing**  | `qa`                    | Run all QA checks                                            |
|                   | `qa-offline`            | Run QA without audit                                         |
|                   | `test`                  | Run all tests with coverage                                  |
|                   | `test-without-coverage` | Run all tests without coverage                               |
|                   | `phpstan`               | Run PHPStan static analysis                                  |
|                   | `phpcs`                 | Check code style                                             |
|                   | `lint`                  | Check PHP syntax                                             |
|                   | `audit`                 | Security vulnerability scan                                  |
| **Maintenance**   | `clean`                 | Clean caches and vendors                                     |
|                   | `warmup`                | Clear and warm cache                                         |

## Extension System

Space supports extensions via Teknoo East Foundation.

### Extension Capabilities

| Capability               | Description                                  |
|--------------------------|----------------------------------------------|
| **Symfony Bundles**      | Add new bundles to the application           |
| **PHP-DI Configuration** | Extend dependency injection configuration    |
| **Recipe Plans & Steps** | Add or modify workflow orchestration         |
| **East PaaS Compiler**   | Customize the PaaS compilation process       |
| **Build Hooks**          | Add hooks for build and deployment phases    |
| **Libraries**            | Extend containers, pods, services, ingresses |
| **UI Customization**     | Modify templates, routes, menus, assets      |
| **Branding**             | Change logo, CSS, and JavaScript             |

**Registration:** Extensions are registered in `extensions/enabled.json`.

> 📖 **See [.agents/EXAMPLES.md](.agents/EXAMPLES.md#extension-example)** for complete extension implementation examples.

## Code Examples

Detailed code examples are available in a separate file for better readability:

> 📖 **See [.agents/EXAMPLES.md](.agents/EXAMPLES.md)** for complete examples including:
> - [Extension implementation](.agents/EXAMPLES.md#extension-example)
> - [Teknoo States pattern](.agents/EXAMPLES.md#teknoo-states-example)
> - [Recipe Plan](.agents/EXAMPLES.md#recipe-plan-example)
> - [Recipe Step](.agents/EXAMPLES.md#recipe-step-example)

## Key Design Patterns

1. **Hexagonal Architecture** - Ports and adapters separation
2. **Domain-Driven Design** - Bounded contexts, aggregates, repositories
3. **Recipe Pattern** - Composable workflow orchestration
4. **Repository Pattern** - Data access abstraction
5. **Factory Pattern** - Object creation
6. **Strategy Pattern** - Different cluster types, compilers
7. **State Pattern** - Teknoo States library
8. **Immutable Pattern** - Teknoo Immutable library
9. **Dependency Injection** - PHP-DI container
10. **CQRS-like** - Separate Query objects from Commands

## Contact

- **Author**: Richard Déloge
- **Email**: richard@teknoo.software
- **Issues**: https://github.com/TeknooSoftware/space-app/issues
- **Support**: contact@teknoo.software
