# Domain Model

## Overview

Space follows **Domain-Driven Design (DDD)** principles with a rich domain model that encapsulates business logic and 
rules. The domain layer is completely independent of infrastructure concerns and focuses on the core business concepts 
of a Platform as a Service application.

## Bounded Contexts

The Space domain is organized into several bounded contexts:

### 1. Account Management
Manages tenant accounts, their settings, and access control.

### 2. User Management
Handles user authentication, authorization, and profile management.

### 3. Project Management
Manages application projects, their metadata, and configurations.

### 4. Job Management
Orchestrates deployment jobs and their lifecycle.

### 5. Cluster Management
Manages Kubernetes clusters and their configurations.

### 6. Variable Management
Handles persisted variables and secrets at account and project levels.

## Core Entities

### Account (+ AccountData)

The **Account** is the primary aggregate root representing a tenant in the multi-tenant system.
**Account** class come from the Teknoo East PaaS library. **AccountData** is a subclass (one-to-one) to extends 
Account and add more properties, about legal informationm and subscription plan.

**Properties:**
- `id`: Unique identifier
- `name`: Account name
- `namespace`: identifier used for namespace
- `prefixNamespace`: prefix used in the cluster's namespace implementatiion
- `quotas`: Resource quotas (CPU, memory, etc)
- `createdAt`: Creation timestamp
From **AccountData** 
- `legalName`: legal information
- `streetAddress`, `zipCode`, `cityName`, `countryName`: Address details
- `vatNumber`: VAT identification
- `subscriptionPlan`: Active subscription plan

**Relationships:**
- Has many **Users** (1..n)
- Has many **Projects** (0..n)
- Has many **AccountEnvironments** (1..n)
- Has many **AccountClusters** (0..n)
- Has one **AccountRegistry** (0..1)
- Has many **AccountPersistedVariables** (0..n)
- Has many **AccountHistory** entries (0..n)

**Business Rules:**
- An account must have at least one user
- Account slug must be unique
- Quotas are distributed across projects
- Each environment creates a cluster namespace

### User (+ UserData)

Represents a human user in the system. **User** class come from the Teknoo East Common library. **UserData** is a 
subclass (one-to-one) to extends User and add more properties like picture.

**Properties:**
- `id`: Unique identifier
- `email`: Email address (unique)
- `firstName`: User's first name
- `lastName`: User's last name
- `active`: User status
- `roles`: User roles (ROLE_USER, ROLE_ADMIN)
- `authData`: Hashed passzord, OAuth2 and MFA data (TOTP secret, recovery codes), etc..
- `createdAt`: Registration timestamp
Fom **UserData**:
- `picture` : a **Media** instance

**Relationships:**
- Belongs to one **Account**
- - Has one **Media** (0..1)

**Business Rules:**
- Email must be unique across all users
- Must have at least ROLE_USER
- Can enable Multi-Factor Authentication (TOTP)
- Passwords are salted and hashed

### Project (+ ProjectMetadata)

Represents an application project to be deployed. **Projectt** class come from the Teknoo East PaaS library. 
**ProjectMetadata** is a subclass (one-to-one) to extends Project and add more properties, like project's url

**Properties:**
- `id`: Unique identifier
- `name`: Project name
- `prefix`: To prefix all cluster's resources for this project when the namespace is shared
- `imageRegistryy`: OCI image repository URL to push built images
- `sourceRepository`: Git repository configuration
  - `pullUrl`: Git clone URL
  - `identity`: SSH key or credentials
  - `defaultBranch`: Default branch to deploy
- `createdAt`: Creation timestamp
  Fom **ProjectMetadata**:
- `projectUrl` : an url to access to the project

**Relationships:**
- Belongs to one **Account**
- Has many **ProjectPersistedVariables** (0..n)
- Has many **Cluster** (1..n)
- Generates many **Jobs** (0..n)

**Business Rules:**
- Project must be hosted on a Git instance (HTTPS or SSH)
- Images are built using Buildah
- Variables can be encrypted as secrets
- Slug must be unique within account

### Job

Represents a deployment job execution.

**Properties:**
- `id`: Unique identifier
- `project`: Associated project
- `environment`: Target environment
- `sourceRepository`: Git reference (branch, tag, commit)
- `imagesRegistry`: Target image repository to push built images
- `history`: Deployment events
- `quotas`: Quotas allowed to this deployment
- `defaults`: Default value of variables to pass to this deployment
- `extra`: To allow pass some extra stuff needed to run the deployment 
- `createdAt`: Job creation timestamp

**Lifecycle States:**
1. **Pending**: Job created, waiting for worker
2. **Validating**: Worker validate the job
3. **Executing**: Worker executing deployment
4. **Terminated**: Deployment completed sucessfully or failed with errors

**Business Rules:**
- Each job targets one environment
- Variables are merged from account → project → job
- Job history is immutable once recorded
- Secrets are encrypted in transit

### AccountEnvironment

Represents a deployment environment for an account.

**Properties:**
- `id`: Unique identifier
- `envName`: Environment name (e.g., "production", "staging")
- `clusterName`: Associated cluster
- `namespace`: Cluster namespace
- `serviceAccountName`, `roleName`, `roleBindingName`: Resources names needed to configure the cluster namespace
- `caCertificate`, `clientCertificate`, `clientKey`, `token`: Credential to connect to this cluster
-  `metadata`: To store some extra stuffs about this environment.
- `createdAt`: Creation timestamp

**Relationships:**
- Belongs to one **Account**

**Business Rules:**
- Each environment has a dedicated namespace
- Namespace follows pattern: `{prefix}{account-slug}-{environment-name}`
- Environments are quota-restricted based on subscription plan
- Requires service account token for Kubernetes access

### AccountCluster

Links an account to available Kubernetes clusters.

**Properties:**
- `id`: Unique identifier
- `name`
- `slug`
- `type`: type like Kubernetes or other
- `masterAddress`: URL to connect to the cluster
- `storageProvisioner`: Default name of the storage provisioner
- `dashboardAddress`: URL of the dashboardd
- `caCertificate`, `token`: Credentials to connect to this cluster
- `supportRegistry`: To flag this cluster is available to host the account registry
- `registryUrl`
- `useHnc`: If the cluster use Hierarchical namespace in Kubernetes

**Relationships:**
- References global **Cluster** configuration

**Business Rules:**
- Service account token provides scoped access
- Token creation must complete within timeout
- Different clusters may have different capabilities

### AccountRegistry

Represents a private OCI image registry for an account.

**Properties:**
- `id`: Unique identifier
- `registryNamespace`: Cluster's nameespace
- `registryUrl`: Endpoint to push and pull built images
- `registryConfigName`: Resource's name to store Registry credential
- `registryAccountName`, `registryPassword`: Registry credentials
- `persistentVolumeClaimName`: Name of the volume claim in the cluster

**Relationships:**
- Belongs to one **Account**

**Business Rules:**
- Each account can have one private registry
- Registry deployed in dedicated namespace
- Storage is claimed via PersistentVolumeClaim
- Registry credentials are managed as Kubernetes secrets
- Registry is shared for all environment and clusters
- Registry can be present on a different kubernetes cluster

### AccountHistory

Tracks deployment history and events for an account.

**Properties:**
- `id`: Unique identifier
- `history`: Last `History` instance about this account. Older instance or chained in its `previous`
- `createdAt`: Event timestamp

**Business Rules:**
- History is append-only (immutable)
- Events are ordered by serial number
- Final events mark job completion

### PersistedVariable (For Account or Project)

Represents a configuration variable that can be persisted and optionally encrypted.

**Properties:**
- `name`: Variable name
- `envName`: Environment's name where the persisted variable is available
- `value`: Variable value
- `secret`: Boolean flag indicating if value is secret
- `encryptionAlgorithm`: Encryption algorithm if secret

**Implementations:**
- **ProjectPersistedVariable**: Variables scoped to a project
- **AccountPersistedVariable**: Variables scoped to an account

**Business Rules:**
- Secrets are encrypted using RSA
- Non-secret values are stored in plain text
- Variables cascade: Account → Project → Job
- Project variables override account variables
- Job variables override project variables

### Configuration Objects

#### Cluster

Represents a Kubernetes cluster configuration.

**Properties:**
- `name`
- `sluggyName`: Cluster's name as slug
- `type`: like kubernetes or other
- `masterAddress`: Endpoint to connect
- `storageProvisioner`: Name of the storage provisioner 
- `dashboardAddress`: Endpoint to access to the dasboard
- `token`: Token to connect to this cluster and create namespace
- `supportRegistry`: This cluster can be use to store OCI registry
- `useHnc`: This cluster use hierarchical namespace
- `isExternal`: This cluster is user defined and not admin defined

#### SubscriptionPlan

Defines resource quotas and limits for accounts.

**Properties:**
- `id`: Plan identifier
- `name`: Human-readable name
- `quotas`: Resource quotas (compute, memory)
  - `category`: "compute" or "memory"
  - `type`: Quota type
  - `capacity`: Maximum limit
  - `require`: Minimum requirement (optional)
- `envsCountAllowed`: Maximum environments allowed
- `projectsCountAllowed`: Maximum projects allowed
- `clusters`: Allowed clusters (optional)

**Business Rules:**
- Quotas represent sum of all container limits
- Plans can restrict available clusters
- Environment count is enforced

#### ClusterCatalog

Collection of available clusters.

**Properties:**
- `clusters`: Array of Cluster objects

#### SubscriptionPlanCatalog

Collection of available subscription plans.

**Properties:**
- `plans`: Array of SubscriptionPlan objects

## Data Transfer Objects (DTOs)

DTOs facilitate data exchange between layers without exposing domain entities directly.

### SpaceAccount
Represents account data for API/UI interactions.

### SpaceUser
Represents user data for API/UI interactions.

### SpaceProject
Represents project data for API/UI interactions.

### SpaceView
General-purpose view data container.

### NewJob
Encapsulates data for creating a new deployment job.

### JobVarsSet
Collection of job variables.

### SpaceSubscription
Subscription and account creation data.

### AccountWallet
Full account's environements

### AccountEnvironmentResume
Summary of account environments.

### Contact
Contact form data.

### ContactAttachment
Email attachment data.

### JWTConfiguration
JWT token configuration and settings.

### Search
Search query parameters.

## Query Objects

Query objects represent read operations following CQRS-like patterns.

### Account Queries
- `FetchAccountFromUser`: Retrieve user's account

### User Queries
- `SearchQuery`: Search users with filters

### Project Queries
- `CountProjectsInAccount`: Count account's projects

### Environment Queries
- `LoadFromAccountQuery`: Load account environments

### Cluster Queries
- `LoadFromAccountQuery`: Load account clusters

### Variable Queries
- `LoadFromProjectQuery`: Load project variables
- `LoadFromAccountQuery`: Load account variables
- `DeleteVariablesQuery`: Remove variables

### Registry Queries
- `LoadFromAccountQuery`: Load account registry

## Contracts (Domain Services)

### Recipe Step Interfaces

Domain defines contracts for workflow steps without implementing them:

#### Job Management
- `CallNewJobInterface`: Create new deployment job
- `FetchJobIdFromPendingInterface`: Retrieve pending job
- `NewJobNotifierInterface`: Notify about new jobs

#### Kubernetes Operations
- `ClustersInfoInterface`: Retrieve cluster information
- `DashboardFrameInterface`: Generate dashboard embed
- `HealthInterface`: Check cluster health

#### Subscription
- `CreateAccountInterface`: Account creation workflow
- `CreateUserInterface`: User registration workflow
- `LoginUserInterface`: User authentication workflow

#### Contact
- `SendEmailInterface`: Send email notifications

#### User
- `JwtCreateTokenInterface`: Generate JWT tokens

## Business Rules Summary

### Multi-Tenancy
- Strict isolation between accounts
- Each account has its own Kubernetes namespaces
- Resources are quota-limited per account's environments

### Security
- All passwords are salted and hashed
- Secrets are encrypted at rest
- Service account tokens provide scoped access
- MFA support via TOTP

### Variable Precedence
Variables are merged with the following precedence (highest to lowest):
1. Job variables
2. Project persisted variables
3. Account persisted variables
4. Global variables

### Deployment Workflow
1. User creates job
2. Job enters pending state
3. Worker picks up job
4. Job enters executing state
5. Git repository cloned
6. Hooks executed (optional)
7. OCI images built
8. Kubernetes resources deployed
9. History events recorded
10. Job marked as complete/failed

### Resource Management
- Quotas defined at subscription plan level
- Enforced at Kubernetes level (requests/limits)
- Quota capacity = sum of all container limits
- Environment count restricted by plan
- Project count restricted by plan

### Project Requirements
- Must reference a Git repository
- Supports HTTPS and SSH protocols
- Requires `.paas.yaml` configuration file
- Images built using Buildah

## Validation Rules

### Account
- Name: Required, non-empty
- Email: Valid format, unique
- Slug: Alphanumeric + hyphens, unique

### User
- Email: Valid format, unique across all users
- Password: Minimum complexity requirements
- Roles: At least ROLE_USER required

### Project
- Name: Required, non-empty
- Git URL: Valid URL format
- Slug: Alphanumeric + hyphens, unique within account

### Environment
- Name: Required, alphanumeric + hyphens
- Cluster: Must exist in cluster catalog
- Namespace: Follows naming convention

### Variables
- Name: Valid environment variable format
- Secrets: Must specify encryption algorithm
- Values: Non-empty for non-secret variables

## Domain Services

Domain services encapsulate business logic that doesn't naturally belong to a single entity:

### Encryption Service
Handles encryption/decryption of persisted variables.
