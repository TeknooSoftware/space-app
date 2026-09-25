# API Structure

Thin reference for the API layer. **See `documentation/` for full details.**

## Route File Organization

**API v1 routes** (`config/routes/api/v1/`): 10 YAML files across 3 subdirectories. Every file is prefixed
`space.api.v1.` — the bare names (`account.yaml`, `job.yaml`, …) do not exist:

- **unauthenticated/**: `space.api.v1.login.yaml`
- **authenticated/**: `space.api.v1.account.yaml`, `.job.yaml`, `.jwt.yaml`, `.project.yaml`, `.settings.yaml`
- **admin/**: `space.api.v1.account.yaml`, `.job.yaml`, `.project.yaml`, `.user.yaml`

The `/api/v1` and `/api/v1/admin` prefixes are **not** in these files: they come from the loader
`config/routes/api.yaml`, which imports the three directories under their prefix.

**Web routes** (`config/routes/`): 10 YAML files (`space.account.yaml`, `space.admin.account.yaml`,
`space.admin.job.yaml`, `space.dashboard.yaml`, `space.health.yaml`, `space.job.yaml`,
`space.project.yaml`, `space.settings.yaml`, `space.subscription.yaml`,
`space.support.contact.yaml`) containing 48 `path:` entries. The same directory also holds the
framework and vendor route files (`api.yaml`, `connect.oauth.yaml`, `east.common.include.yaml`,
`east.paas.include.yaml`, four `east.paas.overwrite.*.yaml`, `scheb_2fa.yaml`, `symfony.framework.yaml`,
`web_profiler.yaml`).

→ `documentation/api.md#route-file-organization`

## JSON Template Structure

API responses are rendered by Twig templates in `templates/TeknooSpace/api/`. They are **`.json.twig`**, never
`.html.twig`, and the file names differ per resource — a single object is usually `item`, not `get`:

| Directory         | Templates                                              |
|-------------------|--------------------------------------------------------|
| `Account/`        | `environments`, `settings`, `status`, `variables`      |
| `AccountCluster/` | `deleted`, `item`, `list`                              |
| `Job/`            | `deleted`, `get`, `list`, `new`, `pending`             |
| `Jwt/`            | `jwt.form`, `jwt.token`                                |
| `Project/`        | `deleted`, `item`, `list`, `variables`                 |
| `User/`           | `settings`                                             |
| `AdminAccount/`   | `deleted`, `environments`, `item`, `list`, `variables` |
| `AdminJob/`       | `deleted`, `get`, `list`, `new`, `pending`             |
| `AdminUser/`      | `deleted`, `item`, `list`                              |

There is no `AdminProject/` directory: admin project responses reuse the non-admin `Project/` templates.

Each template renders `{"data": {...}}` or `{"error": {...}}`. The template to use is named in the route
`defaults`, alongside `api: 'json'`.

→ `documentation/api.md#json-template-structure`

## API Auth Flow

1. Generate an API token from the Web UI (user settings → API Keys)
2. Exchange token for JWT: `POST /api/v1/login`
3. Use `Authorization: Bearer <JWT_TOKEN>` for all subsequent requests
4. JWT tokens expire based on `SPACE_JWT_TTL`

→ `documentation/api.md#authentication`

## API Endpoints

Full endpoint reference is in `documentation/api.md#api-endpoints`. Key patterns:

- User endpoints: `/api/v1/project/{projectId}/job/new`, `/api/v1/account/settings`
- Admin endpoints: `/api/v1/admin/account/{id}/...`, `/api/v1/admin/users`
- All admin routes prefixed with `/api/v1/admin`
- `/healthy` (`config/routes/space.health.yaml`) is the liveness endpoint, outside `/api/v1`

## Extensions

An extension ships its own route YAML files in its `routes/` directory. They are imported **without a
prefix**, so an extension route spells its path in full — including any `/admin` segment. Which routes an
extension adds is documented by that extension, not here.
