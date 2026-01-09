# Agent Guidelines for Space

## Project Overview

**Space** is a Platform as a Service (PaaS) / Platform as a Code application providing continuous integration, 
delivery, and deployment capabilities. Built on **Teknoo East PaaS**, **Teknoo Kubernetes Client**, and **Symfony** 
components, it supports multi-account, multi-user, and multi-project deployments on clusters, including Kubernetes.

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
- **PHPUnit** - Unit testing framework
- **Behat** - Behavior-driven development (BDD) testing
  - `FriendsOfBehat/SymfonyExtension` - Symfony integration
  - `DMarynicz/BehatParallelExtension` - Parallel test execution
- **PHPStan** - Static analysis tool
- **PHP_CodeSniffer** - Code style checker (PSR-12)
- **Composer Audit** - Security vulnerability scanner

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
- Account Management (tenants, settings, access control)
- User Management (authentication, authorization, profiles)
- Project Management (applications, metadata, configurations)
- Job Management (deployment jobs lifecycle)
- Cluster Management (Kubernetes and cie clusters)
- Variable Management (persisted variables and secrets)

**Key Aggregates:**
- `Account` (with Users, Projects, Environments)
- `Project` (with Metadata, Variables)
- `Job` (deployment execution)

### Recipe Pattern

Workflows are implemented using the Recipe pattern from Teknoo East Foundation:
- **Plans**: High-level workflows combining multiple steps
- **Steps**: Individual operations implementing specific use cases
- **EditablePlan**: Dynamic plans modifiable through extensions

## API & Routes

### REST API Structure

The API is organized in versioned endpoints under `/api/v1/`:

```
config/routes/
├── api.yaml                    # Main API routing configuration
├── api/v1/
│   ├── unauthenticated/       # Public endpoints (login)
│   │   └── space.api.v1.login.yaml
│   ├── authenticated/         # User endpoints (requires JWT)
│   │   ├── space.api.v1.account.yaml
│   │   ├── space.api.v1.project.yaml
│   │   ├── space.api.v1.job.yaml
│   │   ├── space.api.v1.jwt.yaml
│   │   └── space.api.v1.settings.yaml
│   └── admin/                 # Admin endpoints
│       ├── space.api.v1.account.yaml
│       ├── space.api.v1.project.yaml
│       ├── space.api.v1.job.yaml
│       └── space.api.v1.user.yaml
```

### Web Routes Organization

Web routes are organized by domain in `config/routes/`:

- `space.account.yaml` - Account management
- `space.project.yaml` - Project management
- `space.job.yaml` - Job/deployment management
- `space.dashboard.yaml` - Dashboard views
- `space.settings.yaml` - User settings
- `space.subscription.yaml` - Subscription management
- `space.admin.*.yaml` - Admin interfaces
- `east.paas.overwrite.*.yaml` - East PaaS route overrides

### API Authentication

- **JWT Tokens**: Used for API authentication
- Tokens are configured via environment variables (`SPACE_JWT_*`)
- Users can manage API tokens through the settings interface
- JWT tokens can be generated from the WebUI in the user account or via Users API token after calling the
  `/api/v1/login` endpoint. 
  - API Tokens can be generated only from the WebUI in the user account.

### Twig for HTML and JSON Rendering

Twig is used for both HTML pages and JSON API responses:

**HTML Templates**: Standard Twig templates in `templates/`

**JSON API Templates**: Templates with `.json.twig` extension

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

1. **Always use `declare(strict_types=1);`**
2. **Full type declarations** for all parameters and return types
3. **Readonly properties** where applicable
4. **Immutable objects** using `Teknoo\Immutable` where possible
5. **Final classes** by default unless inheritance is required
6. **Constructor property promotion** for cleaner code

### Editor Configuration

- **Charset**: UTF-8
- **Line endings**: LF (Unix)
- **Indentation**: 4 spaces
- **Final newline**: Yes
- **Trailing whitespace**: Trimmed (except in `.md` files)
- **YAML files**: 2 spaces indentation

## Testing

### Test Requirements

- **90% code coverage** minimum for new contributions
- Any contribution must provide tests for additional introduced conditions
- Any unconfirmed issue needs a failing test case before being accepted

### Running Tests

```bash
# All tests with coverage
./space.sh test

# Tests without coverage
./space.sh test-without-coverage

# Unit tests only (PHPUnit)
./vendor/bin/phpunit

# Behavior tests (Behat)
./vendor/bin/behat
```

### Test Structure

- **Unit Tests**: `appliance/tests/` using PHPUnit
- **Behavior Tests**: `appliance/features/` using Behat (Gherkin syntax)

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
      | name       | My Project                           |
      | repository | https://github.com/example/repo.git |
    Then the project should be created
```

## Quality Assurance

### Running QA Checks

```bash
# Full QA suite
./space.sh qa

# Offline QA (no audit)
./space.sh qa-offline

# Individual checks
./space.sh lint      # PHP linting
./space.sh phpstan   # Static analysis
./space.sh phpcs     # Code style
./space.sh audit     # Security audit
```

### Fix Code Style

```bash
./vendor/bin/phpcbf
```

## Contributing

### Branch Strategy

- Pull requests must be sent from a new `hotfix/` or `feature/` branch
- Never submit PRs directly from `master`

### Contribution Checklist

1. ✅ Code follows PSR-12 style
2. ✅ All type declarations present
3. ✅ PHPStan passes without errors
4. ✅ Tests provided for new functionality
5. ✅ 90% code coverage maintained
6. ✅ Behavior tests for user-facing features

## Security

### Supported Versions

| Version | Supported |
|---------|-----------|
| 2.x     | ✅        |
| 1.2.x   | ✅        |
| < 1.2.x | ❌        |

### Reporting Vulnerabilities

Send an email to `richard@teknoo.software` with:
- Vulnerability description
- Proof of concept of the exploit

## CLI Tool (space.sh)

### Common Commands

```bash
# Installation
./space.sh install        # Production install
./space.sh dev-install    # Development install with dev dependencies
./space.sh update         # Update dependencies

# Docker
./space.sh build          # Build Docker images
./space.sh start          # Start Docker stack
./space.sh stop           # Stop Docker stack

# Configuration
./space.sh config         # Configure Space
./space.sh create-admin email=<email> password=<password>

# Extensions
./space.sh extension-list
./space.sh extension-enable name=<extension>
./space.sh extension-disable name=<extension>

# QA & Testing
./space.sh qa             # Run all QA checks
./space.sh test           # Run all tests
./space.sh phpstan        # Run PHPStan
./space.sh phpcs          # Check code style

# Maintenance
./space.sh clean          # Clean all caches and vendors
./space.sh warmup         # Clear and warm cache
```

## Extension System

Space supports extensions via Teknoo East Foundation:

- Add Symfony bundles
- Extend PHP-DI configuration
- Add/modify Recipe steps and plans
- Customize East PaaS compiler
- Add hooks for build/deployment
- Extend libraries (containers, pods, services, ingresses)
- Customize UI (templates, routes, menus, assets)
- Change branding (logo, CSS, JS)

Extensions are registered in `extensions/enabled.json`.

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
