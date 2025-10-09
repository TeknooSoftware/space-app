# Development Guide

## Overview

This guide is for developers who want to contribute to Space, extend its functionality, or understand the codebase for 
customization purposes.

## Getting Started

### Prerequisites

- PHP 8.4+
- Composer 2.0+
- Docker and Docker Compose (recommended)
- Git

### Development Environment Setup

#### Using Docker Compose (Recommended)

1. **Clone the repository**:
   ```bash
   git clone https://github.com/TeknooSoftware/space-app.git
   cd space-app
   ```

2. **Copy Docker Compose override**:
   ```bash
   cp compose.override.yml.dist compose.override.yml
   ```

3. **Start development environment**:
   ```bash
   ./space.sh build
   ./space.sh start
   ```

4. **Install dependencies with dev requirements**:
   ```bash
   ./space.sh dev-install
   ```

5. **Create admin user**:
   ```bash
   ./space.sh create-admin email=dev@example.com password=dev123
   ```

6. **Access the application**:
   - Web UI: http://localhost
   - RabbitMQ Management: http://localhost:15672 (guest/guest)
   - MongoDB: localhost:27017

#### Local Setup (Without Docker)

1. **Install system dependencies** (see [Requirements](requirements.md))

2. **Clone repository**:
   ```bash
   git clone https://github.com/TeknooSoftware/space-app.git
   cd space-app
   ```

3. **Install PHP dependencies**:
   ```bash
   ./space.sh dev-install
   ```

4. **Configure environment**:
   ```bash
   cp appliance/.env appliance/.env.local
   # Edit .env.local with your local settings
   ```

5. **Start services**:
   ```bash
   # MongoDB
   mongod --dbpath=/path/to/data
   
   # RabbitMQ
   rabbitmq-server
   
   # PHP built-in server (development only)
   cd appliance
   php -S localhost:8000 -t public
   
   # Workers
   bin/console messenger:consume new_job &
   bin/console messenger:consume execute_job &
   bin/console messenger:consume history_sent &
   bin/console messenger:consume job_done &
   ```

## Project Structure

```
space-app/
├── appliance/              # Main application
│   ├── bin/               # Console scripts
│   ├── config/            # Application configuration
│   │   ├── doctrine/      # ODM mappings
│   │   ├── packages/      # Bundle configs
│   │   ├── routes/        # Routing
│   │   └── serializer/    # Serialization
│   ├── domain/            # Domain layer (DDD)
│   │   ├── Contracts/     # Interfaces
│   │   ├── Object/        # Entities, DTOs
│   │   ├── Query/         # Query objects
│   │   └── Recipe/        # Workflows
│   ├── infrastructures/   # Infrastructure layer
│   │   ├── Doctrine/      # ODM repositories
│   │   ├── Kubernetes/    # K8s integration
│   │   ├── Symfony/       # Symfony adapters
│   │   └── Twig/          # Template extensions
│   ├── public/            # Web root
│   ├── src/               # Application code
│   ├── templates/         # Twig templates
│   ├── tests/             # Tests
│   └── var/               # Cache, logs
├── build.dev/             # Docker build files
├── documentation/         # Documentation
├── compose.yml            # Docker Compose config
└── space.sh              # CLI tool
```

## Coding Standards

### PHP Standards

Space follows PSR-12 coding style and uses PHPStan for static analysis.

**Check code style**:
```bash
./space.sh phpcs
```

**Fix code style automatically**:
```bash
./vendor/bin/phpcbf
```

**Run PHPStan**:
```bash
./space.sh phpstan
```

**Run all QA tools**:
```bash
./space.sh qa
```

### Type Declarations

Always use strict types and full type declarations:

```php
<?php

declare(strict_types=1);

namespace Teknoo\Space\Domain\Object;

final class Example
{
    public function __construct(
        private readonly string $name,
        private readonly int $value,
    ) {
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}
```

### Immutability

Use immutable objects where possible:

```php
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

final class Config implements ImmutableInterface
{
    use ImmutableTrait;
    
    public function __construct(
        private readonly string $value,
    ) {
        $this->uniqueConstructorCheck();
    }
}
```

## Architecture Patterns

### Domain-Driven Design

Space follows DDD principles:

**Domain Layer** (`appliance/domain/`):
- Pure business logic
- No dependencies on infrastructure
- Entities, Value Objects, Domain Services
- Interfaces define contracts

**Infrastructure Layer** (`appliance/infrastructures/`):
- Technical implementations
- Adapters for external systems
- Repository implementations
- Framework integrations

### Recipe Pattern

Workflows are implemented using the Recipe pattern from Teknoo East Foundation:

```php
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\Bowl\Bowl;

$recipe = new Recipe();

$recipe = $recipe->cook(
    new Step1(),
    Bowl::class,
    10
)->cook(
    new Step2(),
    Bowl::class,
    20
)->cook(
    new Step3(),
    Bowl::class,
    30
);
```

**Creating a Recipe Step**:

```php
namespace Teknoo\Space\Domain\Recipe\Step;

use Teknoo\Recipe\Ingredient\IngredientInterface;

class MyStep implements StepInterface
{
    public function __invoke(
        SomeObject $object,
        IngredientInterface $result,
    ): self {
        // Perform operation
        $result->add('key', $value);
        
        return $this;
    }
}
```

### Repository Pattern

Repositories abstract data access:

**Interface** (Domain):
```php
namespace Teknoo\Space\Domain\Contracts\DbSource\Repository;

interface MyRepositoryInterface
{
    public function save(MyEntity $entity): self;
    
    public function findById(string $id): ?MyEntity;
}
```

**Implementation** (Infrastructure):
```php
namespace Teknoo\Space\Infrastructures\Doctrine\Repository;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class MyRepository extends DocumentRepository implements MyRepositoryInterface
{
    public function save(MyEntity $entity): self
    {
        $this->getDocumentManager()->persist($entity);
        $this->getDocumentManager()->flush();
        
        return $this;
    }
    
    public function findById(string $id): ?MyEntity
    {
        return $this->find($id);
    }
}
```

## Testing

### Running Tests

**All tests**:
```bash
./space.sh test
```

**Unit tests only**:
```bash
./vendor/bin/phpunit
```

**Behavior tests (Behat)**:
```bash
./vendor/bin/behat
```

**Without coverage**:
```bash
./space.sh test-without-coverage
```

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
    
    public function testGetValue(): void
    {
        $object = new MyClass('test');
        
        self::assertEquals('test', $object->getValue());
    }
}
```

### Writing Behavior Tests (Behat)

**Feature file** (`features/my_feature.feature`):

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
    And I should see "My Project" in the project list
```

**Context implementation**:

```php
use Behat\Behat\Context\Context;

class ProjectContext implements Context
{
    /**
     * @Given I am logged in as :email
     */
    public function iAmLoggedInAs(string $email): void
    {
        // Implementation
    }
    
    /**
     * @When I create a project with:
     */
    public function iCreateProjectWith(TableNode $table): void
    {
        // Implementation
    }
}
```

## Quality Assurance

### Running QA Checks

**Full QA suite**:
```bash
./space.sh qa
```

**Offline QA** (no audit):
```bash
./space.sh qa-offline
```

**Individual checks**:
```bash
# Linting
./space.sh lint

# PHPStan
./space.sh phpstan

# Code style
./space.sh phpcs

# Security audit
./space.sh audit
```

## Extending Space

### Creating an Extension

Extensions allow you to add functionality without modifying core code.

**1. Create Extension Class**:

```php
namespace Acme\SpaceExtension;

use Teknoo\East\Common\Contracts\FrontAsset\FilesSetInterface;
use Teknoo\East\Common\FrontAsset\Extensions\SourceLoader;
use Teknoo\East\Common\FrontAsset\File;
use Teknoo\East\Common\FrontAsset\FilesSet;
use Teknoo\East\Common\FrontAsset\FileType;
use Teknoo\East\Foundation\Extension\ExtensionInterface;
use Teknoo\East\Foundation\Extension\ExtensionInitTrait;
use Teknoo\East\Foundation\Extension\ModuleInterface;
use Teknoo\East\FoundationBundle\Extension\Bundles;
use Teknoo\East\FoundationBundle\Extension\PHPDI;
use Teknoo\East\FoundationBundle\Extension\Routes;
use Teknoo\Space\Extensions\Enterprise\Bundle\TeknooSpaceEnterpriseBundle;
use Teknoo\Space\Infrastructures\Twig\SpaceExtension\Twig;

use function class_exists;
use function is_dir;

/**
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author      Richard Déloge <richard@teknoo.software>
 */
class MyExtension implements ExtensionInterface
{
    use ExtensionInitTrait;

    private ?ExtensionOfTest $extensionOfTest = null;

    /**
     * @return array<int, array{priority?: int, file: string}>
     */
    private function loadParameters(): array
    {
        $path = __DIR__ . '/config/';

        return [
            ['file' => $path . 'di.php'],
        ];
    }

    private function configurePHPDI(PHPDI $phpdi): void
    {
        $phpdi->loadDefinition(
            $this->loadParameters()
        );
    }

    private function configureRoutes(Routes $routes): void
    {
        $path = __DIR__ . '/routes/';

        if (is_dir($envPath = $path . $routes->getEnvironment())) {
            $routes->import($envPath . '/*.{php,yaml}');
        }

        $routes->import($path . '*.{php,yaml}');
    }

    private function injectTwigTemplates(Twig $twig): void
    {
        $twig->load(fn (?string $blockName): ?string => match ($blockName) {
            'space_container' => '@AcmeExtension/container.html.twig',
            default => null,
        });
    }

    public function extendsTest(): ?ExtensionOfTest
    {
        if (!class_exists(ExtensionOfTest::class)) {
            return null;
        }

        return $this->extensionOfTest ??= ExtensionOfTest::create();
    }

    public function executeFor(ModuleInterface $module): ExtensionInterface
    {
        match ($module::class) {
            Bundles::class => $module->register(TeknooSpaceEnterpriseBundle::class, ['all' => true]),
            PHPDI::class => $this->configurePHPDI($module),
            Routes::class => $this->configureRoutes($module),
            Twig::class => $this->injectTwigTemplates($module),
            SourceLoader::class => $this->updateAssets($module),

            default => $this->extendsTest()?->executeFor($module),
        };

        return $this;
    }

    public function __toString(): string
    {
        return 'Acme Extension';
    }
}

```

**2. Register Extension**:

Add to `extensions/enabled.json`:
```json
[
  "Acme\\SpaceExtension\\MyExtension"
]
```

**3. Add Services**:

`Resources/config/services.php`:
```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    
    $services->set(MyService::class)
        ->autowire()
        ->autoconfigure();
};
```

### Adding Custom Recipe Steps

**1. Create Step Class**:

```php
namespace Acme\SpaceExtension\Recipe\Step;

use Teknoo\Recipe\Ingredient\IngredientInterface;

class CustomStep
{
    public function __invoke(
        MyObject $object,
        IngredientInterface $result,
    ): self {
        // Custom logic
        $result->add('custom_data', $data);
        
        return $this;
    }
}
```

**2. Register Step**:

```php
$services->set(CustomStep::class)
    ->tag('teknoo.east.recipe.step');
```

**3. Add to Plan**:

```php
$plan = $plan->add(CustomStep::class, 15);
```

### Adding Custom Hooks

**1. Define Hook**:

```php
namespace Acme\SpaceExtension\Hook;

use Teknoo\East\Paas\Contracts\Hook\HookInterface;

class CustomHook implements HookInterface
{
    public function setPath(string $path): self
    {
        // Set working directory
        return $this;
    }
    
    public function run(array $options): self
    {
        // Execute hook logic
        return $this;
    }
}
```

**2. Register Hook**:

Add to hooks collection configuration:
```php
[
    'name' => 'my-custom-hook',
    'type' => CustomHook::class,
    'options' => ['option1' => 'value1'],
]
```

### Custom Repository Implementations

Implement domain repository interface with custom storage:

```php
namespace Acme\SpaceExtension\Repository;

use Teknoo\Space\Domain\Contracts\DbSource\Repository\MyRepositoryInterface;

class CustomRepository implements MyRepositoryInterface
{
    public function __construct(
        private readonly ExternalApiClient $client,
    ) {
    }
    
    public function save(MyEntity $entity): self
    {
        $this->client->save($entity->toArray());
        return $this;
    }
    
    public function findById(string $id): ?MyEntity
    {
        $data = $this->client->find($id);
        return $data ? MyEntity::fromArray($data) : null;
    }
}
```

## Debugging

### Enable Debug Mode

```bash
# .env.local
APP_ENV=dev
APP_DEBUG=1
```

### Symfony Profiler

Access profiler in browser:
- Bottom toolbar with debug info
- Full profiler: `/_profiler`

### Logging

**Log Levels**:
- DEBUG: Detailed information
- INFO: Informational messages
- WARNING: Warning messages
- ERROR: Error messages
- CRITICAL: Critical errors

### Debugging Workers

Run worker in foreground with verbose output:

```bash
bin/console messenger:consume execute_job -vvv
```

### Database Queries

Enable query logging:

```yaml
# config/packages/dev/doctrine_mongodb.yaml
doctrine_mongodb:
    default_database: space
    logging:
        enabled: true
```

### XDebug

Configure XDebug for step debugging:

```ini
; php.ini
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.client_host=localhost
xdebug.client_port=9003
xdebug.start_with_request=yes
```

**PhpStorm Configuration**:
1. Settings → PHP → Debug
2. Set port to 9003
3. Enable "Listen for PHP Debug Connections"
4. Set breakpoints
5. Start debugging

## Contributing

### Getting Started

1. Fork the repository
2. Create feature branch: `git checkout -b feature/my-feature`
3. Make changes
4. Write tests
5. Run QA checks: `./space.sh qa`
6. Commit changes: `git commit -am 'Add feature'`
7. Push to branch: `git push origin feature/my-feature`
8. Create Pull Request

### Commit Messages

Follow conventional commits format:

```
type(scope): subject

body

footer
```

**Types**:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style
- `refactor`: Refactoring
- `test`: Tests
- `chore`: Maintenance

**Example**:
```
feat(domain): add support for custom hooks

Implement HookInterface to allow custom deployment hooks.
Hooks can be registered via configuration.

Closes #123
```

### Pull Request Guidelines

1. **Title**: Clear and descriptive
2. **Description**: Explain changes and motivation
3. **Tests**: Include tests for new features/fixes
4. **Documentation**: Update docs if needed
5. **QA**: All checks must pass
6. **Review**: Address reviewer feedback

### Code Review

Code reviews focus on:
- Correctness
- Design and architecture
- Testing coverage
- Code quality
- Documentation
- Security implications
- Performance

## Building and Packaging

### Building Docker Images

```bash
# Build all images
./space.sh build

# Build specific image
docker build -t space-php-fpm -f build.dev/php-fpm/Dockerfile .
```

## Best Practices

### Domain Layer

1. **Keep it pure**: No framework dependencies
2. **Use interfaces**: Define contracts in domain
3. **Immutability**: Prefer immutable objects
4. **Type safety**: Use strict types

### Infrastructure Layer

1. **Implement interfaces**: From domain
2. **Adapt external systems**: Don't leak into domain
3. **Handle errors**: Convert to domain exceptions
4. **Test adapters**: Mock external dependencies

### Testing

1. **Test behavior**: Not implementation
2. **Unit tests**: Fast and isolated
3. **Integration tests**: Real dependencies
4. **Coverage**: Aim for 80%+ coverage

### Performance

1. **Lazy loading**: Load data on demand
2. **Caching**: Use Symfony cache
3. **Database indexes**: Index frequently queried fields
4. **Query optimization**: Use projections

### Security

1. **Input validation**: Always validate user input
2. **Output escaping**: Escape output in templates
3. **Authentication**: Use Symfony Security
4. **Authorization**: Use voters for access control
5. **Encryption**: Encrypt sensitive data

## Resources

### Documentation

- [Architecture](architecture.md) - System architecture
- [Domain Model](domain.md) - Domain documentation
- [Infrastructure](infrastructure.md) - Infrastructure layer

### External Resources

- [Teknoo East Foundation](https://github.com/TeknooSoftware/east-foundation)
- [Teknoo East PaaS](https://github.com/TeknooSoftware/east-paas)

### Community

- **GitHub**: https://github.com/TeknooSoftware/space-app
- **Issues**: Report bugs and request features
- **Discussions**: Ask questions and share ideas
- **Email**: contact@teknoo.software

## License

Space Standard Edition is licensed under the 3-Clause BSD License.
See the LICENSE file for details.

## Support

- **Community Support**: GitHub Issues and Discussions (free)
- **Priority Support**: contact@teknoo.software (commercial)
- **Enterprise Edition**: richard@teknoo.software
