# Development Guide

## Overview

This guide is for developers who want to contribute to Space, extend its functionality, or understand the codebase for
customization purposes.

## Getting Started

### Prerequisites

- PHP 8.5+
- Composer 2.8+
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
    - Web UI: https://localhost (the stack serves TLS; a self-signed certificate is generated)
    - RabbitMQ Management: http://localhost:15672 (`space` / `space_pwd`)
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
   bin/console messenger:consume new_task &
   bin/console messenger:consume execute_job &
   bin/console messenger:consume history_sent &
   bin/console messenger:consume job_done &
   ```

## Project Structure

```
space-app/
├── appliance/              # Main application
│   ├── bin/               # console + config.sh
│   ├── config/            # Application configuration — di.*.php live here, directly
│   │   ├── doctrine/      # ODM mappings
│   │   ├── packages/      # Bundle configs
│   │   ├── routes/        # Routing (api/ holds the API v1 files)
│   │   └── serializer/    # Serialization
│   ├── domain/            # Domain layer (DDD)
│   │   ├── Cluster/       # Cluster catalog
│   │   ├── Configuration/ # Configuration objects
│   │   ├── Contracts/     # Interfaces
│   │   ├── Liveness/      # Ping file and scheduler
│   │   ├── Loader/        # Read side
│   │   ├── Middleware/    # Domain middlewares
│   │   ├── Object/        # Entities (Persisted/), DTOs (DTO/, DTO/Task/), Config/
│   │   ├── Query/         # Query objects
│   │   ├── Recipe/        # Workflows — Plan/, Plan/Task/, Step/
│   │   ├── Service/       # Domain services
│   │   └── Writer/        # Write side
│   ├── extensions/        # Mounted extensions — gitignored, see extensions/*/AGENTS.md
│   ├── features/          # Behat .feature files (NOT under tests/)
│   ├── infrastructures/   # Infrastructure layer
│   │   ├── AnsibleDockerCompose/ # Docker Compose target
│   │   ├── Doctrine/      # ODM repositories
│   │   ├── Endroid/       # QR code step
│   │   ├── Kubernetes/    # K8s integration
│   │   ├── Recipe/        # ProvisioningPlanBowl
│   │   ├── Symfony/       # Symfony adapters
│   │   └── Twig/          # Template extensions
│   ├── public/            # Web root
│   ├── src/               # Kernel.php only
│   ├── templates/         # Twig templates (api/ holds the .json.twig views)
│   ├── tests/             # PHPUnit tests + Behat contexts, traits and fixtures
│   ├── translations/      # Translation catalogues
│   └── var/               # Cache, logs
├── .agents/               # Agent coordination hub
├── build.dev/             # Docker build files
├── documentation/         # Documentation (start at documentation/README.md)
├── AGENTS.md              # Standards for contributors and AI agents
├── CLAUDE.md              # Claude Code gateway to AGENTS.md
├── compose.yml            # Default Docker Compose stack (three others ship beside it)
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

**All tests** (unit + behavior, with coverage, multi-threaded):

```bash
./space.sh test
```

**All tests in mono thread** (unit + behavior, with coverage):

```bash
./space.sh test-mono-thread
```

**All tests without coverage** (unit + behavior, multi-threaded):

```bash
./space.sh test-without-coverage
```

**Unit tests only** (with coverage):

```bash
./space.sh units-tests
```

**Unit tests only** (without coverage):

```bash
./space.sh units-tests-without-coverage
```

**Unit tests directly** (PHPUnit):

```bash
vendor/bin/phpunit tests/path/to/TestFile.php
```

**Behavior tests only** (Behat, multi-threaded):

```bash
./space.sh behavior-test
```

**Behavior tests only** (Behat, mono thread):

```bash
./space.sh behavior-test-mono-thread
```

**Behavior tests directly** (Behat):

```bash
vendor/bin/behat features/path/to/feature.feature
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
        
        $this->assertInstanceOf(MyClass::class, $object);
    }
    
    public function testGetValue(): void
    {
        $object = new MyClass('test');
        
        $this->assertEquals('test', $object->getValue());
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
      | name       | My Project                          |
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

### Behat Feature Structure

43 Behat feature files in `appliance/features/`:

**API features** (32): `api.account`, `api.account.cluster`, `api.account.environments`,
`api.account.variables`, `api.admin.account`, `api.admin.account.cluster`, `api.admin.account.environments`,
`api.admin.account.variables`, `api.admin.job.k8s.*` (7 files), `api.admin.project`,
`api.admin.project.refresh-credentials`, `api.admin.project.variables`, `api.admin.user`, `api.job.k8s.*` (7 files),
`api.job.dc.start`, `api.jwt`, `api.login`, `api.project`, `api.project.refresh-credentials`,
`api.project.variables`, `api.settings`

**Web features** (10): `web.account`, `web.account.variables`, `web.job.start`,
`web.job.start.with-vars-from-account`, `web.job.start.with-vars-from-project`, `web.login`,
`web.project`, `web.project.variables`, `web.subscription`, `web.user.settings`

**Worker hook** (1): `worker.hooks`

An enabled extension adds features of its own in `appliance/extensions/<Name>/features/`, discovered through
`ExtensionsDiscoveryExtension` in `appliance/behat.yml`.

**Conventions** (adopted 2026-09-19):

- A `Background:` holds only the strictly identical leading `Given` run of a file. Never reorder a step to
  widen one: `an account for …` / `a user, called …` act on the *last* created object.
- Use the composite authentication steps from `AuthenticationTrait` rather than re-chaining the primitives.
- **A scenario title stays on one line.** A wrapped title is parsed as a description, and `behat --name` can
  never match it again.
- Tag a feature with its audience: `@api`, `@web`, `@worker`, `@admin`.
- Step patterns match case-insensitively (`/^…$/iu`), and `behat/gherkin` 4.17 does not parse `Rule:`.

### Test Traits

Behat test traits in `tests/Behat/Traits/`:

| Trait                       | Purpose                                       |
|-----------------------------|-----------------------------------------------|
| `ApiTrait`                  | API request helpers                           |
| `AuthenticationTrait`       | Composite sign in / TOTP / JWT / logout steps |
| `BrowserActionTrait`        | Browser action helpers                        |
| `BrowserCrawlingTrait`      | Browser crawling/navigation                   |
| `BuilderTrait`              | Git/build helpers                             |
| `DockerComposeTrait`        | Docker Compose cluster helpers                |
| `HttpTrait`                 | HTTP request/response helpers                 |
| `JwtTrait`                  | JWT token generation                          |
| `KubernetesTrait`           | Kubernetes cluster helpers                    |
| `NotificationTrait`         | Notification/messaging helpers                |
| `PersistenceOperationTrait` | MongoDB persistence operations                |
| `PersistenceStepsTrait`     | Persistence step definitions                  |
| `WorkerTrait`               | Worker/AMQP helpers                           |

### PHPUnit Structure

`appliance/tests/` mirrors the `domain/` and `infrastructures/` directory structure. Test classes follow the
namespace pattern `Teknoo\Space\Tests\{Layer}\{SubPath}` and the `Test.php` suffix. For example:
`tests/domain/Object/Persisted/AccountDataTest.php` tests `domain/Object/Persisted/AccountData.php`.

The suite is at **100% line coverage** and runs with `failOnDeprecation="true"`: a deprecation warning fails
the build and must be fixed at its call site, never silenced.

### Form Types

Form types live in `infrastructures/Symfony/Form/Type/`, one subdirectory per category:

- **Account**: `AccountType`, `AccountClusterType`, `SpaceAccountType`, `AdminSpaceAccountType`,
  `SpaceSubscriptionType`, `CodeGeneratorType`, `VarsSetType`, `VarsType`
- **AccountData**: `AccountDataType`
- **AccountEnvironment**: `AccountEnvironmentResumesType`
- **Project**: `SpaceProjectType`, `VarsSetType`, `VarsType`
- **ProjectMetadata**: `ProjectMetadataType`
- **Job**: `NewJobType`, `ApiNewJobType`, `JobVarType`
- **User**: `UserType`, `AdminSpaceUserType`, `SpaceUserType`, `PasswordType`, `SpacePasswordType`,
  `ApiKeysAuthType`, `JWTConfigurationType`
- **Contact**: `SupportType`, `AttachmentType`
- **Search**: `AccountSearchType`, `AccountClusterSearchType`, `JobSearchType`, `MediaSearchType`,
  `ProjectSearchType`, `UserSearchType`, and the shared `DefaultSearchTrait`

`VarsSetType` and `VarsType` exist under both `Account/` and `Project/`: distinct classes in distinct
namespaces, not a duplication.

Custom data mappers in `infrastructures/Symfony/Form/DataMapper/`:
`AbstractVarsMapper`, `AccountVarsMapper`, `ProjectVarsMapper`.

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
use Teknoo\Space\Extensions\MyExtension\Infrastructure\Symfony\Bundle\MyExtensionBundle;
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
            Bundles::class => $module->register(MyExtensionBundle::class, ['all' => true]),
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
zend_extension = xdebug.so
xdebug.mode = debug
xdebug.client_host = localhost
xdebug.client_port = 9003
xdebug.start_with_request = yes
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

### The `cli_execute` Image (`build.dev/php-buildah/`)

Three of the four workers run on `build.dev/php-cli`. The `execute_job` one — the service `cli_execute`
in the compose stacks — runs on `build.dev/php-buildah` instead, because it is the only process that
builds OCI images and runs build hooks. That image mirrors the production builder image, on a Debian
base rather than Alpine:

- **`buildah`, `containerd`, `nerdctl`** with a rootless setup: a real `HOME` for `spaceuser`,
  `/etc/subuid` and `/etc/subgid` ranges, a `storage.conf` using `fuse-overlayfs`, a `containerd`
  configuration whose gRPC socket belongs to uid/gid 1000, and `sudoers` rules limited to
  `containerd`, `ctr`, `nerdctl` and the snapshotter.
- **`ansible-core` and `openssh-client`**, used when the deployment target is a Docker Compose host.
  The locale is `C.UTF-8`: Ansible refuses to start unless Python reports a UTF-8 preferred encoding.
- **`/usr/local/bin/space-run`** — a thin `sudo nerdctl run "$@"` wrapper. Hook definitions
  (`SPACE_HOOKS_COLLECTION_JSON` / `SPACE_HOOKS_COLLECTION_FILE`) invoke hooks through this name, so a
  hook fails with `space-run: not found` on any image that does not ship it.
- **The `overlayfs` containerd snapshotter on a named volume.** The two settings go together: the
  `builder_containerd` volume puts `/var/lib/containerd` on a real filesystem, and only then can
  `overlayfs` work — left on the overlay2 filesystem Docker gives the container it cannot stack an
  overlay on an overlay and every `nerdctl run` dies with
  `failed to mount rootfs component: invalid argument`. Measured on `composer --version`:

  | Snapshotter | containerd root | run |
  |---|---|---|
  | `overlayfs` | named volume | **~0.7 s** |
  | `native` | container filesystem | 11 s, then 36 s (it copies the whole rootfs each time) |
  | `fuse-overlayfs` | container filesystem | cannot extract a layer at all (`setxattr ... user.overlay.impure: operation not permitted`) |

  The volume also makes the pulled hook images survive a container recreation. It holds the content
  store, the metadata database and the snapshots together — splitting them across separate volumes
  desynchronises them and pulls then fail with `snapshot ... already exists`. It applies to `nerdctl`
  only; `buildah` has its own store and uses `fuse-overlayfs` as a mount program there.
- **`/builder-init.sh`** — the image entrypoint. It creates `XDG_RUNTIME_DIR`, starts `containerd`,
  waits for its socket, logs into the global OCI registry when
  `SPACE_OCI_GLOBAL_REGISTRY_URL`/`_USERNAME`/`_PWD` are all set, then `exec`s whatever the compose
  file passes as `command:`. Every step degrades to a warning, so the worker keeps consuming its queue
  even without a usable nested runtime. The hook pre-pull is started **in the background**: it takes
  minutes, and a worker that has not reached `messenger:consume` leaves the `execute_job` queue
  unconsumed for exactly that long.

#### The hook images are pre-pulled, and that is not optional

`SPACE_BUILDER_HOOKS` lists the hook images the entrypoint pulls before the worker starts consuming,
exactly as production does. It looks like an optimisation but it is what makes hooks usable at all.

The value is a list of `name:tag` pairs separated by spaces or commas, each resolved against
`${SPACE_OCI_GLOBAL_REGISTRY_URL}/space/hook-<name>:<tag>`. It belongs to the **override** layer, next
to the registry credentials, because it depends on what the registry actually holds: the
`compose*.override.yml.dist` templates ship it empty, and an empty value pulls nothing.

```yaml
    cli_execute:
        environment:
            - SPACE_BUILDER_HOOKS=composer:latest composer:8.5 make:latest npm:latest php:8.5
```

A hook is run by `space-run`, that is `nerdctl run`, and `nerdctl run` has no quiet mode in the version
this image ships. So when the image is not already local, the pull happens *inside* the hook, and
`Teknoo\East\Paas\Infrastructures\ProjectBuilding\AbstractHook::run()` stores
`getOutput() . getErrorOutput()` in the job history. Measured on `hook-composer:8.5`: **4.2 MB and
28 122 lines** of progress-bar redraw frames written to the history, and **187 s** of the hook's
**240 s** timeout spent downloading before `composer` even starts.

The `builder_containerd` volume keeps the pulled images across container recreations, so the download
is paid once. The pre-pull still runs in the background rather than inline: on a first start the
collection is several gigabytes, and a worker that has not reached `messenger:consume` leaves the
`execute_job` queue unconsumed for exactly that long. Keep `SPACE_BUILDER_HOOKS` to the hooks you
actually use rather than the full catalogue.

#### Why the service is `privileged`

`privileged: true` on `cli_execute` is not a convenience, it is a requirement, and it was measured on
this image:

| Docker options | Result |
|---|---|
| defaults | `buildah` dies immediately: `Error during unshare(CLONE_NEWUSER): Operation not permitted` (the default seccomp profile blocks the syscall) and `/dev/fuse` is absent |
| `--security-opt seccomp=unconfined` + `--device /dev/fuse` | fails at the overlay mount: `permission denied` (AppArmor) |
| `--security-opt seccomp=unconfined --security-opt apparmor=unconfined` | fails at the overlay mount: `fuse: device not found` |
| all three together | `newuidmap`/`newgidmap` still fail and buildah falls back to a *single* UID mapping, so pulling any real base image fails: `potentially insufficient UIDs or GIDs available in user namespace (requested 0:42 for /etc/shadow)` |
| `--privileged` | works, with the full `/etc/subuid` range: `buildah bud` builds, commits and tags |

Only the last row can build an image whose layers contain files owned by more than one UID, which is
every distribution base image. Note that `privileged` does not give the process any capability here —
the container still runs as `spaceuser` with an empty effective capability set — it lifts the seccomp
and AppArmor profiles, exposes `/dev/fuse` and allows the full user-namespace UID mapping.

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
