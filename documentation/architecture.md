# Space Architecture

## Overview

Space is a **Platform as a Service (PaaS)** application built on modern PHP technologies, following
Domain-Driven Design (DDD) and hexagonal architecture principles. It provides continuous integration, delivery, and
deployment capabilities for containerized applications on clusters, like Kubernetes.

## Architectural Principles

### Hexagonal Architecture (Ports & Adapters)

Space follows a clean hexagonal architecture pattern with clear separation between:

- **Domain Layer**: Core business logic, persisted objects, and domain services, Use cases and workflow orchestration
  through Recipes
- **Application Layer**: Configuration and services containers optimisation
- **Infrastructure Layer**: Technical implementations and adapters

### Domain-Driven Design

The application implements DDD concepts:

- **Bounded Contexts**: Account management, Project management, Job execution, User management
- **Aggregates**: Account (with Users, Projects, Environments), Project (with Metadata, Variables), Job
- **Value Objects**: Configuration objects, DTOs, Query objects
- **Repositories**: Abstracted data access through interfaces

## High-Level Architecture

```
┌────────────────────────────────────────────────────────────┐
│                         Presentation Layer                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐  │
│  │   Web UI     │  │  REST API    │  │  CLI Commands    │  │
│  │  (HTTP)      │  │  (HTTP)      │  │   (Console)      │  │
│  └──────────────┘  └──────────────┘  └──────────────────┘  │
└────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      Application Layer                      │
│  ┌──────────────────────────────────────────────────────┐   │
│  │    Configuration, Service Container Compilation      │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                         Domain Layer                        │
│  ┌──────────────────────────────────────────────────────┐   │
│  │           Recipe Plans (Workflow Orchestration)      │   │
│  │  • Dashboard  • ProjectList  • JobStart  • JobGet    │   │
│  │  • AccountManagement  • Subscription  • Contact      │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Recipe Steps (Use Cases)                │   │
│  │  • Persist  • Validate  • Transform  • Notify        │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │   Entities   │  │Value Objects │  │    Services      │   │
│  │ • Account    │  │ • Config     │  │ • Contracts      │   │
│  │ • User       │  │ • DTO        │  │ • Queries        │   │
│  │ • Project    │  │ • Plans      │  │                  │   │
│  │ • Job        │  │              │  │                  │   │
│  └──────────────┘  └──────────────┘  └──────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Infrastructure Layer                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌───────────┐    │
│  │ Doctrine │  │Kubernetes│  │  Symfony │  │   Twig    │    │
│  │   ODM    │  │  Client  │  │ Messenger│  │Extensions │    │
│  └──────────┘  └──────────┘  └──────────┘  └───────────┘    │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                   External Dependencies                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────┐   │
│  │ MongoDB  │  │ RabbitMQ │  │ Mercure  │  │   Cluster  │   │
│  │          │  │  (AMQP)  │  │          │  │(K8s / SSH) │   │
│  └──────────┘  └──────────┘  └──────────┘  └────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. Recipe Pattern

Space uses the **Recipe pattern** from Teknoo East Foundation for workflow orchestration:

- **Plans**: High-level workflows combining multiple steps
- **Steps**: Individual operations implementing specific use cases
- **EditablePlan**: Dynamic plans that can be modified through extensions during the service container compilation

### 2. Loader/Writer Pattern

Data persistence follows the **Loader/Writer pattern**: Loaders read entities from MongoDB and Writers persist
changes. Each persisted entity has a one-to-one Loader–Writer pair.

- **Loaders** (12 classes in `domain/Loader/`): implement `LoaderInterface`, fetch entities from MongoDB
  repositories. Examples: `AccountDataLoader`, `UserDataLoader`, `ProjectMetadataLoader`.
- **Writers** (13 classes in `domain/Writer/`): implement `WriterInterface`, persist entities via Doctrine ODM
  repositories. Examples: `AccountDataWriter`, `UserDataWriter`, `ProjectMetadataWriter`.
- **Meta Writers** (3 classes in `domain/Writer/Meta/`): `SpaceAccountWriter`, `SpaceUserWriter`,
  `SpaceProjectWriter` — bridge East Foundation entities with Space's domain layer.

Loaders and Writers are wired as services in `config/di.persistent_data.php` and injected into Recipe plans
that need data access.

### 3. Messenger-Based Workers

The application uses Symfony Messenger for asynchronous processing:

- **New Job Worker**: Receives and initializes deployment requests
- **Execute Job Worker**: Builds and deploys projects using East PaaS
- **History Sent Worker**: Persists deployment history events
- **Job Done Worker**: Finalizes completed deployments

### 4. PHP-DI Configuration

Space uses `di.*.php` files for dependency injection. They sit **directly in `appliance/config/`** — there is
no `config/di/` directory:

| File                               | Purpose                                                       |
|------------------------------------|---------------------------------------------------------------|
| `di.common.php`                    | Core services (logger, event dispatcher, Mercure hub)         |
| `di.hook.php`                      | East PaaS hook registration                                   |
| `di.services.php`                  | Application services                                          |
| `di.recipe.plans.php`              | Recipe Plan definitions — and the steps used by a single plan |
| `di.recipe.steps.php`              | Recipe Step definitions                                       |
| `di.variables.php`                 | Application variables                                         |
| `di.variables.clusters.php`        | Cluster catalog                                               |
| `di.variables.east.common.php`     | East Foundation common defaults                               |
| `di.variables.east.paas.php`       | East PaaS defaults                                            |
| `di.variables.from.envs.php`       | Environment-variable-driven config                            |
| `di.persistent_data.php`           | MongoDB repositories, loaders, writers                        |
| `di.persisted_vars.encryption.php` | Persisted-variable encryption service                         |

Extensions register their own configuration via `di.php` files loaded by the Teknoo East Foundation extension
system. See [infrastructure.md](infrastructure.md#php-di-container) for implementation details.

### 5. Extension Repositories

An extension is not committed to this repository. It ships from a repository of its own and is mounted at
runtime under `appliance/extensions/`, which `appliance/.gitignore` ignores — so `git status` here never
reports a change made inside one. The extension loader discovers the mounted bundles through Composer
autoloading.

The consequence for this documentation: what an extension adds is described **by that extension**, in its own
`AGENTS.md` and `documentation/`, never here. An installation running without it must not be reading about
features it does not have.

### 6. Bowl Pattern — ProvisioningPlanBowl

The `ProvisioningPlanBowl` (`infrastructures/Recipe/Bowl/`) resolves the correct account-provisioning plan
at request time based on the cluster `type` (kubernetes vs docker-compose). This is necessary because a
`RecipeBowl`'s recipe is fixed at container-build time, but the provisioning plan set differs per cluster
type. Kubernetes resolves to the standard Kubernetes plan instances; docker-compose resolves to the
Ansible-based provisioning plans.

The bowls are no longer executed inside the web request: each provisioning role is queued as a
`Teknoo\Space\Object\DTO\Task\*` task (a `NewTaskInterface`) through `CallNewTask`, and the `new_task`
worker runs the matching `Recipe\Plan\Task\AccountProvisioningTask` instance, which loads the account, its
history, clusters, environments and registry before delegating to the bowl. The admin routes
(`space_admin_account_*_reinstall`, `space_admin_account_refresh_quota` and their API twins) share one HTTP
plan, `Recipe\Plan\AccountTaskDispatch`, whose route default `taskClass` selects the task; account creation
and edition queue `InstallRegistryTask` / `InstallEnvironmentTask` from their step lists.

### 7. Access Control — ObjectAccessControl

Recipe plans use `ObjectAccessControlInterface` and `ListObjectsAccessControlInterface` (from Teknoo East
Common) for step-level access control. These interfaces are implemented by Symfony-based steps that
delegate to the voter system. Single-entity checks use `ObjectAccessControlInterface`; collection-level
checks use `ListObjectsAccessControlInterface`. See [domain.md#security-voters](domain.md#security-voters)
for the full voter list.

### 8. Liveness Pinging

Workers and the web application use liveness pinging to detect frozen processes:

- **PingFile** (`domain/Liveness/`): writes a timestamp to a file path (`SPACE_PING_FILE`) at regular
  intervals (`SPACE_PING_SECONDS`).
- **PingScheduler** (`domain/Liveness/`): schedules periodic ping writes.
- **LivenessSubscriber** (`infrastructures/Symfony/Event/`): Symfony event subscriber that writes the
  ping file on each HTTP request.

External health checks can monitor the ping file's modification time to detect stuck workers.

### 9. Mercure Publishers

Real-time job status updates are broadcast via two Mercure publishers in
`infrastructures/Symfony/Mercure/`:

- **TaskUrlPublisher**: publishes job URL updates to clients (triggers browser redirect to job page).
- **TaskErrorPublisher**: publishes task error notifications for real-time error display.

Both use the Mercure hub for Server-Sent Events (SSE) delivery to subscribed browsers.

### 10. Multi-Tenancy Model

Space implements a multi-tenancy architecture:

```
Account (Tenant)
├── Users (1..n)
├── Projects (0..n)
│   ├── Metadata (like project url)
│   └── Persisted Variables (can be encrypted)
├── Environments (1..n)
│   └── Namespace
├── Clusters (0..n)
├── Registry (OCI images)
├── Persisted Variables
└── History
```

### 11. Security & Authentication

- **OAuth2 Integration**: Third-party authentication support
- **Multi-Factor Authentication (MFA)**: TOTP-based 2FA with QR code generation
- **JWT Tokens**: API authentication
- **Symfony Security**: Role-based access control (ROLE_USER, ROLE_ADMIN)
- **Variable Encryption**: RSA/DSA encryption for sensitive persisted variables

### 12. East PaaS Integration

Space leverages **Teknoo East PaaS** for deployment orchestration:

- **Compilation**: Transforms `.paas.yaml` configurations into compilation and deployment plan
- **Make**: Pre/post deployment hooks (Composer, NPM, PIP, Make, Symfony Console, Laravel Artisan)
- **Image Building**: Uses Buildah to create OCI-compliant container images
- **Deployment**: Applies resources to a cluster. Two targets are supported, selected per cluster by the `type`
  field: **Kubernetes** (via the Kubernetes API) and **docker-compose** (a remote Docker host running a Compose
  stack behind Traefik v3, applied over SSH with Ansible). The job API and `RunJob` recipe are target-agnostic;
  the driver is chosen at runtime by the cluster `type`.

## Data Flow

### Deployment Workflow

```
1. User submits job via Web UI or API
        ↓
2. Job stored in MongoDB
        ↓
3. NewJob message sent to RabbitMQ
        ↓
4. New Job Worker picks up message
        ↓
5. ExecuteJob message sent to RabbitMQ
        ↓
6. Execute Job Worker:
   • Clones Git repository
   • Executes hooks
   • Builds OCI images with Buildah
   • Create cluster resources
   • Deploys to cluster
        ↓
7. History events sent to History Worker
        ↓
8. Job completion sent to Job Done Worker
        ↓
9. Final status updated in MongoDB
        ↓
10. User notified via Mercure (optional)
```

## Key Technologies

### Backend Stack

- **PHP 8.5+**: Modern PHP with type safety and performance
- **Symfony 7.4 LTS and 8.x**: both maintained branches are supported; the constraints in
  `appliance/composer.json` are declared per component, and an update must never drop one of the two branches
- **Doctrine MongoDB ODM 2.17+**: MongoDB object-document mapper (`doctrine/mongodb-odm`; the `^3.5`
  constraint in `composer.json` belongs to `doctrine/common`, a different package)
- **Teknoo Libraries**:
    - Immutable: Immutable object pattern
    - States: State pattern implementation
    - Recipe: Workflow orchestration
    - East Foundation: Recipe pattern and extension system
    - East Common: Shared components
    - East PaaS: PaaS orchestration engine
    - Kubernetes Client: Kubernetes API integration

### Infrastructure

- **MongoDB**: Primary database for all entities
- **RabbitMQ**: Message broker for worker communication
- **Mercure**: Real-time updates via Server-Sent Events (SSE)
- **Buildah**: OCI image builder
- **Kubernetes 1.30+**: Container orchestration platform (default deployment target)
- **Docker Compose + Ansible + Traefik v3**: alternative deployment target — a remote Docker host managed over
  SSH with Ansible

### Frontend

- **Twig**: Server-side templating
- **Symfony Forms**: Form generation and validation

## Extension System

Space provides a powerful extension mechanism allowing developers to:

- Add Symfony bundles
- Extend PHP-DI configuration
    - Add or modify Recipe steps and plans
    - Customize East PaaS compiler
- Add hooks for build/deployment processes
- Extend libraries (containers, pods, services, ingresses resources in the cluster)
- Customize UI (templates, routes, menus, assets)
- Change branding (logo, CSS, JS)

Extensions are managed through Teknoo East Foundation's extension loader system with two modes:

- **FileLoader**: Extensions listed in JSON file (fast)
- **ComposerLoader**: Auto-discovery via Composer autoloader (convenient)

## Scalability & Performance

### Horizontal Scaling

- **Stateless Web Servers**: Multiple web server instances can run behind a load balancer
- **Worker Pool**: Multiple worker instances can process jobs concurrently
- **Database**: MongoDB supports sharding for horizontal scaling

### Performance Optimizations

- **Symfony Cache**: Opcache and application-level caching
- **Doctrine Query Optimization**: Indexed queries and efficient ODM mapping
- **Async Processing**: Heavy operations delegated to workers
- **Connection Pooling**: Database and message broker connections

### Resource Management

- **Quota System**: CPU and memory limits enforced per account
- **Subscription Plans**: Different resource tiers (compute, memory, environment count)
- **Kubernetes Resource Limits**: Enforced at cluster level

## Security Architecture

### Encryption Layers

1. **East PaaS Encryption**: RSA/DSA encryption for messages between servers and workers
2. **Persisted Variables Encryption**: Separate encryption for stored secrets
3. **TLS/SSL**: HTTPS for all web communications
4. **Kubernetes Secrets**: Native Kubernetes secret management

### Access Control

- **Account-based Isolation**: Strict separation between accounts
- **Kubernetes RBAC**: Role-based access in clusters
- **Namespace Isolation**: Each account environment in separate namespace
- **Service Account Tokens**: Limited-scope Kubernetes access

## Deployment Topology

### Typical Production Setup

```
                    ┌──────────────┐
                    │ Load Balancer│
                    └───────┬──────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
   ┌────▼─────┐       ┌────▼─────┐       ┌────▼─────┐
   │ Web Pod 1│       │ Web Pod 2│       │ Web Pod 3│
   └──────────┘       └──────────┘       └──────────┘
        │                   │                   │
        └───────────────────┼───────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
   ┌────▼─────┐       ┌────▼─────┐       ┌────▼─────┐
   │ MongoDB  │       │ RabbitMQ │       │ Mercure  │
   └──────────┘       └──────────┘       └──────────┘
        │                   │
        └───────────────────┼───────────────────┐
                            │                   │
                       ┌────▼─────┐       ┌────▼─────┐
                       │ Worker 1 │       │ Worker 2 │
                       │ (New Job)│       │(Execute) │
                       └──────────┘       └──────────┘
                            │                   │
                            └───────────────────┘
                                      │
                                 ┌────▼─────────┐
                                 │   Cluster    │
                                 └──────────────┘
```

The **Cluster** target is either a Kubernetes cluster (reached via its API) or a remote Docker host (reached
over SSH; workers run Ansible to apply the Compose/Traefik stack). The target is chosen per cluster by the
`type` field.

## Design Patterns

### Used Patterns

1. **Hexagonal Architecture**: Ports and adapters separation
2. **Domain-Driven Design**: Bounded contexts, aggregates, repositories
3. **Recipe Pattern**: Composable workflow orchestration
4. **Repository Pattern**: Data access abstraction
5. **Factory Pattern**: Object creation (Doctrine repositories)
6. **Strategy Pattern**: Different cluster types, compilers
7. **Observer Pattern**: Event system (Symfony EventDispatcher)
8. **Command Pattern**: Symfony Console commands
9. **State Pattern**: Teknoo States library for object states
10. **Immutable Pattern**: Teknoo Immutable for immutable objects
11. **Dependency Injection**: PHP-DI container
12. **CQRS-like**: Separate Query objects from Commands

## Development Standards

### Code Organization

- **PSR-4 Autoloading**: Standard PHP namespace structure
- **PSR-12 Code Style**: Consistent formatting
- **PHPStan**: Static analysis for type safety and bug prevention
- **Type Declarations**: Strict typing throughout codebase

### Testing Strategy

- **Unit Tests**: Domain logic testing
- **Behavior Tests**: Integration testing with Behat
- **Code Coverage**: Comprehensive test coverage
- **Continuous Integration**: Automated testing pipeline

## Future Considerations

### Planned Enhancements

- **Additional Drivers**: Support for non-Kubernetes clusters beyond Docker Compose
- **Advanced Monitoring**: Enhanced observability and metrics

Commercial extensions are not a future consideration either: the extension system described above is live,
and an enabled extension documents its own features.

### Extensibility Points

- Custom cluster drivers
- Custom hooks
- Custom compiler extensions
- Custom authentication providers
- Custom storage backends
