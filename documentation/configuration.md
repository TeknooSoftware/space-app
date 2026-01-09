# Configuration Guide

## Overview

Space is configured primarily through environment variables, allowing flexible deployment across different
environments. This guide covers all configuration options available in Space Standard Edition.

## Configuration Methods

Configuration can be set through:

1. **Environment Variables**: System environment variables
2. **`.env` Files**: Symfony's `.env.local` file in the `appliance` directory
3. **PHP Configuration Files**: For complex structures (arrays, objects)
4. **JSON Configuration Files**: For subscription plan and cluster definition

**Priority Order** (highest to lowest):

1. System environment variables
2. `.env.local` file
3. `.env` file (default values)

## Core Configuration

### Application Settings

#### APP_ENV

- **Type**: String
- **Values**: `dev`, `prod`, `test`
- **Default**: `prod`
- **Description**: Application environment mode
- **Production**: Always use `prod`

```bash
APP_ENV=prod
```

#### APP_SECRET

- **Type**: String (random)
- **Required**: Yes
- **Description**: Secret key for Symfony framework (CSRF, encryption)
- **Generation**: Use `php -r "echo bin2hex(random_bytes(32));"`

```bash
APP_SECRET=your_random_32_char_secret_here
```

#### APP_REMEMBER_SECRET

- **Type**: String (random)
- **Required**: Yes
- **Description**: Secret for "remember me" functionality
- **Generation**: Use `php -r "echo bin2hex(random_bytes(32));"`

```bash
APP_REMEMBER_SECRET=another_random_32_char_secret
```

#### SPACE_HOSTNAME

- **Type**: String (URL)
- **Required**: Yes
- **Description**: Public URL of your Space instance
- **Example**: `https://space.example.com`

```bash
SPACE_HOSTNAME=https://space.example.com
```

## Database Configuration

### MongoDB Connection

#### MONGODB_SERVER

- **Type**: String (MongoDB URI)
- **Required**: Yes
- **Description**: MongoDB connection string
- **Format**: `mongodb://[username:password@]host[:port][/database][?options]`

```bash
# Simple connection
MONGODB_SERVER=mongodb://localhost:27017

# With authentication
MONGODB_SERVER=mongodb://space_user:password@localhost:27017

# Replica set
MONGODB_SERVER=mongodb://user:pass@host1:27017,host2:27017,host3:27017/?replicaSet=rs0

# With SSL/TLS
MONGODB_SERVER=mongodb://user:pass@host:27017/?ssl=true
```

#### MONGODB_NAME

- **Type**: String
- **Required**: Yes
- **Description**: Database name for Space
- **Default**: `space`

```bash
MONGODB_NAME=space
```

## Message Queue Configuration

### Symfony Messenger Transports

#### MESSENGER_NEW_JOB_DSN

- **Type**: String (DSN)
- **Required**: Yes
- **Description**: Transport for new job creation messages
- **Format**: `amqp://user:pass@host:port/vhost/queue`

```bash
MESSENGER_NEW_JOB_DSN=amqp://space_user:password@localhost:5672/%2f/new_job
```

#### MESSENGER_EXECUTE_JOB_DSN

- **Type**: String (DSN)
- **Required**: Yes
- **Description**: Transport for job execution messages

```bash
MESSENGER_EXECUTE_JOB_DSN=amqp://space_user:password@localhost:5672/%2f/execute_job
```

#### MESSENGER_HISTORY_SENT_DSN

- **Type**: String (DSN)
- **Required**: Yes
- **Description**: Transport for history persistence messages

```bash
MESSENGER_HISTORY_SENT_DSN=amqp://space_user:password@localhost:5672/%2f/history_sent
```

#### MESSENGER_JOB_DONE_DSN

- **Type**: String (DSN)
- **Required**: Yes
- **Description**: Transport for job completion messages

```bash
MESSENGER_JOB_DONE_DSN=amqp://space_user:password@localhost:5672/%2f/job_done
```

## Email Configuration

### Mailer Settings

#### MAILER_DSN

- **Type**: String (DSN)
- **Required**: For email functionality
- **Description**: Email transport configuration
- **Format**: `protocol://user:pass@host:port`

```bash
# SMTP
MAILER_DSN=smtp://user:password@mail.example.com:587

# SendGrid
MAILER_DSN=sendgrid://API_KEY@default

# Gmail
MAILER_DSN=gmail+smtp://username:password@default

# Local sendmail
MAILER_DSN=sendmail://default

# Disable emails
MAILER_DSN=null://null
```

#### MAILER_SENDER_ADDRESS

- **Type**: String (email)
- **Optional**: Yes
- **Description**: Default sender email address used by the application

```bash
MAILER_SENDER_ADDRESS=no-reply@space.example.com
```

#### MAILER_SENDER_NAME

- **Type**: String
- **Optional**: Yes
- **Description**: Default sender display name

```bash
MAILER_SENDER_NAME=Space Platform
```

#### MAILER_FORBIDDEN_WORDS

- **Type**: String (comma-separated)
- **Optional**: Yes
- **Description**: Comma-separated forbidden words to filter emails content

```bash
MAILER_FORBIDDEN_WORDS=spam,viagra,lottery
```

#### SPACE_MAIL_MAX_ATTACHMENTS

- **Type**: Integer
- **Optional**: Yes
- **Default**: `5`
- **Description**: Maximum number of attachments allowed per email

```bash
SPACE_MAIL_MAX_ATTACHMENTS=5
```

#### SPACE_MAIL_MAX_FILE_SIZE

- **Type**: Integer (bytes)
- **Optional**: Yes
- **Default**: `204800`
- **Description**: Maximum file size per attachment in bytes

```bash
SPACE_MAIL_MAX_FILE_SIZE=204800
```

## User and UI Configuration

### Support Contact

#### SPACE_SUPPORT_CONTACT

- **Type**: String (email or URL)
- **Optional**: Yes
- **Description**: Contact email address or URI for support displayed in the UI

```bash
SPACE_SUPPORT_CONTACT=support@space.example.com
```

### Two-Factor Authentication (2FA)

#### SPACE_2FA_PROVIDER

- **Type**: String
- **Optional**: Yes
- **Default**: `google`
- **Values**: `google`, `generic`
- **Description**: Two factor provider to use

```bash
SPACE_2FA_PROVIDER=google
```

## Session Storage

### Redis (sessions)

#### SPACE_REDIS_HOST

- **Type**: String (hostname)
- **Optional**: Yes
- **Description**: Redis host used for sessions

```bash
SPACE_REDIS_HOST=redis
```

#### SPACE_REDIS_PORT

- **Type**: Integer
- **Optional**: Yes
- **Default**: `6379`
- **Description**: Redis port used for sessions

```bash
SPACE_REDIS_PORT=6379
```

## Authentication

### JWT Configuration

#### SPACE_JWT_SECRET_KEY

- **Type**: String (file path)
- **Required**: Yes (if JWT enabled)
- **Description**: Path to the private key used to sign JWT tokens

```bash
SPACE_JWT_SECRET_KEY=/opt/space/jwt/private.pem
```

#### SPACE_JWT_PUBLIC_KEY

- **Type**: String (file path)
- **Required**: Yes (if JWT enabled)
- **Description**: Path to the public key used to verify JWT tokens

```bash
SPACE_JWT_PUBLIC_KEY=/opt/space/jwt/public.pem
```

#### SPACE_JWT_PASSPHRASE

- **Type**: String
- **Optional**: Yes (if private key is protected)
- **Description**: Passphrase to unlock the private key

```bash
SPACE_JWT_PASSPHRASE=change_this_passphrase
```

#### SPACE_JWT_TTL

- **Type**: Integer (seconds)
- **Required**: Yes
- **Description**: Token time-to-live in seconds

```bash
SPACE_JWT_TTL=3600
```

#### SPACE_JWT_ENABLE_IN_QUERY

- **Type**: Boolean (0/1)
- **Optional**: Yes
- **Description**: Allow JWT token to be passed via query string

```bash
SPACE_JWT_ENABLE_IN_QUERY=0
```

#### SPACE_JWT_MAX_DAYS_TO_TIVE

- **Type**: Integer (days)
- **Optional**: Yes
- **Default**: `30`
- **Description**: Maximum life in days for JWT token

```bash
SPACE_JWT_MAX_DAYS_TO_TIVE=30
```

### OAuth Providers

#### OAUTH_ENABLED

- **Type**: Boolean (0/1)
- **Optional**: Yes
- **Description**: Enable or disable OAuth login buttons in the UI

```bash
OAUTH_ENABLED=1
```

#### OAUTH_SERVER_TYPE

- **Type**: String
- **Optional**: Yes
- **Description**: Provider type when using a generic/custom server

```bash
OAUTH_SERVER_TYPE=gitlab
```

#### DigitalOcean

##### OAUTH_DO_CLIENT_ID

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client id for DigitalOcean

##### OAUTH_DO_CLIENT_SECRET

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client secret for DigitalOcean

#### GitHub

##### OAUTH_GH_CLIENT_ID

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client id for GitHub

##### OAUTH_GH_CLIENT_SECRET

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client secret for GitHub

#### GitLab

##### OAUTH_GITLAB_CLIENT_ID

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client id for GitLab

##### OAUTH_GITLAB_CLIENT_SECRET

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client secret for GitLab

##### OAUTH_GITLAB_SERVER_URL

- **Type**: String (URL)
- **Optional**: Yes
- **Description**: Base URL of your GitLab instance (for self-hosted)

#### Google

##### OAUTH_GOOGLE_CLIENT_ID

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client id for Google

##### OAUTH_GOOGLE_CLIENT_SECRET

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client secret for Google

#### Jira

##### OAUTH_JIRA_CLIENT_ID

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client id for Jira

##### OAUTH_JIRA_CLIENT_SECRET

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client secret for Jira

#### Microsoft

##### OAUTH_MS_CLIENT_ID

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client id for Microsoft

##### OAUTH_MS_CLIENT_SECRET

- **Type**: String
- **Optional**: Yes
- **Description**: OAuth client secret for Microsoft

## Kubernetes Configuration

### Single Cluster Configuration (Legacy)

#### SPACE_KUBERNETES_MASTER

- **Type**: String (URL)
- **Required**: Yes (if not using cluster catalog)
- **Description**: Kubernetes API server URL

```bash
SPACE_KUBERNETES_MASTER=https://kubernetes.example.com:6443
```

#### SPACE_KUBERNETES_CREATE_TOKEN

- **Type**: String (JWT)
- **Required**: Yes
- **Description**: Service account token for namespace creation
- **Permissions**: Create namespaces, roles, rolebindings, service accounts

```bash
SPACE_KUBERNETES_CREATE_TOKEN=eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
```

#### SPACE_KUBERNETES_DASHBOARD

- **Type**: String (URL)
- **Optional**: Yes
- **Description**: Kubernetes Dashboard URL for embedding

```bash
SPACE_KUBERNETES_DASHBOARD=https://dashboard.kubernetes.example.com
```

#### SPACE_KUBERNETES_CA_VALUE

- **Type**: String (Base64 PEM)
- **Optional**: Yes
- **Description**: Custom CA certificate for Kubernetes API

```bash
SPACE_KUBERNETES_CA_VALUE=LS0tLS1CRUdJTi...
```

#### SPACE_CLUSTER_NAME

- **Type**: String
- **Required**: Yes (if not using cluster catalog)
- **Description**: Cluster name shown in UI

```bash
SPACE_CLUSTER_NAME=production
```

#### SPACE_CLUSTER_TYPE

- **Type**: String
- **Optional**: Yes
- **Default**: `kubernetes`
- **Description**: Cluster type identifier

```bash
SPACE_CLUSTER_TYPE=kubernetes
```

### Multiple Clusters Configuration

Use **one** of these options:

#### SPACE_CLUSTER_CATALOG_JSON

- **Type**: JSON string
- **Description**: Cluster catalog as JSON

```bash
SPACE_CLUSTER_CATALOG_JSON='[{
  "name": "production",
  "type": "kubernetes",
  "master": "https://k8s-prod.example.com:6443",
  "dashboard": "https://dashboard-prod.example.com",
  "create_account": {
    "token": "eyJhbGciOiJSUzI1...",
    "ca_cert": "LS0tLS1CRUdJTi..."
  },
  "storage_provisioner": "nfs.csi.k8s.io",
  "support_registry": true,
  "use_hnc": false
}]'
```

#### SPACE_CLUSTER_CATALOG_FILE

- **Type**: String (file path)
- **Description**: JSON file returning cluster array

```bash
SPACE_CLUSTER_CATALOG_FILE=/opt/space/config/clusters.php
```

**File format** (`/opt/space/config/clusters.php`):

```php
<?php
return [
    [
        'name' => 'production',
        'type' => 'kubernetes',
        'master' => 'https://k8s-prod.example.com:6443',
        'dashboard' => 'https://dashboard-prod.example.com',
        'create_account' => [
            'token' => 'eyJhbGciOiJSUzI1...',
            'ca_cert' => 'LS0tLS1CRUdJTi...',
        ],
        'storage_provisioner' => 'nfs.csi.k8s.io',
        'support_registry' => true,
        'use_hnc' => false,
    ],
    [
        'name' => 'staging',
        'type' => 'kubernetes',
        'master' => 'https://k8s-staging.example.com:6443',
        'create_account' => [
            'token' => 'eyJhbGciOiJSUzI1...',
        ],
        'support_registry' => false,
        'use_hnc' => false,
    ],
];
```

### Kubernetes Client Settings

#### SPACE_KUBERNETES_CLIENT_TIMEOUT

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Default**: `3`
- **Description**: Timeout for Kubernetes API requests

```bash
SPACE_KUBERNETES_CLIENT_TIMEOUT=5
```

#### SPACE_KUBERNETES_CLIENT_VERIFY_SSL

- **Type**: Boolean (0/1)
- **Optional**: Yes
- **Default**: `1`
- **Description**: Enable SSL certificate verification

```bash
SPACE_KUBERNETES_CLIENT_VERIFY_SSL=1
```

### Kubernetes Namespace Configuration

#### SPACE_KUBERNETES_ROOT_NAMESPACE

- **Type**: String (prefix)
- **Optional**: Yes
- **Default**: `space-client-`
- **Description**: Prefix for client namespaces

```bash
SPACE_KUBERNETES_ROOT_NAMESPACE=space-client-
```

#### SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE

- **Type**: String (prefix)
- **Optional**: Yes
- **Default**: `space-registry-`
- **Description**: Prefix for registry namespaces

```bash
SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE=space-registry-
```

#### SPACE_KUBERNETES_SECRET_ACCOUNT_TOKEN_WAITING_TIME

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Default**: `5`
- **Description**: Max wait time for service account token creation

```bash
SPACE_KUBERNETES_SECRET_ACCOUNT_TOKEN_WAITING_TIME=10
```

### Kubernetes Resource Defaults

#### SPACE_STORAGE_CLASS

- **Type**: String
- **Optional**: Yes
- **Default**: `nfs.csi.k8s.io`
- **Description**: Default storage class for PVCs

```bash
SPACE_STORAGE_CLASS=standard
```

#### SPACE_STORAGE_DEFAULT_SIZE

- **Type**: String
- **Optional**: Yes
- **Default**: `3Gi`
- **Description**: Default PVC size

```bash
SPACE_STORAGE_DEFAULT_SIZE=5Gi
```

#### SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS

- **Type**: String
- **Optional**: Yes
- **Default**: `public`
- **Description**: Default ingress class

```bash
SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS=nginx
```

#### SPACE_CLUSTER_ISSUER

- **Type**: String
- **Optional**: Yes
- **Default**: `lets-encrypt`
- **Description**: Default cert-manager cluster issuer

```bash
SPACE_CLUSTER_ISSUER=letsencrypt-prod
```

### Kubernetes Ingress Annotations

Use **one** of these options:

#### SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_JSON

- **Type**: JSON string
- **Description**: Default annotations for ingresses

```bash
SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_JSON='{"nginx.ingress.kubernetes.io/ssl-redirect":"true"}'
```

#### SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_FILE

- **Type**: String (file path)
- **Description**: JSON file returning annotations array

```bash
SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_FILE=/opt/space/config/ingress-annotations.php
```

## OCI Registry Configuration

### Private Registry Settings

#### SPACE_OCI_REGISTRY_IMAGE

- **Type**: String
- **Optional**: Yes
- **Default**: `registry:latest`
- **Description**: OCI registry Docker image

```bash
SPACE_OCI_REGISTRY_IMAGE=registry:2
```

#### SPACE_OCI_REGISTRY_URL

- **Type**: String (URL template)
- **Required**: Yes (if using private registries)
- **Description**: URL template for account registries
- **Format**: `{account-slug}.registry.example.com`

```bash
SPACE_OCI_REGISTRY_URL={account}.registry.example.com
```

#### SPACE_OCI_REGISTRY_TLS_SECRET

- **Type**: String
- **Optional**: Yes
- **Default**: `registry-certs`
- **Description**: Kubernetes secret name for registry TLS

```bash
SPACE_OCI_REGISTRY_TLS_SECRET=registry-tls
```

#### SPACE_OCI_REGISTRY_PVC_SIZE

- **Type**: String
- **Optional**: Yes
- **Default**: `4Gi`
- **Description**: PVC size for private registries

```bash
SPACE_OCI_REGISTRY_PVC_SIZE=10Gi
```

#### SPACE_OCI_REGISTRY_REQUESTS_CPU

- **Type**: String
- **Optional**: Yes
- **Default**: `10m`
- **Description**: CPU requests for registry pods

```bash
SPACE_OCI_REGISTRY_REQUESTS_CPU=50m
```

#### SPACE_OCI_REGISTRY_REQUESTS_MEMORY

- **Type**: String
- **Optional**: Yes
- **Default**: `30Mi`
- **Description**: Memory requests for registry pods

```bash
SPACE_OCI_REGISTRY_REQUESTS_MEMORY=64Mi
```

#### SPACE_OCI_REGISTRY_LIMITS_CPU

- **Type**: String
- **Optional**: Yes
- **Default**: `100m`
- **Description**: CPU limits for registry pods

```bash
SPACE_OCI_REGISTRY_LIMITS_CPU=200m
```

#### SPACE_OCI_REGISTRY_LIMITS_MEMORY

- **Type**: String
- **Optional**: Yes
- **Default**: `256Mi`
- **Description**: Memory limits for registry pods

```bash
SPACE_OCI_REGISTRY_LIMITS_MEMORY=512Mi
```

### Global Registry Settings

#### SPACE_OCI_GLOBAL_REGISTRY_URL

- **Type**: String (URL)
- **Optional**: Yes
- **Description**: Global OCI registry accessible by all deployments

```bash
SPACE_OCI_GLOBAL_REGISTRY_URL=registry.example.com
```

#### SPACE_OCI_GLOBAL_REGISTRY_USERNAME

- **Type**: String
- **Optional**: Yes
- **Description**: Username for global registry

```bash
SPACE_OCI_GLOBAL_REGISTRY_USERNAME=space
```

#### SPACE_OCI_GLOBAL_REGISTRY_PWD

- **Type**: String
- **Optional**: Yes
- **Description**: Password for global registry

```bash
SPACE_OCI_GLOBAL_REGISTRY_PWD=SecurePassword
```

## Encryption Configuration

### East PaaS Encryption

Used for encrypting messages between servers and workers.

#### TEKNOO_PAAS_SECURITY_ALGORITHM

- **Type**: String
- **Values**: `rsa`, `dsa`
- **Optional**: Yes (but recommended)
- **Description**: Encryption algorithm

```bash
TEKNOO_PAAS_SECURITY_ALGORITHM=rsa
```

#### TEKNOO_PAAS_SECURITY_PRIVATE_KEY

- **Type**: String (file path)
- **Required**: If encryption enabled
- **Description**: Path to private key for decryption

```bash
TEKNOO_PAAS_SECURITY_PRIVATE_KEY=/opt/space/config/secrets/private.pem
```

#### TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE

- **Type**: String
- **Optional**: Yes
- **Description**: Passphrase to unlock private key

```bash
TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE=YourPassphrase
```

#### TEKNOO_PAAS_SECURITY_PUBLIC_KEY

- **Type**: String (file path)
- **Required**: If encryption enabled
- **Description**: Path to public key for encryption

```bash
TEKNOO_PAAS_SECURITY_PUBLIC_KEY=/opt/space/config/secrets/public.pem
```

### Persisted Variables Encryption

Used for encrypting stored secrets in database.

#### SPACE_PERSISTED_VAR_AGENT_MODE

- **Type**: Boolean (0/1)
- **Optional**: Yes
- **Description**: Force agent mode (auto-enabled for CLI)

```bash
SPACE_PERSISTED_VAR_AGENT_MODE=1
```

#### SPACE_PERSISTED_VAR_SECURITY_ALGORITHM

- **Type**: String
- **Values**: `rsa`, `dsa`
- **Optional**: Yes (but recommended)
- **Description**: Encryption algorithm for variables

```bash
SPACE_PERSISTED_VAR_SECURITY_ALGORITHM=rsa
```

#### SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY

- **Type**: String (file path)
- **Required**: If encryption enabled
- **Description**: Path to private key

```bash
SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY=/opt/space/config/secrets/var-private.pem
```

#### SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE

- **Type**: String
- **Optional**: Yes
- **Description**: Passphrase for private key

```bash
SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE=VarPassphrase
```

#### SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY

- **Type**: String (file path)
- **Required**: If encryption enabled
- **Description**: Path to public key

```bash
SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY=/opt/space/config/secrets/var-public.pem
```

## Subscription Configuration

### Account Subscription Settings

#### SPACE_CODE_SUBSCRIPTION_REQUIRED

- **Type**: Boolean (0/1)
- **Optional**: Yes
- **Default**: `0`
- **Description**: Require subscription code for registration

```bash
SPACE_CODE_SUBSCRIPTION_REQUIRED=1
```

#### SPACE_CODE_GENERATOR_SALT

- **Type**: String
- **Optional**: Yes
- **Description**: Salt for subscription code generation

```bash
SPACE_CODE_GENERATOR_SALT=YourRandomSalt
```

#### SPACE_SUBSCRIPTION_DEFAULT_PLAN

- **Type**: String
- **Optional**: Yes
- **Description**: Default plan ID for new accounts

```bash
SPACE_SUBSCRIPTION_DEFAULT_PLAN=free
```

### Subscription Plan Catalog

Use **one** of these options:

#### SPACE_SUBSCRIPTION_PLAN_CATALOG_JSON

- **Type**: JSON string
- **Description**: Subscription plans as JSON

```bash
SPACE_SUBSCRIPTION_PLAN_CATALOG_JSON='[{
  "id": "free",
  "name": "Free Plan",
  "envsCountAllowed": 1,
  "quotas": [
    {"category": "compute", "type": "cpu", "capacity": "1000m", "require": "100m"},
    {"category": "memory", "type": "memory", "capacity": "2Gi", "require": "256Mi"}
  ]
}]'
```

#### SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE

- **Type**: String (file path)
- **Description**: JSON file returning plans array

```bash
SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE=/opt/space/config/plans.php
```

**File format** (`/opt/space/config/plans.php`):

```php
<?php
return [
    [
        'id' => 'free',
        'name' => 'Free Plan',
        'envsCountAllowed' => 1,
        'quotas' => [
            [
                'category' => 'compute',
                'type' => 'cpu',
                'capacity' => '1000m',
                'require' => '100m',
            ],
            [
                'category' => 'memory',
                'type' => 'memory',
                'capacity' => '2Gi',
                'require' => '256Mi',
            ],
        ],
    ],
    [
        'id' => 'pro',
        'name' => 'Professional Plan',
        'envsCountAllowed' => 5,
        'quotas' => [
            [
                'category' => 'compute',
                'type' => 'cpu',
                'capacity' => '10000m',
                'require' => '500m',
            ],
            [
                'category' => 'memory',
                'type' => 'memory',
                'capacity' => '20Gi',
                'require' => '2Gi',
            ],
        ],
        'clusters' => ['production', 'staging'],
    ],
];
```

## Worker Configuration

### Job Execution Settings

#### SPACE_JOB_ROOT

- **Type**: String (path)
- **Optional**: Yes
- **Default**: `/tmp`
- **Description**: Working directory for job execution

```bash
SPACE_JOB_ROOT=/var/lib/space/jobs
```

#### SPACE_WORKER_TIME_LIMIT

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Description**: Maximum time allowed for job execution

```bash
SPACE_WORKER_TIME_LIMIT=3600
```

#### SPACE_GIT_TIMEOUT

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Description**: Maximum time for Git clone operations

```bash
SPACE_GIT_TIMEOUT=600
```

### Image Building Settings

#### SPACE_IMG_BUILDER_CMD

- **Type**: String
- **Optional**: Yes
- **Default**: `buildah`
- **Description**: OCI image builder command

```bash
SPACE_IMG_BUILDER_CMD=buildah
```

#### SPACE_IMG_BUILDER_TIMEOUT

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Description**: Maximum time for image building

```bash
SPACE_IMG_BUILDER_TIMEOUT=1800
```

#### SPACE_IMG_BUILDER_PLATFORMS

- **Type**: String
- **Optional**: Yes
- **Default**: `linux/amd64`
- **Description**: Target platforms for images

```bash
SPACE_IMG_BUILDER_PLATFORMS=linux/amd64,linux/arm64
```

### Worker Health Check

#### SPACE_PING_FILE

- **Type**: String (path)
- **Optional**: Yes
- **Default**: `/tmp/ping_file`
- **Description**: Health check file path

```bash
SPACE_PING_FILE=/var/run/space/ping
```

#### SPACE_PING_SECONDS

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Default**: `60`
- **Description**: Interval between health check updates

```bash
SPACE_PING_SECONDS=30
```

## PaaS Compilation Configuration

### Hooks Configuration

Use **one** of these options:

#### SPACE_HOOKS_COLLECTION_JSON

- **Type**: JSON string
- **Description**: Available hooks as JSON

```bash
SPACE_HOOKS_COLLECTION_JSON='[{
  "name": "composer-install",
  "type": "composer",
  "command": ["$PATH/composer", "install", "--no-dev"],
  "timeout": 300
}]'
```

#### SPACE_HOOKS_COLLECTION_FILE

- **Type**: String (file path)
- **Description**: JSON file returning hooks array

```bash
SPACE_HOOKS_COLLECTION_FILE=/opt/space/config/hooks.php
```

### Global Variables

Use **one** of these options:

#### SPACE_PAAS_GLOBAL_VARIABLES_JSON

- **Type**: JSON string
- **Description**: Global variables for all deployments

```bash
SPACE_PAAS_GLOBAL_VARIABLES_JSON='{"APP_ENV":"production","TIMEZONE":"UTC"}'
```

#### SPACE_PAAS_GLOBAL_VARIABLES_FILE

- **Type**: String (file path)
- **Description**: PHP file returning variables array

```bash
SPACE_PAAS_GLOBAL_VARIABLES_FILE=/opt/space/config/global-vars.php
```

### Extends Libraries

For pods, containers, services, and ingresses:

#### SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_JSON / _FILE

#### SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_JSON / _FILE

#### SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_JSON / _FILE

#### SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_JSON / _FILE

See Enterprise Edition documentation for library configuration.

### Image Library

Use **one** of these options:

#### SPACE_PAAS_IMAGE_LIBRARY_JSON

- **Type**: JSON string
- **Description**: Embedded OCI image library

#### SPACE_PAAS_IMAGE_LIBRARY_FILE

- **Type**: String (file path)
- **Description**: Json file returning image library

## Mercure Configuration

### Real-Time Updates

#### SPACE_MERCURE_PUBLISHING_ENABLED

- **Type**: Boolean (0/1)
- **Optional**: Yes
- **Default**: `0`
- **Description**: Enable Mercure real-time updates

```bash
SPACE_MERCURE_PUBLISHING_ENABLED=1
```

#### MERCURE_PUBLISH_URL

- **Type**: String (URL)
- **Optional**: Yes (required if Mercure enabled)
- **Description**: Mercure hub URL for publishing

```bash
MERCURE_PUBLISH_URL=http://mercure:3000/.well-known/mercure
```

#### MERCURE_SUBSCRIBER_URL

- **Type**: String (URL)
- **Optional**: Yes (required if Mercure enabled)
- **Description**: Mercure URL for browser subscriptions

```bash
MERCURE_SUBSCRIBER_URL=https://mercure.example.com/.well-known/mercure
```

#### MERCURE_JWT_TOKEN

- **Type**: String (JWT)
- **Optional**: Yes (required if Mercure enabled)
- **Description**: JWT token for Mercure authentication

```bash
MERCURE_JWT_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Job Notification

#### SPACE_NEW_JOB_WAITING_TIME

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Description**: Wait time before redirecting to job page

```bash
SPACE_NEW_JOB_WAITING_TIME=3
```

## Extension System Configuration

### Extension Loader

#### TEKNOO_EAST_EXTENSION_DISABLED

- **Type**: Boolean (any non-empty value)
- **Optional**: Yes
- **Description**: Disable extension system

```bash
# To disable extensions
TEKNOO_EAST_EXTENSION_DISABLED=1

# To enable extensions (default)
# TEKNOO_EAST_EXTENSION_DISABLED=
```

#### TEKNOO_EAST_EXTENSION_LOADER

- **Type**: String (class name)
- **Optional**: Yes
- **Default**: `Teknoo\East\Foundation\Extension\FileLoader`
- **Values**:
    - `Teknoo\East\Foundation\Extension\FileLoader`
    - `Teknoo\East\Foundation\Extension\ComposerLoader`
- **Description**: Extension loader implementation

```bash
TEKNOO_EAST_EXTENSION_LOADER=Teknoo\East\Foundation\Extension\FileLoader
```

#### TEKNOO_EAST_EXTENSION_FILE

- **Type**: String (file path)
- **Optional**: Yes (required for FileLoader)
- **Default**: `extensions/enabled.json`
- **Description**: JSON file listing enabled extensions

```bash
TEKNOO_EAST_EXTENSION_FILE=/opt/space/extensions/enabled.json
```

**File format** (`extensions/enabled.json`):

```json
[
    "Acme\\SpaceExtension\\MyExtension",
    "Vendor\\AnotherExtension\\Extension"
]
```

## Example Complete Configuration

Here's a complete `.env.local` example for production:

```bash
###> symfony/framework-bundle ###
APP_ENV=prod
APP_SECRET=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
APP_REMEMBER_SECRET=z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4
###< symfony/framework-bundle ###

###> doctrine/mongodb-odm-bundle ###
MONGODB_SERVER=mongodb://space_user:SecurePass123@mongo1:27017,mongo2:27017,mongo3:27017/?replicaSet=rs0&ssl=true
MONGODB_NAME=space
###< doctrine/mongodb-odm-bundle ###

###> symfony/messenger ###
MESSENGER_NEW_JOB_DSN=amqp://space:RabbitPass456@rabbitmq:5672/%2f/new_job
MESSENGER_EXECUTE_JOB_DSN=amqp://space:RabbitPass456@rabbitmq:5672/%2f/execute_job
MESSENGER_HISTORY_SENT_DSN=amqp://space:RabbitPass456@rabbitmq:5672/%2f/history_sent
MESSENGER_JOB_DONE_DSN=amqp://space:RabbitPass456@rabbitmq:5672/%2f/job_done
###< symfony/messenger ###

###> symfony/mailer ###
MAILER_DSN=smtp://smtp.sendgrid.net:587?username=apikey&password=SG.xxx
###< symfony/mailer ###

###> space application ###
SPACE_HOSTNAME=https://space.example.com
###< space application ###

###> kubernetes ###
SPACE_CLUSTER_CATALOG_FILE=/opt/space/config/clusters.php
SPACE_KUBERNETES_CLIENT_TIMEOUT=5
SPACE_KUBERNETES_CLIENT_VERIFY_SSL=1
SPACE_KUBERNETES_ROOT_NAMESPACE=space-client-
SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE=space-registry-
SPACE_STORAGE_CLASS=fast-ssd
SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS=nginx
SPACE_CLUSTER_ISSUER=letsencrypt-prod
###< kubernetes ###

###> oci registry ###
SPACE_OCI_REGISTRY_URL={account}.registry.example.com
SPACE_OCI_REGISTRY_PVC_SIZE=10Gi
SPACE_OCI_GLOBAL_REGISTRY_URL=registry.example.com
SPACE_OCI_GLOBAL_REGISTRY_USERNAME=space
SPACE_OCI_GLOBAL_REGISTRY_PWD=RegistryPass789
###< oci registry ###

###> encryption ###
TEKNOO_PAAS_SECURITY_ALGORITHM=rsa
TEKNOO_PAAS_SECURITY_PRIVATE_KEY=/opt/space/config/secrets/paas-private.pem
TEKNOO_PAAS_SECURITY_PUBLIC_KEY=/opt/space/config/secrets/paas-public.pem

SPACE_PERSISTED_VAR_SECURITY_ALGORITHM=rsa
SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY=/opt/space/config/secrets/var-private.pem
SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY=/opt/space/config/secrets/var-public.pem
###< encryption ###

###> subscription ###
SPACE_CODE_SUBSCRIPTION_REQUIRED=1
SPACE_CODE_GENERATOR_SALT=MySuperSecretSalt
SPACE_SUBSCRIPTION_DEFAULT_PLAN=starter
SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE=/opt/space/config/plans.php
###< subscription ###

###> workers ###
SPACE_JOB_ROOT=/var/lib/space/jobs
SPACE_WORKER_TIME_LIMIT=3600
SPACE_GIT_TIMEOUT=600
SPACE_IMG_BUILDER_TIMEOUT=1800
SPACE_PING_FILE=/var/run/space/ping
###< workers ###

###> mercure ###
SPACE_MERCURE_PUBLISHING_ENABLED=1
MERCURE_PUBLISH_URL=http://mercure:3000/.well-known/mercure
MERCURE_SUBSCRIBER_URL=https://mercure.example.com/.well-known/mercure
MERCURE_JWT_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
###< mercure ###

###> extensions ###
TEKNOO_EAST_EXTENSION_LOADER=Teknoo\East\Foundation\Extension\FileLoader
TEKNOO_EAST_EXTENSION_FILE=/opt/space/extensions/enabled.json
###< extensions ###
```

## Configuration Validation

After configuration, validate your setup:

```bash
# Check Symfony configuration
./space.sh verify

# Test database connection
php bin/console doctrine:mongodb:schema:validate

# Test message queue connection
php bin/console messenger:stats

# Verify Kubernetes access
kubectl --kubeconfig=/path/to/kubeconfig cluster-info
```

## Security Best Practices

1. **Never commit `.env.local` to version control**
2. **Use strong random secrets** (32+ characters)
3. **Enable encryption** for sensitive data
4. **Restrict file permissions** on configuration files:
   ```bash
   chmod 600 /opt/space/appliance/.env.local
   chmod 600 /opt/space/config/secrets/*.pem
   ```
5. **Use environment-specific values** (different passwords per environment)
6. **Rotate secrets regularly**
7. **Enable TLS/SSL** for all external connections

## Troubleshooting

### Configuration Not Loading

- Check file permissions
- Verify environment variable syntax
- Ensure no typos in variable names
- Check for conflicting values

### Database Connection Failed

- Verify MONGODB_SERVER URI
- Check MongoDB is running
- Verify authentication credentials
- Check firewall rules

### Workers Not Processing

- Verify MESSENGER_*_DSN values
- Check RabbitMQ is running
- Verify queue names
- Check worker logs

## Related Documentation

- [Installation Guide](installation.md) - Installation procedures
- [Worker Documentation](worker.md) - Worker configuration
- [Requirements](requirements.md) - System requirements

For Enterprise Edition features and additional configuration options, contact richard@teknoo.software.
