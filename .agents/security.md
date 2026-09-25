# Security & Access Control

Thin reference for the security model. **See `documentation/` for full details.**

## Security Voters

Symfony Security Voters provide fine-grained entity-level authorization. Registered as services and checked
via `#[IsGranted]` or `AuthorizationChecker` during recipe steps and controllers.

| Voter            | Protects              | Logic                            |
|------------------|-----------------------|----------------------------------|
| **AdminVoter**   | Admin-only operations | Checks `ROLE_ADMIN` role         |
| **AccountVoter** | Account entities      | Validates account ownership      |
| **JobVoter**     | Job entities          | Validates via project ownership  |
| **ProjectVoter** | Project entities      | Validates via account membership |
| **UserVoter**    | User entities         | Own profile or admin             |

→ `documentation/domain.md#security-voters` · `documentation/infrastructure.md#security`

## ObjectAccessControl / ListObjectsAccessControl

Recipe plans use step-level access control through interfaces from Teknoo East Common:

- **ObjectAccessControlInterface** — single-entity ACL check (e.g. "can user access this Job?")
- **ListObjectsAccessControlInterface** — collection-level ACL check (e.g. "which Jobs can user list?")

Access control is baked into the recipe at step registration time (priority 20–30) so unauthorized
requests fail early. See `documentation/architecture.md#7-access-control--objectaccesscontrol`.

## API Token → JWT Flow

1. User generates an API token from the Web UI (user settings → API Keys)
2. `POST /api/v1/login` with `username` = `"<token_name>:<email>"` and `token` = `"<token_value>"`
3. Returns a JWT token in `{"data": {"token": "..."}}`
4. Subsequent requests use `Authorization: Bearer <JWT_TOKEN>`

See `documentation/api.md#authentication` for full endpoint details.

## MFA (Multi-Factor Authentication)

Scheb 2FA bundle provides TOTP-based multi-factor authentication. Space generates the QR code for the
authenticator app through the `BuildQrCode` recipe step (`infrastructures/Endroid/QrCode/Recipe/Step/BuildQrCode.php` —
the only class in the `Endroid/` tree).
Backup recovery codes are also generated.

→ `documentation/architecture.md#11-security--authentication`

## SSH Key-Only Auth for Docker-Compose

Docker-compose clusters use SSH key-only authentication (no password). The SSH private key and
`known_hosts` are stored plaintext for parity with Kubernetes `token`/`clientKey`/`caCertificate`.
See `documentation/domain.md#accountcluster`.

## Secrets at Rest — Persisted Variables

Account and project persisted variables are the application's secret store, and they are encrypted before
they reach MongoDB. The wiring is `config/di.persisted_vars.encryption.php`, which builds an East PaaS
`EncryptionInterface` (phpseclib, RSA or DSA) and wraps it in `Teknoo\Space\Service\PersistedVariableEncryption`.

Keys come from the environment — `SPACE_PERSISTED_VAR_SECURITY_ALGORITHM`,
`SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY`, `SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE`,
`SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY`. The split is asymmetric **per process**: the web process holds
the public key only (it can write a new value but not read one back), while the `new_task` worker holds the
full pair. Never widen that split to make something convenient.

## Extensions

An extension's object can reuse the core voters instead of shipping its own: implementing
`AccountComponentInterface` puts it under `AccountVoter`. An `/admin` route additionally picks up the
`ROLE_ADMIN` gate from the `access_control` rules.

The two denial layers do not answer alike, which matters when writing tests: `access_control` refuses **before** any
view is rendered, so the response is HTML with a bare status, while a voter refusal goes
through the recipe and produces the JSON error envelope.
