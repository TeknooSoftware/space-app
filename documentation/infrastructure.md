# Infrastructure Layer

## Overview

The infrastructure layer implements technical adapters for external systems and frameworks, providing concrete
implementations of domain interfaces. It follows the **hexagonal architecture** pattern, isolating the domain from
technical concerns.

## Infrastructure Components

### 1. Doctrine ODM Integration

Space uses **Doctrine Object-Document Mapper (ODM)** for MongoDB persistence.

#### Repository Implementations

Located in `appliance/infrastructures/Doctrine/Repository/`:

**AccountDataRepository**

- Implements: `AccountDataRepositoryInterface`
- Persistence: MongoDB collection for accounts' data
- Features:
    - CRUD operations for accounts
    - Query by ID or slug
    - Unique constraint on email and slug

**UserDataRepository**

- Implements: `UserDataRepositoryInterface`
- Persistence: MongoDB collection for users' data
- Features:
    - User authentication queries
    - Search and filtering
    - Unique email constraint

**ProjectMetadataRepository**

- Implements: `ProjectMetadataRepositoryInterface`
- Persistence: MongoDB collection for projects' metadata
- Features:
    - Project CRUD operations
    - Account-scoped queries
    - Project counting

**AccountEnvironmentRepository**

- Implements: `AccountEnvironmentRepositoryInterface`
- Persistence: MongoDB collection for environments
- Features:
    - Environment management
    - Namespace tracking

**AccountClusterRepository**

- Implements: `AccountClusterRepositoryInterface`
- Persistence: MongoDB collection for accounts' clusters
- Features:
    - Cluster association management
    - Token storage

**AccountRegistryRepository**

- Implements: `AccountRegistryRepositoryInterface`
- Persistence: MongoDB collection for accouns' registries
- Features:
    - Registry configuration storage

**ProjectPersistedVariableRepository**

- Implements: `ProjectPersistedVariableRepositoryInterface`
- Persistence: MongoDB collection for projects' persisted variables
- Features:
    - Encrypted variable storage
    - Variable CRUD operations

**AccountPersistedVariableRepository**

- Implements: `AccountPersistedVariableRepositoryInterface`
- Persistence: MongoDB collection for account's persisted variables
- Features:
    - Account-scoped variables
    - Encryption support

**AccountHistoryRepository**

- Implements: `AccountHistoryRepositoryInterface`
- Persistence: MongoDB collection for history
- Features:
    - Append-only history
    - Serial number tracking
    - Pagination support

#### Form Types

Located in `appliance/infrastructures/Doctrine/Form/`:

Form types integrate Doctrine entities with Symfony Forms for validation and rendering:

- User form types

Located in `appliance/infrastructures/Symfony/Form/`:

Form types integrate Doctrine entities with Symfony Forms for validation and rendering:

- Account form types
- Account Environment
- User form types
- Project form types
- Contact form types

#### Document Mapping

Doctrine ODM uses xml mapping available in `appliance/config/doctrine`.

### 2. Kubernetes Integration

Located in `appliance/infrastructures/Kubernetes/`:

#### Transcribers

Transcribers convert domain objects to Kubernetes API resources.

**Ingress Transcribers**

#### Recipe Steps

Kubernetes-specific workflow steps:

**Account**

- Implement namespace (create and install)
- Catch Errorr
- Reinstall namespaec

**DashboardInfo**

- Generates Kubernetes Dashboard embed URLs
- Handles authentication tokens

**Health**

- Checks cluster connectivity
- Validates API access

**ClustersInfo**

- Retrieves available clusters
- Provides cluster metadata

### 3. Symfony Integration

Located in `appliance/infrastructures/Symfony/`:

#### Command Line Interface

Console commands for administrative tasks:

**Extension**

- List available extensions.
- Enable an extension.
- Disable an extension.

#### Messenger Integration

Symfony Messenger handles asynchronous job processing.

**Message Handlers**

Located in `appliance/infrastructures/Symfony/Messenger/Handler/`:

**NewJobHandler**

- Handles: `NewJobMessage`
- Action: Initializes deployment workflow
- Transport: RabbitMQ (`new_job` queue)

**ExecuteJobHandler**

- Handles: `ExecuteJobMessage`
- Action: Executes deployment via East PaaS
- Transport: RabbitMQ (`execute_job` queue)

**HistorySentHandler**

- Handles: `HistorySentMessage`
- Action: Persists deployment events
- Transport: RabbitMQ (`history_sent` queue)

**JobDoneHandler**

- Handles: `JobDoneMessage`
- Action: Finalizes completed jobs
- Transport: RabbitMQ (`job_done` queue)

#### Mercure Integration

Located in `appliance/infrastructures/Symfony/Mercure/`:

**Publisher**

- Publishes real-time updates to Mercure hub
- Notifies clients of job status changes to redirect them to job's page
- Enables live dashboard updates

**Subscriber Configuration**

- Configures browser subscriptions
- Handles JWT authentication for Mercure

#### Forms

Located in `appliance/infrastructures/Symfony/Form/`:

Symfony form types for web UI:

**Account Forms**

- AccountType: Create/edit accounts
- AccountSettingsType: Account configuration
- AccountClusterType
- AccountEnvironment
- VerSetType and VarsType
- Admin forms

**Project Forms**

- ProjectType: Create/edit projects
- ProjectMetadataType: Project settings
- VerSetType and VarsType
- Admin forms

**Job Forms**

- JobType: Create deployment jobs
- JobVarsType: Job variables

**User Forms**

- UserType: User management
- RegistrationType: User registration
- LoginType: Authentication

#### Security

Located in `appliance/infrastructures/Symfony/Security/`:

**Authenticators**

Build on `Teknoo East Common` authenticator, Space is bundled with `UserConverter` to import users
from an external repository.

**Voters**

Symfony Security voters for fine-grained authorization:

**AdmintVoter**

- Checks admin role

**AccountVoter**

- Checks account ownership
- Validates account access

**UserVoter**

- Validates user access

**ProjectVoter**

- Validates project access
- Checks account membership

**JobVoter**

- Validates job access
- Checks project ownership

#### Recipe Steps

Symfony-specific workflow steps:

**AccessControl**

- Check voter and apply ACL.

**Account Management Steps**

- Prepare Accounts'forms

**User Management Steps**

- Prepare Users'forms

**Mercure**

- Client call

### 4. Twig Integration

Located in `appliance/infrastructures/Twig/`:

#### Extensions

**SpaceExtension**

- Custom Twig functions
- Template helpers
- Formatting utilities

**Functions Provided:**

- `space_cluster_info`: Display cluster information
- `space_format_quota`: Format quota values
- `space_env_badge`: Render environment badges
- `space_job_status`: Display job status
- `space_format_date`: Date formatting
- `space_truncate`: Text truncation

### 5. Endroid QR Code Integration

Located in `appliance/infrastructures/Endroid/QrCode/`:

**QrCodeGenerator**

- Generates QR codes for TOTP setup
- Formats for authenticator apps
- Provides backup codes

## Message Transports

### RabbitMQ Configuration

**Exchange Types**

- Direct exchange for targeted routing
- Topic exchange for pattern-based routing

**Queues**

- `new_job`: New deployment requests
- `execute_job`: Job execution tasks
- `history_sent`: History persistence
- `job_done`: Job completion

**Durability**

- Durable queues survive restarts
- Persistent messages survive crashes
- Acknowledgment ensures delivery

**Dead Letter Queues**

- Failed messages routed to DLQ
- Manual inspection and retry
- Prevents message loss

## Configuration Management

### PHP-DI Container

Space uses PHP-DI for dependency injection:

**Configuration Files**

- `config/di/*.php`: Service definitions
- Autowiring for standard services
- Manual wiring for complex dependencies

