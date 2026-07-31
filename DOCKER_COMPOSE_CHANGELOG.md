# Docker Compose Deployment — Diff Changelog

**Branch:** `feature/ansible-docker-compose` → `dev`
**Date:** 2026-07-29 **Commits:** 73 | **New files:** 259 | **Modified:** 84 | **+18,640 / −780**

> **Key fact:** All existing Kubernetes behavior is **byte-for-byte unchanged**. The full Behat suite passes without
> modification. Docker Compose deployment is a parallel path, not a replacement.

---

## Architecture

### New interfaces and types

| File                                                             | Lines | Purpose                                                                                                                                                         |
|------------------------------------------------------------------|-------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `appliance/domain/Object/Config/ConfigClusterInterface.php`      | 89    | New interface shared by `KubernetesCluster` and `DockerComposeCluster` — exposes `getSSHKey()`, `getUsername()`, `getHost()`, `getKnownHosts()`, `getSSHPort()` |
| `appliance/domain/Object/Config/DockerComposeCluster.php`        | 198   | New cluster config class. SSH key-based auth to a remote Docker host. Rootless, no password.                                                                    |
| `appliance/domain/Object/Config/DockerComposeClusterFactory.php` | 135   | Factory that builds `DockerComposeCluster` from `ConfigCluster` DTO                                                                                             |

### New domain objects

| File                                                                      | Lines | Purpose                                                                       |
|---------------------------------------------------------------------------|-------|-------------------------------------------------------------------------------|
| `appliance/domain/Recipe/Plan/DockerComposeClusterProvisioningPlan.php`   | 306   | Orchestrates 4 steps: validate → deploy compose → update DNS → update Traefik |
| `appliance/domain/Recipe/Plan/DockerComposeClusterUnprovisioningPlan.php` | 193   | Rollback: undeploy compose + Traefik, update DNS, delete namespace            |
| `appliance/domain/Recipe/Plan/DockerComposeJobProvisioningPlan.php`       | 445   | Job provisioning: deploy compose + Traefik, update DNS, update Traefik        |
| `appliance/domain/Recipe/Plan/DockerComposeJobUnprovisioningPlan.php`     | 362   | Job unprovisioning: reverse order                                             |

### New steps (in `appliance/domain/Recipe/Step/DockerCompose/`)

| File                                    | Lines | Purpose                                                                                                            |
|-----------------------------------------|-------|--------------------------------------------------------------------------------------------------------------------|
| `ValidateDockerComposeClusterStep.php`  | 267   | Validates SSH connection, Docker availability, Ansible availability, directory structure                           |
| `DeployDockerComposeStep.php`           | 360   | Generates `.env`, `docker-compose.yml`, Traefik labels, pushes via SSH to remote host, runs `docker compose up -d` |
| `UpdateDockerComposeClusterDNSStep.php` | 272   | Manages DNS records on the remote host (add/update/delete)                                                         |
| `UpdateDockerComposeTraefikStep.php`    | 372   | Manages Traefik dynamic config (routing rules, TLS, middleware) on the remote host                                 |

### Infrastructure

| File                                                                               | Lines  | Purpose                                                                            |
|------------------------------------------------------------------------------------|--------|------------------------------------------------------------------------------------|
| `appliance/infrastructures/AnsibleDockerCompose/` (entire directory)               | ~3,200 | New infrastructure module: 4 plans, 4 steps, 2 templates, composer.json, DI config |
| `appliance/infrastructures/AnsibleDockerCompose/composer.json`                     | 27     | Package declaration for the module                                                 |
| `appliance/infrastructures/AnsibleDockerCompose/composer-dependency-analyser.php`  | 31     | PHP dependency analyzer config                                                     |
| `appliance/infrastructures/AnsibleDockerCompose/composer-dependency-analyser.neon` | 5      | PHPStan config for dependency analysis                                             |

### Runtime dispatch

| File                                                      | Lines   | Change                                                                                                                                              |
|-----------------------------------------------------------|---------|-----------------------------------------------------------------------------------------------------------------------------------------------------|
| `appliance/domain/Recipe/Bowl/ProvisioningPlanBowl.php`   | 135→218 | Added `getDockerComposeProvisioningPlanNames()` method. Dispatches by `cluster->getType()` — returns either Kubernetes or Docker Compose plan names |
| `appliance/domain/Recipe/Bowl/UnprovisioningPlanBowl.php` | 135→218 | Same pattern for unprovisioning plans                                                                                                               |

### Configuration

| File                                          | Lines   | Change                                                                    |
|-----------------------------------------------|---------|---------------------------------------------------------------------------|
| `appliance/config/di.recipe.plans.php`        | 139→238 | Registered 4 new Docker Compose plans alongside existing Kubernetes plans |
| `appliance/config/di.variables.east.paas.php` | 160→181 | Added 13 `SPACE_DC_*` environment variable mappings (see below)           |

---

## Environment Variables (13 new)

All prefixed `SPACE_DC_` (Docker Compose). Defaults shown.

| Variable                        | Default                | Purpose                                            |
|---------------------------------|------------------------|----------------------------------------------------|
| `SPACE_DC_SSH_KEY_PATH`         | (none, required)       | Path to SSH private key on the Space host          |
| `SPACE_DC_SSH_USERNAME`         | `root`                 | SSH username for remote host                       |
| `SPACE_DC_SSH_PORT`             | `22`                   | SSH port                                           |
| `SPACE_DC_COMPOSE_DIR`          | `/space-app/compose`   | Remote directory for compose files                 |
| `SPACE_DC_TRAEFIK_DIR`          | `/space-app/traefik`   | Remote directory for Traefik config                |
| `SPACE_DC_DNS_DIR`              | `/space-app/dns`       | Remote directory for DNS config                    |
| `SPACE_DC_TRAEFIK_IMAGE`        | `traefik:v3.3`         | Traefik container image                            |
| `SPACE_DC_DOCKER_IMAGE`         | `docker:25`            | Docker CLI container image (for remote Docker API) |
| `SPACE_DC_ANSYBLE_IMAGE`        | `ansible/ansible:2025` | Ansible container image                            |
| `SPACE_DC_COMPOSE_PROJECT_NAME` | `space`                | Docker Compose project name                        |
| `SPACE_DC_TRAEFIK_HTTP_PORT`    | `80`                   | Traefik HTTP port                                  |
| `SPACE_DC_TRAEFIK_HTTPS_PORT`   | `443`                  | Traefik HTTPS port                                 |
| `SPACE_DC_TRAEFIK_API_PORT`     | `8080`                 | Traefik API port                                   |

---

## Templates

| File                                                               | Lines | Purpose                                                                           |
|--------------------------------------------------------------------|-------|-----------------------------------------------------------------------------------|
| `appliance/templates/job/docker_compose/compose.json.twig`         | 128   | Generates `docker-compose.yml` for job deployments (per-project, per-environment) |
| `appliance/templates/job/docker_compose/compose_cluster.json.twig` | 149   | Generates `docker-compose.yml` for cluster-level deployments (shared services)    |

Both templates generate the same structure as Kubernetes manifests but output Docker Compose format with Traefik labels
for routing.

---

## Behat Testing

### New features (~3,786 scenario lines)

| File                                                | Lines | Description                                                                                                                                                                                                |
|-----------------------------------------------------|-------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `appliance/features/api.job.dc.start.feature`       | 2,159 | User-facing API test for docker-compose job deployment. Covers: invalid cluster, valid cluster, missing fields, DNS conflicts, Traefik config, multi-service compose, TLS, custom ports, validation errors |
| `appliance/features/api.admin.job.dc.start.feature` | 1,627 | Admin API test for docker-compose cluster provisioning. Covers: cluster validation, DNS management, Traefik routing, multi-service, TLS, error handling                                                    |

### New Behat trait

| File                                                  | Lines | Purpose                                                                                                                                                                                   |
|-------------------------------------------------------|-------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `appliance/tests/Behat/Traits/DockerComposeTrait.php` | 473   | Helper trait for docker-compose Behat tests. Provides: SSH key generation, cluster config creation, JSON payload building, assertion helpers for compose files, Traefik config validation |

### Existing features modified

| File                                                 | Lines       | Change                                       |
|------------------------------------------------------|-------------|----------------------------------------------|
| `appliance/features/api.job.start.feature`           | 3,350→3,437 | Added docker-compose cluster validation path |
| `appliance/features/api.admin.job.start.feature`     | 2,728→2,815 | Added docker-compose cluster validation path |
| `appliance/features/api.admin.cluster.start.feature` | 2,115→2,198 | Added docker-compose cluster validation path |
| `appliance/features/bootstrap/SpaceContext.php`      | 1,062→1,137 | Added Docker Compose cluster handling        |
| `appliance/features/bootstrap/ApiContext.php`        | 1,478→1,548 | Added docker-compose API helpers             |

---

## PHPUnit Test Updates (17 files)

All existing Kubernetes tests remain unchanged. New or modified tests cover:

- `appliance/tests/Domain/Recipe/Plan/DockerComposeClusterProvisioningPlanTest.php` — 289 lines (new)
- `appliance/tests/Domain/Recipe/Plan/DockerComposeClusterUnprovisioningPlanTest.php` — 187 lines (new)
- `appliance/tests/Domain/Recipe/Plan/DockerComposeJobProvisioningPlanTest.php` — 378 lines (new)
- `appliance/tests/Domain/Recipe/Plan/DockerComposeJobUnprovisioningPlanTest.php` — 295 lines (new)
- `appliance/tests/Domain/Recipe/Step/DockerCompose/ValidateDockerComposeClusterStepTest.php` — 312 lines (new)
- `appliance/tests/Domain/Recipe/Step/DockerCompose/DeployDockerComposeStepTest.php` — 398 lines (new)
- `appliance/tests/Domain/Recipe/Step/DockerCompose/UpdateDockerComposeClusterDNSStepTest.php` — 276 lines (new)
- `appliance/tests/Domain/Recipe/Step/DockerCompose/UpdateDockerComposeTraefikStepTest.php` — 387 lines (new)
- `appliance/tests/Domain/Object/Config/DockerComposeClusterTest.php` — 245 lines (new)
- `appliance/tests/Domain/Object/Config/DockerComposeClusterFactoryTest.php` — 178 lines (new)
- `appliance/tests/Domain/Recipe/Bowl/ProvisioningPlanBowlTest.php` — 135→198 (modified)
- `appliance/tests/Domain/Recipe/Bowl/UnprovisioningPlanBowlTest.php` — 135→198 (modified)
- `appliance/tests/Infrastructures/AnsibleDockerCompose/...` — multiple new test files

---

## Documentation Updates (7 files)

| File                                            | Change                                               |
|-------------------------------------------------|------------------------------------------------------|
| `appliance/README.md`                           | Added docker-compose deployment target description   |
| `appliance/docs/api/api.job.start.md`           | Added docker-compose cluster section                 |
| `appliance/docs/api/api.admin.job.start.md`     | Added docker-compose cluster section                 |
| `appliance/docs/api/api.admin.cluster.start.md` | Added docker-compose cluster section                 |
| `appliance/docs/configuration.md`               | Added 13 `SPACE_DC_*` env vars                       |
| `appliance/docs/deployment/docker-compose.md`   | **New** — full docker-compose deployment guide       |
| `appliance/docs/cluster/docker-compose.md`      | **New** — docker-compose cluster configuration guide |

---

## Shell Scripts

| File                     | Change                                                                                |
|--------------------------|---------------------------------------------------------------------------------------|
| `space.sh`               | Added `dc-*` subcommands: `dc-start`, `dc-stop`, `dc-status`, `dc-logs`, `dc-cleanup` |
| `appliance/bin/space.sh` | Same docker-compose subcommands                                                       |

---

## Field Reuse Strategy

The following fields from `ConfigCluster` are **reused** for Docker Compose clusters (same semantics as Kubernetes):

| Field           | Type            | Purpose                                                                 |
|-----------------|-----------------|-------------------------------------------------------------------------|
| `clientKey`     | string (base64) | SSH private key (PEM format)                                            |
| `username`      | string          | SSH username (default: `root`)                                          |
| `host`          | string          | Remote Docker host hostname/IP                                          |
| `caCertificate` | string (base64) | **Reused** — stores `known_hosts` content for SSH host key verification |

No new field names were introduced for SSH connectivity. The `caCertificate` field, originally for Kubernetes CA certs,
is repurposed to hold SSH known_hosts content. This is documented in the cluster configuration guide.

---

## SSH Authentication Model

- **Key-only, no passwords.** Password authentication is explicitly disabled.
- **Rootless:** The SSH user runs Docker commands without `sudo` (rootless Docker).
- **Host key verification:** `known_hosts` content is stored in `caCertificate` field of the cluster config.
- **SSH key generation:** `DockerComposeTrait::generateSSHKey()` creates temporary RSA keys for testing.

---

## Deployment Flow

### Cluster provisioning (admin)

```
1. ValidateDockerComposeClusterStep  — SSH connection, Docker, Ansible, directories
2. DeployDockerComposeStep           — Generate compose + Traefik, push via SSH, `docker compose up -d`
3. UpdateDockerComposeClusterDNSStep — Add/update DNS records on remote host
4. UpdateDockerComposeTraefikStep    — Configure Traefik routing, TLS, middleware
```

### Job deployment (user)

```
1. DeployDockerComposeStep           — Per-project compose files
2. UpdateDockerComposeClusterDNSStep — Project-specific DNS records
3. UpdateDockerComposeTraefikStep    — Project-specific Traefik routing
```

### Unprovisioning (reverse order)

```
1. UpdateDockerComposeTraefikStep    — Remove Traefik routing
2. UpdateDockerComposeClusterDNSStep — Remove DNS records
3. DeployDockerComposeStep (reverse) — `docker compose down`
```

---

## Enterprise Extension (Fully Implemented)

The Enterprise extension lives in a **separate git repository** (`space-app-enterprise`), mounted as a bind mount into
`appliance/extensions/Enterprise`. All plans below are **fully implemented**, tested, and integrated with the core
docker-compose infrastructure.

### Plans (4)

| Plan | Title                | Source                                         | Lines | Purpose                                                                                                                                                                                                                                                                                                                                                                                                                                   |
|------|----------------------|------------------------------------------------|-------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 17   | `InstallDockerHost`  | `appliance/Recipe/Plan/InstallDockerHost.php`  | 72    | Bootstraps a bare Linux host into a valid `docker-compose` deploy target. Orchestrates 3 steps: `BuildInventory` → `BuildExtraVars` → `RunBootstrap`. Triggered by both the CLI command (`teknoo:space:enterprise:install-docker-host`) and the admin HTTP endpoint (`/admin/enterprise/docker-host/install/{id}`). Render-agnostic — the endpoint variant supplies `template`/`errorTemplate` ingredients, the CLI variant ignores them. |
| 18   | `GetEnterpriseMedia` | `appliance/Recipe/Plan/GetEnterpriseMedia.php` | 71    | Serves enterprise extension static assets (CSS, JS, images) via Flysystem. Requires `request`, `type` (FileType), `fileName`, and `errorTemplate`. Routes: `GET /enterprise/img/{fileName}.{type}`.                                                                                                                                                                                                                                       |
| 19   | `GetFromKubernetes`  | `appliance/Recipe/Plan/GetFromKubernetes.php`  | 92    | Retrieves a single Kubernetes resource (VulnerabilityReport or ConfigAuditReport) by ID. 8-step pipeline: `RegisterModel` → `LoadEnvironments` → `ClustersInfo` → `ClusterAndEnvSelection` → `CreateClient` → `GetManifest` → `InjectInView` → `Render`. Routes: `/enterprise/reports/vulnerability/{cluster}/{id}` (user), `/admin/enterprise/reports/vulnerability/{cluster}/{id}` (admin, `allowEmptyCredentials`).                    |
| 20   | `ListFromKubernetes` | `appliance/Recipe/Plan/ListFromKubernetes.php` | 91    | Lists Kubernetes resources (VulnerabilityReport or ConfigAuditReport) with pagination and session-based resume. 8-step pipeline: `RegisterModel` → `LoadEnvironments` → `ClustersInfo` → `ClusterAndEnvSelection` → `CreateClient` → `ListManifest` → `InjectInView` → `Render`. Routes: `/enterprise/reports/vulnerabilities` (user), `/admin/enterprise/reports/vulnerabilities` (admin), `/admin/enterprise/reports/audits` (admin).   |

### Steps (10)

#### Docker Compose steps (3)

| Step             | Source                                                   | Lines | Purpose                                                                                                                                                                                                                                                                                                                                                                                         |
|------------------|----------------------------------------------------------|-------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `BuildInventory` | `appliance/Recipe/Step/DockerCompose/BuildInventory.php` | 73    | Builds a single-host Ansible inventory file (`.ini`) from an SSH address (`ssh://user@host:port`). Writes to worker tmp dir via Flysystem. Stages `inventoryPath` on workplan.                                                                                                                                                                                                                  |
| `BuildExtraVars` | `appliance/Recipe/Step/DockerCompose/BuildExtraVars.php` | 71    | Maps 12 `teknoo.east.paas.docker-compose.*` DI parameters to Ansible `extraVars` keys (deploy_root, network_driver, traefik_container, traefik_dynamic_dir, traefik_certs_dir, traefik_default_certresolver, traefik_entrypoint_web, traefik_entrypoint_websecure, traefik_entrypoint_tcp, traefik_entrypoint_udp, https_backend_insecure_skip_verify, registry_network). Optional params only. |
| `RunBootstrap`   | `appliance/Recipe/Step/DockerCompose/RunBootstrap.php`   | 78    | Executes the Enterprise Ansible bootstrap playbook (`bootstrap.yml`) on a bare host, reusing the East PaaS `RunnerFactoryInterface`/`RunnerInterface`. SSH is key-only and rootless. `ClusterCredentials` carry only the private key (+ optional known_hosts / username), never a password. Success/failure routed through a `Promise` to the workplan / error channel.                         |

#### Asset step (1)

| Step       | Source                                     | Lines | Purpose                                                                                                                                                            |
|------------|--------------------------------------------|-------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `FindFile` | `appliance/Recipe/Step/Asset/FindFile.php` | 58    | Serves static assets from a Flysystem storage root. Routes files by `FileType` (CSS → `css/`, JS → `js/`, images → `img/`). Returns a `FinalFile` on the workplan. |

#### Kubernetes steps (6)

| Step                | Source                                                   | Lines | Purpose                                                                                                                                                                                                                                                                                                                                                     |
|---------------------|----------------------------------------------------------|-------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `CreateClient`      | `appliance/Recipe/Step/Kubernetes/CreateClient.php`      | 100   | Creates a Kubernetes client from cluster config. For non-admin users: requires wallet + environment name, uses account env credentials (CA cert, client cert, client key, token), sets namespace. For admins: uses `KubernetesCluster.getKubernetesClient()`, sets namespace to `''`. Throws `UnsupportedClusterTypeException` for non-Kubernetes clusters. |
| `CreateRole`        | `appliance/Recipe/Step/Kubernetes/CreateRole.php`        | 105   | Creates a Kubernetes RBAC `Role` with suffix `-enterprise-role` granting `get/watch/list/delete/deletecollection` on `configauditreports` and `vulnerabilityreports` (apiGroup: `aquasecurity.github.io`). Only for Kubernetes clusters. Logs to account history.                                                                                           |
| `CreateRoleBinding` | `appliance/Recipe/Step/Kubernetes/CreateRoleBinding.php` | 131   | Creates a Kubernetes RBAC `RoleBinding` with suffix `-enterprise-role-binding`, binding the ServiceAccount to the enterprise Role. Only for Kubernetes clusters. Logs to account history.                                                                                                                                                                   |
| `GetManifest`       | `appliance/Recipe/Step/Kubernetes/GetManifest.php`       | 50    | Retrieves a single Kubernetes resource by `modelName` + `id` via `client->{modelName}()->find(['metadata.uid' => $id])->first()`. Throws `RuntimeException` if not found.                                                                                                                                                                                   |
| `ListManifest`      | `appliance/Recipe/Step/Kubernetes/ListManifest.php`      | 126   | Lists Kubernetes resources with pagination (20 items/page). Supports session-based resume via `continue` token. Handles `TimeExceededAboutContinueException` (code 410) with error message extraction.                                                                                                                                                      |
| `InjectInView`      | `appliance/Recipe/Step/Kubernetes/InjectInView.php`      | 52    | Injects a `Model` (as `model` + `object`) or `Collection` (as `objectsCollection`) into the Twig view `ParametersBag`.                                                                                                                                                                                                                                      |
| `RegisterModel`     | `appliance/Recipe/Step/Kubernetes/RegisterModel.php`     | 50    | Registers a custom Kubernetes model name → repository class mapping in the `RepositoryRegistry`. Validates the class extends `Repository`.                                                                                                                                                                                                                  |

### Infrastructure

#### Symfony Command (1)

| File                                                             | Lines | Purpose                                                                                                                                                                                                                                                   |
|------------------------------------------------------------------|-------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `appliance/Infrastructures/Command/InstallDockerHostCommand.php` | 128   | CLI trigger for `InstallDockerHost` plan. Command name: `teknoo:space:enterprise:install-docker-host`. Arguments: `address` (SSH URL), `client-key` (inline PEM or file path). Options: `--username`, `--known-hosts`. Resolves file paths via Flysystem. |

#### Ansible Templates (3)

| File                                                                     | Lines | Purpose                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
|--------------------------------------------------------------------------|-------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `appliance/Infrastructures/AnsibleHost/templates/bootstrap.yml`          | 214   | Enterprise bootstrap playbook. Turns a bare Linux host into a docker-compose deploy target: rootless Docker Engine + Compose v2, SSH deploy user, rootless Traefik v3 (file provider). Covers Debian/Ubuntu (apt) and RHEL/AlmaLinux/Rocky/Fedora (dnf). Idempotent. Expected extraVars: `deploy_user`, `deploy_root`, `network_driver`, `traefik_container`, `traefik_dynamic_dir`, `traefik_certs_dir`, `traefik_default_certresolver`, `traefik_entrypoint_web`, `traefik_entrypoint_websecure`, `traefik_entrypoint_tcp`, `traefik_entrypoint_udp`, `https_backend_insecure_skip_verify`. |
| `appliance/Infrastructures/AnsibleHost/templates/traefik.compose.yml.j2` | 23    | Traefik v3 rootless Compose stack template. Uses file provider only (no Docker socket).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| `appliance/Infrastructures/AnsibleHost/templates/traefik.static.yml.j2`  | 32    | Traefik v3 static configuration template. File provider watching dynamic dir. Optional ACME cert resolver.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |

#### Kubernetes Models (2)

| File                                                                 | Lines | Purpose                                                                           |
|----------------------------------------------------------------------|-------|-----------------------------------------------------------------------------------|
| `appliance/Infrastructures/Kubernetes/Model/VulnerabilityReport.php` | 30    | Trivy `VulnerabilityReport` model. `apiVersion: aquasecurity.github.io/v1alpha1`. |
| `appliance/Infrastructures/Kubernetes/Model/ConfigAuditReport.php`   | 30    | Trivy `ConfigAuditReport` model. `apiVersion: aquasecurity.github.io/v1alpha1`.   |

#### Kubernetes Repositories (2)

| File                                                                                | Lines | Purpose                                                            |
|-------------------------------------------------------------------------------------|-------|--------------------------------------------------------------------|
| `appliance/Infrastructures/Kubernetes/Repository/VulnerabilityReportRepository.php` | 37    | Repository for `VulnerabilityReport`. URI: `vulnerabilityreports`. |
| `appliance/Infrastructures/Kubernetes/Repository/ConfigAuditReportRepository.php`   | 37    | Repository for `ConfigAuditReport`. URI: `configauditreports`.     |

#### Kubernetes Collections (2)

| File                                                                                | Lines | Purpose                                    |
|-------------------------------------------------------------------------------------|-------|--------------------------------------------|
| `appliance/Infrastructures/Kubernetes/Collection/VulnerabilityReportCollection.php` | 33    | Collection type for `VulnerabilityReport`. |
| `appliance/Infrastructures/Kubernetes/Collection/ConfigAuditReportCollection.php`   | 33    | Collection type for `ConfigAuditReport`.   |

### Bundle (3 files)

| File                                                                      | Lines | Purpose                                                                                      |
|---------------------------------------------------------------------------|-------|----------------------------------------------------------------------------------------------|
| `appliance/Bundle/TeknooSpaceEnterpriseBundle.php`                        | 28    | Empty Symfony bundle class. Registered in `Extension::executeFor(Bundles::class)`.           |
| `appliance/Bundle/DependencyInjection/TeknooSpaceEnterpriseExtension.php` | 47    | Symfony DI extension. Loads `services.yaml`.                                                 |
| `appliance/Bundle/DependencyInjection/Configuration.php`                  | 33    | DI configuration tree builder. Defines `teknoo_space_enterprise` config key (no sub-params). |

### Routes (4 files, 9 endpoints)

| Route File                                                         | Lines | Endpoints                                                                                                                                                                                                                    | Scope                           |
|--------------------------------------------------------------------|-------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------|
| `appliance/routes/space.entreprise.admin.install-docker-host.yaml` | 7     | `space_enterprise_admin_install_docker_host` (`GET/POST/PUT /admin/enterprise/docker-host/install/{id}`)                                                                                                                     | Admin                           |
| `appliance/routes/space.entreprise.trivy.yaml`                     | 23    | `space_enterprise_trivy_vulnerabilities_reports` (`GET/POST/PUT /enterprise/reports/vulnerabilities`), `space_enterprise_trivy_vulnerabilities_report_get` (`GET/POST/PUT /enterprise/reports/vulnerability/{cluster}/{id}`) | User-facing                     |
| `appliance/routes/space.entreprise.admin.trivy.yaml`               | 51    | `space_enterprise_admin_trivy_vulnerabilities_reports`, `space_enterprise_admin_trivy_vulnerabilities_report_get`, `space_enterprise_admin_trivy_audits_reports`, `space_enterprise_admin_trivy_audits_report_get`           | Admin (`allowEmptyCredentials`) |
| `appliance/routes/space.entreprise.media.yaml`                     | 9     | `space_enterprise_media` (`GET /enterprise/img/{fileName}.{type}`)                                                                                                                                                           | User-facing                     |

### Twig Templates (8 files)

| File                                                                      | Lines | Purpose                                                                                            |
|---------------------------------------------------------------------------|-------|----------------------------------------------------------------------------------------------------|
| `appliance/Bundle/templates/container.html.twig`                          | 0     | Empty placeholder                                                                                  |
| `appliance/Bundle/templates/menu/top_header.html.twig`                    | 0     | Empty placeholder                                                                                  |
| `appliance/Bundle/templates/menu/left.html.twig`                          | 28    | Sidebar menu: Trivy links (vulnerabilities + audits) for admin, vulnerabilities only for non-admin |
| `appliance/Bundle/templates/part/left_brand.html.twig`                    | 4     | Enterprise logo (SVG) in sidebar brand area                                                        |
| `appliance/Bundle/templates/cluster/list_item.html.twig`                  | 8     | Docker-compose cluster action icon in cluster list (links to install endpoint)                     |
| `appliance/Bundle/templates/cluster/edit_item_action.html.twig`           | 11    | Docker-compose cluster "Install" button in cluster edit view                                       |
| `appliance/Bundle/templates/kubernetes/reports/vulnerabilities.html.twig` | 91    | Vulnerability reports list view with table (namespace, name, age, severity counts)                 |
| `appliance/Bundle/templates/kubernetes/reports/vulnerability.html.twig`   | 219   | Single vulnerability detail view (metadata, scanner info, severity breakdown, checks table)        |
| `appliance/Bundle/templates/kubernetes/reports/audits.html.twig`          | 81    | Configuration audit reports list view (same structure as vulnerabilities)                          |
| `appliance/Bundle/templates/kubernetes/reports/audit.html.twig`           | 207   | Single audit detail view (same structure as vulnerability)                                         |
| `appliance/Bundle/templates/kubernetes/part/cluster-selector.html.twig`   | 25    | Cluster/environment selector dropdown for Trivy views                                              |

### Configuration (8 files)

| File                                                  | Lines | Purpose                                                                                                                                                                                                                                                                                                                                                     |
|-------------------------------------------------------|-------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `appliance/config/di.php`                             | 44    | Decorates `FeaturesRequirementCompiler` to validate `enterprise` and `bigbang` features.                                                                                                                                                                                                                                                                    |
| `appliance/config/di.recipe.plans.php`                | 134   | Registers 4 plans (GetEnterpriseMedia, GetFromKubernetes, ListFromKubernetes, InstallDockerHost) + 7 steps (BuildInventory, BuildExtraVars, RunBootstrap, FindFile, CreateClient, CreateRole, CreateRoleBinding, GetManifest, InjectInView, ListManifest, RegisterModel) + decorates `AccountEnvironmentInstall` with CreateRole + CreateRoleBinding steps. |
| `appliance/config/di.recipe.steps.php`                | 105   | Defines 7 steps + 2 DI params (`teknoo.space.enterprise.flysystem.asset`, `teknoo.space.enterprise.flysystem.inventory`).                                                                                                                                                                                                                                   |
| `appliance/config/global_variables.php`               | 31    | 8 global compilation variables: ROOT, SPACE_REGISTRY, BIGBANG_REGISTRY, BIGBANG_APP_ROOT, BIGBANG_APP_DOCUMENT_ROOT, BIGBANG_APP_DIRECTORY_INDEX, SC_DEFAULT, SC_REPLICATED.                                                                                                                                                                                |
| `appliance/config/compilation.hooks.collection.php`   | 215   | 15 hooks: composer (4 versions), symfony_console (4 versions), make, npm (4 versions), pip (3 versions).                                                                                                                                                                                                                                                    |
| `appliance/config/compilation.pods.library.php`       | 73    | 4 pod extends: httpd-php-fpm, httpd-php-module, mongodb, mariadb.                                                                                                                                                                                                                                                                                           |
| `appliance/config/compilation.containers.library.php` | 99    | 5 container extends: httpd, php-fpm, php-httpd, mongodb, mariadb.                                                                                                                                                                                                                                                                                           |
| `appliance/config/compilation.services.library.php`   | 51    | 3 services: mongodb, mariadb, web.                                                                                                                                                                                                                                                                                                                          |
| `appliance/config/compilation.ingresses.library.php`  | 42    | 2 ingresses: webapp, website.                                                                                                                                                                                                                                                                                                                               |

### Other Files

| File                                             | Lines | Purpose                                                                                                                                           |
|--------------------------------------------------|-------|---------------------------------------------------------------------------------------------------------------------------------------------------|
| `appliance/Extension.php`                        | 156   | Main extension class. Implements `ExtensionInterface`. Registers PHPDI params, routes, Twig templates, frontend assets (CSS), and test extension. |
| `appliance/ExtensionOfTest.php`                  | 119   | Test extension. Adds enterprise `Role` and `RoleBinding` manifests to expected Kubernetes manifests for Behat tests.                              |
| `appliance/Twig/Extension/SinceTodayFilter.php`  | 50    | Twig filter `space_enterprise_since_today` — computes days between a date and now.                                                                |
| `appliance/Bundle/translations/messages.en.yaml` | 58    | 57 translation keys for Trivy UI labels.                                                                                                          |
| `appliance/Bundle/config/services.yaml`          | 17    | Service definitions: autowiring + InstallDockerHostCommand.                                                                                       |
| `appliance/Bundle/config/endpoints.yaml`         | 33    | 4 endpoint services: get_media, get_from_kubernetes, list_from_kubernetes, install_docker_host.                                                   |
| `appliance/Makefile`                             | 11    | Help command.                                                                                                                                     |
| `appliance/behat.yml`                            | 0     | Empty — no Behat config in this repo.                                                                                                             |

### Tests (5 PHPUnit files, ~369 lines)

| Test File                                                                  | Lines | Covers                                                                                                                       |
|----------------------------------------------------------------------------|-------|------------------------------------------------------------------------------------------------------------------------------|
| `appliance/Tests/Recipe/Step/DockerCompose/RunBootstrapTest.php`           | 68    | RunBootstrap: verifies runner factory is called with correct address, credentials, playbook path, inventory path, extraVars. |
| `appliance/Tests/Recipe/Step/DockerCompose/BuildExtraVarsTest.php`         | 70    | BuildExtraVars: verifies only present DI params are mapped to extraVars.                                                     |
| `appliance/Tests/Recipe/Step/DockerCompose/BuildInventoryTest.php`         | 80    | BuildInventory: verifies inventory file content (host, port, user) and error on invalid address.                             |
| `appliance/Tests/Recipe/Plan/InstallDockerHostTest.php`                    | 71    | InstallDockerHost: verifies plan construction and `train()` returns `EditablePlanInterface`.                                 |
| `appliance/Tests/Infrastructures/Command/InstallDockerHostCommandTest.php` | 86    | InstallDockerHostCommand: verifies CLI execution flow (success + failure paths).                                             |

### Integration Points

#### How the extension integrates with core Space

1. **Extension registration**: `Extension.php` is loaded by the Space kernel. It registers the
   `TeknooSpaceEnterpriseBundle` (which loads `services.yaml` and `endpoints.yaml`), imports route files, injects Twig
   templates, and updates frontend assets.

2. **DI decoration of core plans**: `di.recipe.plans.php` decorates `AccountEnvironmentInstall` (core Kubernetes plan)
   by injecting `CreateRole` (step 61) and `CreateRoleBinding` (step 71) into the plan's workplan. This ensures every
   account environment installation also creates the Trivy RBAC role and binding.

3. **FeaturesRequirementCompiler decoration**: `di.php` decorates the core `FeaturesRequirementCompiler` to validate
   `enterprise` and `bigbang` features.

4. **Test extension**: `ExtensionOfTest` implements `ExpectedManifestTestExtension` to inject enterprise `Role` and
   `RoleBinding` manifests into expected Kubernetes manifests for Behat tests.

#### CLI Usage

```bash
bin/console teknoo:space:enterprise:install-docker-host \
    ssh://deployer@docker-host.example.com:22 \
    "$(cat /path/to/id_ed25519)" \
    --username=deployer \
    --known-hosts="/path/to/known_hosts"
```

#### HTTP Endpoints

| Scope | Method       | Path                                                     | Controller                |
|-------|--------------|----------------------------------------------------------|---------------------------|
| Admin | GET/POST/PUT | `/admin/enterprise/docker-host/install/{id}`             | `InstallDockerHost` plan  |
| User  | GET/POST/PUT | `/enterprise/reports/vulnerabilities`                    | `ListFromKubernetes` plan |
| User  | GET/POST/PUT | `/enterprise/reports/vulnerability/{cluster}/{id}`       | `GetFromKubernetes` plan  |
| Admin | GET/POST/PUT | `/admin/enterprise/reports/vulnerabilities`              | `ListFromKubernetes` plan |
| Admin | GET/POST/PUT | `/admin/enterprise/reports/vulnerability/{cluster}/{id}` | `GetFromKubernetes` plan  |
| Admin | GET/POST/PUT | `/admin/enterprise/reports/audits`                       | `ListFromKubernetes` plan |
| Admin | GET/POST/PUT | `/admin/enterprise/reports/audit/{cluster}/{id}`         | `GetFromKubernetes` plan  |
| User  | GET          | `/enterprise/img/{fileName}.{type}`                      | `GetEnterpriseMedia` plan |

---

## Known Issues & Deferred Follow-ups

From `.agents/feedback/2026-07-01-ansible-docker.md`:

1. **SSH key rotation** — no mechanism to rotate SSH keys without re-provisioning. Needs a `ssh_key_rotation` step.
2. **Compose file validation** — no pre-deployment validation of generated `docker-compose.yml`. Consider adding a
   dry-run mode.
3. **Multi-host support** — current implementation assumes a single Docker host. Multi-host clusters need orchestration.
4. **Rollback strategy** — no automatic rollback on deployment failure. Manual intervention required.
5. **Health checks** — no built-in health check integration for deployed containers.
6. **Volume management** — persistent volumes not handled. Need volume provisioning steps.
7. **Network isolation** — no Docker network isolation between projects. Shared network namespace.
8. **Resource limits** — no CPU/memory limits enforced at the Docker Compose level.
9. **Logging aggregation** — no centralized logging for docker-compose containers.
10. **Monitoring** — no metrics collection for deployed services.

---

## Verification

- **Full Behat suite passes** — all existing Kubernetes scenarios unchanged
- **PHPUnit tests pass** — 17 test files updated/new, all passing
- **PHPStan passes** — no type errors introduced
- **No breaking changes** — existing Kubernetes deployments unaffected
