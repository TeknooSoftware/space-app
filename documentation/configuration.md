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

## Which Process Reads What

Space runs one web server and four workers (`new_task`, `execute_job`, `history_sent`, `job_done`, see
[worker.md](worker.md)). Since account provisioning moved to the `new_task` worker, each process only needs
the variables below; the compose files at the repository root apply this split per service.

| Variables                                                                                                                                                                                                                                                                 | Web                                                 | new_task                              | execute_job                               | history_sent / job_done |
|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------|---------------------------------------|-------------------------------------------|-------------------------|
| `APP_*`, `MONGODB_*`, `TEKNOO_EAST_EXTENSION_*`, `SPACE_HOSTNAME`, `MERCURE_PROTOCOL_VERSION` (compile-time, same value everywhere)                                                                                                                                                                                                         | yes                                                 | yes                                   | yes (no MongoDB for `execute_job`)        | yes                     |
| `MESSENGER_*_DSN`                                                                                                                                                                                                                                                         | `new_task` (producer)                               | `execute_job`, `history_sent`         | `execute_job`, `history_sent`, `job_done` | its own transport       |
| `TEKNOO_PAAS_SECURITY_*` (message encryption)                                                                                                                                                                                                                             | public key only                                     | public + private keys                 | public + private keys                     | public + private keys   |
| `SPACE_PERSISTED_VAR_SECURITY_*`                                                                                                                                                                                                                                          | public key, `AGENT_MODE=0`                          | public + private keys, `AGENT_MODE=1` | no                                        | no                      |
| `MERCURE_PUBLISH_URL`, `MERCURE_JWT_TOKEN`, `MERCURE_JWT_ISSUER`                                                                                                                                                                                                                                | yes                                                 | yes (`NewJob` updates)                | no                                        | no                      |
| `MERCURE_SUBSCRIBER_URL`, `MAILER_*`, `OAUTH_*`, `SPACE_JWT_*`, `SPACE_VALKEY_*`, `SPACE_2FA_PROVIDER`, `SPACE_SUPPORT_CONTACT`, `SPACE_CODE_*`, `SPACE_SUBSCRIPTION_*`, `SPACE_MAIL_*`, `SPACE_TRUSTED_HOSTS`                                                            | yes                                                 | no                                    | no                                        | no                      |
| Clusters catalog (`SPACE_CLUSTER_CATALOG_*` or `SPACE_CLUSTER_NAME`/`TYPE`, `SPACE_KUBERNETES_MASTER`/`DASHBOARD`/`CREATE_TOKEN`/`CA_VALUE`), `SPACE_KUBERNETES_CLIENT_*`, `SPACE_KUBERNETES_ROOT_NAMESPACE`                                                              | yes (dashboard, account clusters, namespace naming) | yes                                   | `SPACE_KUBERNETES_CLIENT_*` only          | no                      |
| `SPACE_KUBERNETES_CLUSTER_USE_HNC`, `SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE`, `SPACE_KUBERNETES_SECRET_ACCOUNT_TOKEN_WAITING_TIME`, `SPACE_CLUSTER_ISSUER`, `SPACE_OCI_REGISTRY_*`, `SPACE_OCI_GLOBAL_REGISTRY_*`, `SPACE_DC_REGISTRY_*`, `SPACE_NEW_TASK_WAITING_TIME` | no                                                  | yes                                   | no                                        | no                      |
| `SPACE_STORAGE_CLASS`, `SPACE_STORAGE_DEFAULT_SIZE`, `SPACE_JOB_ROOT`, `SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS`, `SPACE_DC_ANSIBLE_BINARY`, `SPACE_DC_TIMEOUT`, `SPACE_DC_DEPLOY_ROOT`                                                                                    | no                                                  | yes                                   | yes                                       | no                      |
| `SPACE_KUBERNETES_VERSION_LEVEL`, `SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_*`, `SPACE_INGRESS_PROVIDER_*`, `SPACE_HOOKS_COLLECTION_*`, `SPACE_PAAS_*`, `SPACE_GIT_TIMEOUT`, `SPACE_IMG_BUILDER_*`, other `SPACE_DC_*`                                                | no                                                  | no                                    | yes                                       | no                      |
| `SPACE_WORKER_TIME_LIMIT`                                                                                                                                                                                                                                                 | no                                                  | yes                                   | yes                                       | `history_sent`          |
| `SPACE_PING_FILE`, `SPACE_PING_SECONDS`                                                                                                                                                                                                                                   | no                                                  | yes                                   | yes                                       | yes                     |

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

#### MESSENGER_NEW_TASK_DSN

- **Type**: String (DSN)
- **Required**: Yes
- **Description**: Transport for new job creation messages
- **Format**: `amqp://user:pass@host:port/vhost/queue`

```bash
MESSENGER_NEW_TASK_DSN=amqp://space_user:password@localhost:5672/%2f/new_task
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

### Valkey (sessions)

Space stores HTTP sessions in a [Valkey](https://valkey.io/) server (BSD licensed, Redis protocol
compatible), accessed through the `phpredis` extension and Symfony's `RedisSessionHandler`.

#### SPACE_VALKEY_HOST

- **Type**: String (hostname)
- **Optional**: Yes
- **Description**: Valkey host used for sessions

```bash
SPACE_VALKEY_HOST=valkey
```

#### SPACE_VALKEY_PORT

- **Type**: Integer
- **Optional**: Yes
- **Default**: `6379`
- **Description**: Valkey port used for sessions

```bash
SPACE_VALKEY_PORT=6379
```

#### SPACE_REDIS_HOST / SPACE_REDIS_PORT (deprecated)

- **Optional**: Yes
- **Description**: Former names of `SPACE_VALKEY_HOST` / `SPACE_VALKEY_PORT`. They are still read as a
  fallback when the `SPACE_VALKEY_*` variables are not set or empty, so existing deployments keep
  working. New configurations must use the `SPACE_VALKEY_*` names.

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
- **Description**: Cluster type identifier. Selects the deployment target for this cluster. Supported values:
  `kubernetes` (deploy to a Kubernetes API) and `docker-compose` (deploy to a remote Docker host over SSH with
  Ansible — see [Docker Compose Configuration](#docker-compose-configuration)).

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

**Docker Compose cluster entry** — a cluster with `"type": "docker-compose"` targets a remote Docker host over **SSH**
(key-only authentication, no password, all operations rootless) instead of a Kubernetes API. It carries
an `ssh` block rather than `create_account`/`storage_provisioner`/`use_hnc`:

```bash
SPACE_CLUSTER_CATALOG_JSON='[{
  "name": "docker-prod-1",
  "type": "docker-compose",
  "master": "ssh://deployer@docker-host.example.com:22",
  "support_registry": true,
  "ssh": {
    "client_key": "-----BEGIN OPENSSH PRIVATE KEY-----\n...",
    "username": "deployer",
    "known_hosts": ""
  }
}]'
```

Field mapping for a docker-compose entry:

- `master` — SSH URL of the target host, `ssh://user@host:port` (the user may be embedded here or supplied via
  `ssh.username`). Stored on `masterAddress`.
- `ssh.client_key` — **required** SSH private key (PEM). Stored plaintext in the same `client_key` field used by
  the Kubernetes client key.
- `ssh.username` — *Optional* SSH user (falls back to the user embedded in `master`).
- `ssh.known_hosts` — *Optional* `known_hosts` host key. Stored in the same `ca_cert`/`caCertificate` field used
  by the Kubernetes CA.
- `support_registry` — *Optional*, defaults `true`. When enabled, Space provisions a **per-account private OCI
  registry** container on the same Docker host over Ansible (see
  [Docker Compose Configuration](#docker-compose-configuration)).

The `create_account.token`, `create_account.ca_cert`, `storage_provisioner`, and `use_hnc` keys are
Kubernetes-only and ignored for docker-compose entries.

#### SPACE_CLUSTER_CATALOG_FILE

- **Type**: String (file path)
- **Description**: JSON file returning cluster array

```bash
SPACE_CLUSTER_CATALOG_FILE=/opt/space/config/clusters.json
```

**File format** (`/opt/space/config/clusters.json`):

```json
[
    {
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
    },
    {
        "name": "staging",
        "type": "kubernetes",
        "master": "https://k8s-staging.example.com:6443",
        "create_account": {
            "token": "eyJhbGciOiJSUzI1..."
        },
        "support_registry": false,
        "use_hnc": false
    }
]
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

#### SPACE_KUBERNETES_VERSION_LEVEL

- **Type**: String
- **Optional**: Yes
- **Default**: `1.30`
- **Description**: Target Kubernetes API level used by the manifest transcribers.
  `1.32`+ emits native image-volume sources instead of init-container + emptyDir.
  `1.36`+ adds `hostUsers: false` to pod specs.

```bash
SPACE_KUBERNETES_VERSION_LEVEL=1.30
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
SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_FILE=/opt/space/config/ingress-annotations.json
```

### Kubernetes Ingress Provider Mapping

Define ingress provider type based on ingress class name pattern matching.

The resolved type only decides **which annotation declares an `https-backend: true` ingress to its
controller**. It never decides on which entrypoint or port an ingress is published:

| Type                                   | Annotation added on the Ingress when `https-backend: true` |
|----------------------------------------|------------------------------------------------------------|
| `nginx` (and any unmatched class)      | `nginx.ingress.kubernetes.io/backend-protocol: HTTPS`      |
| `traefik`, `traefik1`                  | `ingress.kubernetes.io/protocol: https`                    |
| `traefik2`, `traefik3` (Traefik v2/v3) | _none_                                                     |
| `haproxy`                              | `haproxy.org/server-ssl: true`                             |
| `aws`                                  | `alb.ingress.kubernetes.io/backend-protocol: HTTPS`        |
| `gce`                                  | `cloud.google.com/app-protocols: HTTPS`                    |

Traefik v2/v3 exposes no Ingress annotation for the backend scheme — it reads it from the Service — so
`traefik2` writes nothing, and `https-backend` has no effect for that type. `traefik3` is accepted as an
alias of `traefik2`: both versions share the same `traefik.ingress.kubernetes.io/*` annotations, and
without the alias a cluster declared `traefik3` would silently fall back to the `nginx` annotations.
`traefik.ingress.kubernetes.io/router.entrypoints` must **never** be used as a substitute: it pins the
router to a single entrypoint, so `web` leaves the host with no HTTPS router at all (`404` on 443) and
`websecure` leaves it with no HTTP one. Without that annotation the router is published on every
entrypoint, which is the behaviour the nginx controller had.

Use **one** of these options:

#### SPACE_INGRESS_PROVIDER_JSON

- **Type**: JSON string
- **Optional**: Yes
- **Description**: Maps ingress class name patterns (regex) to provider types
- **Format**: `{"pattern": "type", ...}`
    - `pattern`: Regular expression to match against ingress class name
    - `type`: Provider type - one of: `nginx`, `traefik`, `traefik1`, `traefik2`, `traefik3`, `haproxy`, `aws`, `gce`
- **Default**: `nginx` (used when no match found or invalid type)

```bash
SPACE_INGRESS_PROVIDER_JSON='{".*nginx.*":"nginx",".*traefik.*":"traefik2",".*haproxy.*":"haproxy"}'
```

#### SPACE_INGRESS_PROVIDER_FILE

- **Type**: String (file path)
- **Optional**: Yes
- **Description**: JSON file returning provider mapping object

```bash
SPACE_INGRESS_PROVIDER_FILE=/opt/space/config/ingress-providers.json
```

**File format** (`/opt/space/config/ingress-providers.json`):

```json
{
    ".*nginx.*": "nginx",
    ".*traefik.*": "traefik2",
    ".*haproxy.*": "haproxy",
    "public": "nginx",
    "internal": "traefik"
}
```

## Docker Compose Configuration

These variables tune the **docker-compose** deployment target — used only when a project's cluster has
`type: docker-compose`. Deployments are applied to a remote Docker host over SSH with Ansible, exposing services
through **Traefik v3**. Every variable is **optional**; each maps to a `teknoo.east.paas.docker-compose.*` DI
parameter and, when unset, falls back to the library default shown below.

The deployment target is always the per-cluster `type` — there is intentionally no global deployment-target
variable.

### Ansible & Runtime Settings

#### SPACE_DC_ANSIBLE_BINARY

- **Type**: String
- **Optional**: Yes
- **Default**: `ansible-playbook`
- **Maps to**: `teknoo.east.paas.docker-compose.ansible.binary`
- **Description**: `ansible-playbook` executable used to apply the generated playbooks

```bash
SPACE_DC_ANSIBLE_BINARY=ansible-playbook
```

#### SPACE_DC_TIMEOUT

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Default**: `900`
- **Maps to**: `teknoo.east.paas.docker-compose.timeout`
- **Description**: Timeout for a single playbook run

```bash
SPACE_DC_TIMEOUT=900
```

#### SPACE_DC_DEPLOY_ROOT

- **Type**: String (path)
- **Optional**: Yes
- **Default**: `/opt/paas`
- **Maps to**: `teknoo.east.paas.docker-compose.deploy_root`
- **Description**: Root directory on the target host where compose projects are written

```bash
SPACE_DC_DEPLOY_ROOT=/opt/paas
```

#### SPACE_DC_NETWORK_DRIVER

- **Type**: String
- **Optional**: Yes
- **Default**: `bridge`
- **Maps to**: `teknoo.east.paas.docker-compose.network.driver`
- **Description**: Docker network driver for generated project networks

```bash
SPACE_DC_NETWORK_DRIVER=bridge
```

#### SPACE_DC_NETWORK_INTERNAL

- **Type**: Boolean
- **Optional**: Yes
- **Default**: `false`
- **Maps to**: `teknoo.east.paas.docker-compose.network.internal`
- **Description**: Declare each project network (`<project>-private`) as `internal: true`: the containers have no
  egress and are only reachable through Traefik. Off by default (like Kubernetes pods, the containers keep an
  egress). When enabled, the host ports published by public services are **not** reachable.

```bash
SPACE_DC_NETWORK_INTERNAL=false
```

#### SPACE_DC_HTTPS_BACKEND_INSECURE_SKIP_VERIFY

- **Type**: Boolean
- **Optional**: Yes
- **Default**: `false`
- **Maps to**: `teknoo.east.paas.docker-compose.https_backend.insecure_skip_verify`
- **Description**: Skip TLS certificate verification for HTTPS backends behind Traefik

```bash
SPACE_DC_HTTPS_BACKEND_INSECURE_SKIP_VERIFY=false
```

### Traefik Settings

#### SPACE_DC_TRAEFIK_CONTAINER

- **Type**: String
- **Optional**: Yes
- **Default**: `traefik`
- **Maps to**: `teknoo.east.paas.docker-compose.traefik.container`
- **Description**: Name of the Traefik container on the target host

```bash
SPACE_DC_TRAEFIK_CONTAINER=traefik
```

#### SPACE_DC_TRAEFIK_DYNAMIC_DIR

- **Type**: String (path)
- **Optional**: Yes
- **Default**: `/etc/traefik/dynamic`
- **Maps to**: `teknoo.east.paas.docker-compose.traefik.dynamic_dir`
- **Description**: Directory Traefik watches for dynamic file-provider configuration

```bash
SPACE_DC_TRAEFIK_DYNAMIC_DIR=/etc/traefik/dynamic
```

#### SPACE_DC_TRAEFIK_CERTS_DIR

- **Type**: String (path)
- **Optional**: Yes
- **Default**: `/etc/traefik/certs`
- **Maps to**: `teknoo.east.paas.docker-compose.traefik.certs_dir`
- **Description**: Directory on the Docker host where the TLS certificates of the ingresses are pushed (the Traefik
  container bind-mounts it, see `SPACE_DC_TRAEFIK_CERTS_MOUNT_DIR`)

```bash
SPACE_DC_TRAEFIK_CERTS_DIR=/etc/traefik/certs
```

#### SPACE_DC_TRAEFIK_CERTS_MOUNT_DIR

- **Type**: String (path)
- **Optional**: Yes
- **Default**: the value of `SPACE_DC_TRAEFIK_CERTS_DIR`
- **Maps to**: `teknoo.east.paas.docker-compose.traefik.certs_mount_dir`
- **Description**: The certificates directory as seen by the Traefik process, i.e. the path where the host
  directory above is bind-mounted in the Traefik container. The generated dynamic files reference the certificates
  under this path. Only set it when the mount target differs from the host path.

```bash
SPACE_DC_TRAEFIK_CERTS_MOUNT_DIR=/etc/traefik/certs
```

#### SPACE_DC_TRAEFIK_CERTRESOLVER

- **Type**: String
- **Optional**: Yes
- **Default**: _(none)_
- **Maps to**: `teknoo.east.paas.docker-compose.traefik.default_certresolver`
- **Description**: Default Traefik cert resolver (e.g. an ACME resolver). The DI parameter is **only declared
  when this variable is set to a non-empty value**; otherwise the driver's own default applies.

```bash
SPACE_DC_TRAEFIK_CERTRESOLVER=letsencrypt
```

#### SPACE_DC_TRAEFIK_ENTRYPOINT_WEB

- **Type**: String
- **Optional**: Yes
- **Default**: `web`
- **Maps to**: `teknoo.east.paas.docker-compose.traefik.entrypoint.web`
- **Description**: Traefik entrypoint name for plain HTTP

```bash
SPACE_DC_TRAEFIK_ENTRYPOINT_WEB=web
```

#### SPACE_DC_TRAEFIK_ENTRYPOINT_WEBSECURE

- **Type**: String
- **Optional**: Yes
- **Default**: `websecure`
- **Maps to**: `teknoo.east.paas.docker-compose.traefik.entrypoint.websecure`
- **Description**: Traefik entrypoint name for HTTPS

```bash
SPACE_DC_TRAEFIK_ENTRYPOINT_WEBSECURE=websecure
```

> Public TCP/UDP services of the deployed projects are published as host ports by their Compose stack (`ports:`),
> not routed by Traefik: there is no TCP/UDP entrypoint to configure (the former `SPACE_DC_TRAEFIK_ENTRYPOINT_TCP`
> and `SPACE_DC_TRAEFIK_ENTRYPOINT_UDP` variables are ignored). A public service on a replicated pod cannot publish
> host ports; the deployment succeeds with a warning in the job history, expose it through an ingress instead.

### Per-Account Registry Settings (docker-compose)

When a docker-compose cluster has `support_registry: true` (the default), Space provisions a **per-account
private OCI registry** as a dedicated `<namespace>-registry` container on the same Docker host, over Ansible.
The container is attached to an internal Docker network (`SPACE_DC_REGISTRY_NETWORK`, no host port) and is **exposed by
the host's Traefik** on the `websecure` entrypoint under the host name
`<namespace>-registry.<docker host>` (the host of the cluster `master` address): this name is the account
`registryUrl`, the worker pushes the built images to it and the Docker host pulls them from it at
`docker compose up` (a container name would be resolvable by neither). The registry playbook also connects Traefik
to the registry network, drops the Traefik dynamic file `<namespace>-registry.yml` in `SPACE_DC_TRAEFIK_DYNAMIC_DIR`
and logs the deploy user in on the registry (`~/.docker/config.json`) so the pull is authenticated (htpasswd).

Prerequisites on the Docker host: a DNS record for `<namespace>-registry.<docker host>` (a wildcard `*.<docker host>`
covers every account) pointing to the host, and a valid certificate for it on Traefik — either an ACME resolver
(`SPACE_DC_TRAEFIK_CERTRESOLVER`, the certificate is issued on the first request) or a certificate declared in
Traefik for that name; the worker (`buildah login`/`push`) and the Docker daemon verify it. TLS between Traefik
and the registry container is optional (`SPACE_DC_REGISTRY_TLS`). This is the docker-compose equivalent of the
Kubernetes-hosted per-account registry (behind an Ingress) — docker-compose clusters do **not** require the
Kubernetes-only OCI registry settings below.

#### SPACE_DC_REGISTRY_IMAGE

- **Type**: String
- **Optional**: Yes
- **Default**: `registry:2`
- **Maps to**: `teknoo.east.paas.docker-compose.registry.image`
- **Description**: Docker image used for the per-account registry container

```bash
SPACE_DC_REGISTRY_IMAGE=registry:2
```

#### SPACE_DC_REGISTRY_NETWORK

- **Type**: String
- **Optional**: Yes
- **Default**: `space-registry`
- **Maps to**: `teknoo.east.paas.docker-compose.registry.network`
- **Description**: Name of the external, internal-only Docker network the registry is attached to (Traefik is
  connected to it to reach the registry)

```bash
SPACE_DC_REGISTRY_NETWORK=space-registry
```

#### SPACE_DC_REGISTRY_PORT

- **Type**: Integer
- **Optional**: Yes
- **Default**: `5000`
- **Maps to**: `teknoo.east.paas.docker-compose.registry.port`
- **Description**: Port the registry container exposes on the internal network

```bash
SPACE_DC_REGISTRY_PORT=5000
```

#### SPACE_DC_REGISTRY_TLS

- **Type**: Boolean
- **Optional**: Yes
- **Default**: `false`
- **Maps to**: `teknoo.east.paas.docker-compose.registry.tls`
- **Description**: Enable TLS on the per-account registry container itself (between Traefik and the registry; the
  public side is always HTTPS through Traefik)

```bash
SPACE_DC_REGISTRY_TLS=false
```

## OCI Registry Configuration

The settings in this section configure the **Kubernetes-hosted** per-account registry and the shared global
registry. For docker-compose clusters, the per-account registry is provisioned over Ansible instead — see
[Per-Account Registry Settings (docker-compose)](#per-account-registry-settings-docker-compose).

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

Used for encrypting stored secrets in database. The web server encrypts them when they are saved (public key
only, `SPACE_PERSISTED_VAR_AGENT_MODE=0`); the `new_task` worker is the only process decrypting them, when a
new job is prepared (public and private keys, `SPACE_PERSISTED_VAR_AGENT_MODE=1`). The `execute_job`,
`history_sent` and `job_done` workers receive the variables already decrypted inside the encrypted job message
and need none of these keys.

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
SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE=/opt/space/config/plans.json
```

**File format** (`/opt/space/config/plans.json`):

```json
[
    {
        "id": "free",
        "name": "Free Plan",
        "envsCountAllowed": 1,
        "quotas": [
            {
                "category": "compute",
                "type": "cpu",
                "capacity": "1000m",
                "require": "100m"
            },
            {
                "category": "memory",
                "type": "memory",
                "capacity": "2Gi",
                "require": "256Mi"
            }
        ]
    },
    {
        "id": "pro",
        "name": "Professional Plan",
        "envsCountAllowed": 5,
        "quotas": [
            {
                "category": "compute",
                "type": "cpu",
                "capacity": "10000m",
                "require": "500m"
            },
            {
                "category": "memory",
                "type": "memory",
                "capacity": "20Gi",
                "require": "2Gi"
            }
        ],
        "clusters": [
            "production",
            "staging"
        ]
    }
]
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
SPACE_HOOKS_COLLECTION_FILE=/opt/space/config/hooks.json
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
- **Description**: JSON file returning variables object

```bash
SPACE_PAAS_GLOBAL_VARIABLES_FILE=/opt/space/config/global-vars.json
```

### Extends Libraries

For pods, containers, services, and ingresses:

#### SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_JSON / _FILE

#### SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_JSON / _FILE

#### SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_JSON / _FILE

#### SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_JSON / _FILE

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

#### MERCURE_JWT_ISSUER

- **Type**: String (URL)
- **Optional**: Yes
- **Default**: `https://localhost`
- **Description**: `iss` claim of the tokens minted for the hub. Only read by a Mercure **1.0** hub,
  where RFC 9068 access tokens require it and where it must match the issuer the hub trusts
  (its `issuer` directive, `MERCURE_TRUSTED_ISSUERS` in the official image). A 0.x hub ignores it.

```bash
MERCURE_JWT_ISSUER=https://space.example.com
```

#### MERCURE_PROTOCOL_VERSION

- **Type**: String, `0.x` or `1.0`
- **Optional**: Yes
- **Default**: `0.x`, which an empty or unrecognized value also falls back to
- **Description**: The Mercure protocol spoken by the hub. It must match the hub actually deployed,
  and the two Docker Compose topologies do not run the same one:

| Stack | Hub | `MERCURE_PROTOCOL_VERSION` |
|---|---|---|
| `compose.yml` (default), `compose.frankenphp.yml` | Caddy module embedded in `dunglas/frankenphp`, still Mercure 0.24 | unset, i.e. `0.x` |
| `compose.fpm.yml`, and its legacy httpd variant `compose.legacy.override.yml` | dedicated `dunglas/mercure:v1` container | `1.0`, set on every PHP service |

The FrankenPHP stacks will move to `1.0` as soon as FrankenPHP ships a Mercure 1.0 module (it embeds
`github.com/dunglas/mercure v0.24.2` today). The hub tag stays overridable with
`MERCURE_IMAGE_TAG=v0.24` to roll the FPM stack back, which then also means setting
`MERCURE_PROTOCOL_VERSION=0.x` on its PHP services.

```bash
MERCURE_PROTOCOL_VERSION=1.0
```

**This variable is read while the Symfony container is compiled**, in `appliance/config/di.variables.php`,
and not at runtime like every other one: MercureBundle turns the value into a `ProtocolVersion` enum
case during compilation, so an `%env()%` placeholder, which only gets its value at runtime, can not be
used. Two consequences: a `./space.sh warmup` is required after changing it (it is already part of the
install flow), and it must carry the same value for every PHP process of a stack — all the more so with
Docker Compose, where `var/cache` is shared through the mounted volume, including when switching from
one stack to the other.

On the FPM and legacy stacks the workers publish through the internal
`http://mercure:8181/.well-known/mercure` while the browser subscribes through the httpd proxy at
`https://localhost/hub/.well-known/mercure`. A 1.0 hub derives the audience it expects from each
request, so the two URLs differing would make every publication fail with a `401`; the hub therefore
pins `resource_identifier` to the public URL, which is also the `aud` claim of the generated tokens.
`MERCURE_TRUSTED_ISSUERS` on the hub and `MERCURE_JWT_ISSUER` on the PHP services must likewise be
the same value.

Switching the parameter to `1.0` changes three things at once, and all of them are handled by the
code: the subscription query parameter becomes `match` instead of `topic`, the generated JWT becomes
an RFC 9068 access token carrying `authorization_details` (hence `MERCURE_JWT_ISSUER`), and the
subscriber cookie is renamed `__Secure-mercure_access_token`, which requires the hub public URL to be
served over HTTPS. Switch it only once **every** process talks to a 1.0 hub, and update the hub
configuration accordingly (`issuer` block instead of `publisher_jwt`/`subscriber_jwt`).

### Job Notification

#### SPACE_NEW_TASK_WAITING_TIME

- **Type**: Integer (seconds)
- **Optional**: Yes
- **Description**: Wait time before redirecting to job page

```bash
SPACE_NEW_TASK_WAITING_TIME=3
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
MESSENGER_NEW_TASK_DSN=amqp://space:RabbitPass456@rabbitmq:5672/%2f/new_task
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
SPACE_CLUSTER_CATALOG_FILE=/opt/space/config/clusters.json
SPACE_KUBERNETES_CLIENT_TIMEOUT=5
SPACE_KUBERNETES_CLIENT_VERIFY_SSL=1
SPACE_KUBERNETES_VERSION_LEVEL=1.30
SPACE_KUBERNETES_ROOT_NAMESPACE=space-client-
SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE=space-registry-
SPACE_STORAGE_CLASS=fast-ssd
SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS=nginx
SPACE_CLUSTER_ISSUER=letsencrypt-prod
SPACE_INGRESS_PROVIDER_JSON='{".*nginx.*":"nginx",".*traefik.*":"traefik2"}'
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
SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE=/opt/space/config/plans.json
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
MERCURE_JWT_ISSUER=https://space.example.com
MERCURE_PROTOCOL_VERSION=1.0
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
