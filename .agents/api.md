# API Structure

Thin reference for the API layer. **See `documentation/` for full details.**

## Route File Organization

**API v1 routes** (`config/routes/api/v1/`): 10 YAML files across 3 subdirectories:
- **unauthenticated/**: `login.yaml` (public login endpoint)
- **authenticated/**: `account.yaml`, `job.yaml`, `jwt.yaml`, `project.yaml`, `settings.yaml` (5 files)
- **admin/**: `account.yaml`, `job.yaml`, `project.yaml`, `user.yaml` (4 files)

**Web routes** (`config/routes/`): 10 YAML files (`space.account.yaml`, `space.admin.account.yaml`,
`space.admin.job.yaml`, `space.dashboard.yaml`, `space.health.yaml`, `space.job.yaml`,
`space.project.yaml`, `space.settings.yaml`, `space.subscription.yaml`,
`space.support.contact.yaml`) containing 48 `path:` entries.

→ `documentation/api.md#route-file-organization`

## JSON Template Structure

API responses are rendered via JSON templates in `templates/TeknooSpace/api/`, organized by resource:

- **Account/**: `list.html.twig`, `new.html.twig`, `get.html.twig`, `deleted.html.twig`, `pending.html.twig`
- **Job/**: `list.html.twig`, `new.html.twig`, `get.html.twig`, `deleted.html.twig`, `pending.html.twig`
- **Project/**: `list.html.twig`, `new.html.twig`, `get.html.twig`, `deleted.html.twig`
- **AdminAccount/**, **AdminJob/**, **AdminProject/**, **AdminUser/**: list/get/deleted variants
- **User/**: `get.html.twig`, `settings.html.twig`

Each template renders `{"data": {...}}` or `{"error": {...}}`. Controllers call `renderView()` with the
appropriate template, or use `#[Template]` for auto-rendering.

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

## Enterprise Extension Reference

Enterprise may add additional API endpoints (e.g. Trivy audit, webhook endpoints). These are registered
via Enterprise's own route YAML files in the `space-app-enterprise` repo. See
`documentation/architecture.md#5-two-repo-layout`.
