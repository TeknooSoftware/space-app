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
│  │          │  │  (AMQP)  │  │          │  │(Kubernetes)│   │
│  └──────────┘  └──────────┘  └──────────┘  └────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. Recipe Pattern

Space uses the **Recipe pattern** from Teknoo East Foundation for workflow orchestration:

- **Plans**: High-level workflows combining multiple steps
- **Steps**: Individual operations implementing specific use cases
- **EditablePlan**: Dynamic plans that can be modified through extensions during the service container compilation

### 2. Messenger-Based Workers

The application uses Symfony Messenger for asynchronous processing:

- **New Job Worker**: Receives and initializes deployment requests
- **Execute Job Worker**: Builds and deploys projects using East PaaS
- **History Sent Worker**: Persists deployment history events
- **Job Done Worker**: Finalizes completed deployments

### 3. Multi-Tenancy Model

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

### 4. Security & Authentication

- **OAuth2 Integration**: Third-party authentication support
- **Multi-Factor Authentication (MFA)**: TOTP-based 2FA with QR code generation
- **JWT Tokens**: API authentication
- **Symfony Security**: Role-based access control (ROLE_USER, ROLE_ADMIN)
- **Variable Encryption**: RSA/DSA encryption for sensitive persisted variables

### 5. East PaaS Integration

Space leverages **Teknoo East PaaS** for deployment orchestration:

- **Compilation**: Transforms `.paas.yaml` configurations into compilation and deployment plan
- **Make**: Pre/post deployment hooks (Composer, NPM, PIP, Make, Symfony Console, Laravel Artisan)
- **Image Building**: Uses Buildah to create OCI-compliant container images
- **Deployment**: Applies resources to a clusters like Kubernetes

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

- **PHP 8.4+**: Modern PHP with type safety and performance
- **Symfony 6.4+/7.3+**: Web framework and components
- **Doctrine ODM 3.5+**: MongoDB object-document mapper
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
- **Kubernetes 1.30+**: Container orchestration platform

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

- **Enterprise Edition**: Additional features (BigBang library, Trivy audit, backup, AI assistant, webhooks)
- **Additional Drivers**: Support for non-Kubernetes clusters
- **Advanced Monitoring**: Enhanced observability and metrics

### Extensibility Points

- Custom cluster drivers
- Custom hooks
- Custom compiler extensions
- Custom authentication providers
- Custom storage backends
