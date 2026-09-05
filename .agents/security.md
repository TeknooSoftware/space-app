# Security & Access Control

Thin reference for the security model. **See `documentation/` for full details.**

## Security Voters

Symfony Security Voters provide fine-grained entity-level authorization. Registered as services and checked
via `#[IsGranted]` or `AuthorizationChecker` during recipe steps and controllers.

| Voter | Protects | Logic |
|-------|----------|-------|
| **AdminVoter** | Admin-only operations | Checks `ROLE_ADMIN` role |
| **AccountVoter** | Account entities | Validates account ownership |
| **JobVoter** | Job entities | Validates via project ownership |
| **ProjectVoter** | Project entities | Validates via account membership |
| **UserVoter** | User entities | Own profile or admin |

→ `documentation/domain.md#security-voters` · `documentation/infrastructure.md#voters`

## ObjectAccessControl / ListObjectsAccessControl

Recipe plans use step-level access control through interfaces from Teknoo East Common:

- **ObjectAccessControlInterface** — single-entity ACL check (e.g. "can user access this Job?")
- **ListObjectsAccessControlInterface** — collection-level ACL check (e.g. "which Jobs can user list?")

Access control is baked into the recipe at step registration time (priority 20–30) so unauthorized
requests fail early. See `documentation/architecture.md#7-access-control-objectaccesscontrol`.

## API Token → JWT Flow

1. User generates an API token from the Web UI (user settings → API Keys)
2. `POST /api/v1/login` with `username` = `"<token_name>:<email>"` and `token` = `"<token_value>"`
3. Returns a JWT token in `{"data": {"token": "..."}}`
4. Subsequent requests use `Authorization: Bearer <JWT_TOKEN>`

See `documentation/api.md#authentication` for full endpoint details.

## MFA (Multi-Factor Authentication)

Scheb 2FA bundle provides TOTP-based multi-factor authentication. Space generates QR codes via the
Endroid QR Code integration (`infrastructures/Endroid/QrCode/QrCodeGenerator`) for authenticator app
setup. Backup recovery codes are also generated.

→ `documentation/architecture.md#11-security-and-authentication`

## SSH Key-Only Auth for Docker-Compose

Docker-compose clusters use SSH key-only authentication (no password). The SSH private key and
`known_hosts` are stored plaintext for parity with Kubernetes `token`/`clientKey`/`caCertificate`.
See `documentation/domain.md#accountcluster`.

## Enterprise Extension Reference

Enterprise may add additional voters or extend the authentication flow. The two-repo model means
Enterprise security code lives in `space-app-enterprise`. See
`documentation/architecture.md#5-two-repo-layout`.
