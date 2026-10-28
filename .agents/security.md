# Security & Access Control

Thin reference for the security model. **See `documentation/` for full details.**

## Security Voters

Symfony Security Voters provide fine-grained entity-level authorization. Registered as services and checked
via `#[IsGranted]` or `AuthorizationChecker` during recipe steps and controllers.

| Voter            | Protects                   | Logic                                                       |
|------------------|----------------------------|-------------------------------------------------------------|
| **AdminVoter**   | Operations of the admin    | Matrix of objects and attributes for `ROLE_ADMIN`, see below |
| **AccountVoter** | Account entities           | Validates account ownership; abstains for an admin          |
| **JobVoter**     | Job entities               | Validates via project ownership; abstains for an admin      |
| **ProjectVoter** | Project entities           | Validates via account membership; abstains for an admin     |
| **UserVoter**    | User entities              | Own profile only                                            |

The access control steps vote an attribute fixed in the code, never read from the workplan: `ObjectAccessControl`
votes `access`, `ListObjectsAccessControl` votes `list`. `AdminVoter` grants `access` and `list` on `Account`,
`SpaceAccount`, `User`, `SpaceUser` and `Job`, and only `list` on `Project` and `SpaceProject`; it abstains on
everything else (other objects, roles, expressions). An administrator of Space is never a member of an account, so the
member voters abstain for it.

`access_control` completes the voters: rules refuse to everyone (`allow_if: 'false'`) the admin routes whose object is
allowed but whose operation is not (variables, clusters and environments of an account, media CMS), and other rules
refuse the area of the members (projects, jobs, account settings, and their `/api/v1` twins) to `ROLE_ADMIN`. Use
`allow_if` alone in these rules: Symfony adds the expression to the `roles` of the rule. A member route that loads an
account by its `{id}` (`/account/{id}/environment/.../reinstall`) needs such a rule: `AdminVoter` grants `access` on
the account to an administrator. Its API twin (`/api/v1/account/{id}/environment/.../reinstall`) is covered by the
rule of the `/api/v1` members area.

`ROLE_PENDING` is a user attached to no account: the first sign-in with OAuth gives it, or the admin user form (alone,
never for a user of an account). It does not include `ROLE_USER`, so the default rule refuses it: only `/`,
`/my-settings` and its password, `/2fa/`, `/contact/` and `/media/` accept it. The API refuses it (`allow_if`), so it
has no API key and no JWT. Its dashboard shows a message (`RenderWithoutAccount`). Attached to an account by an
administrator, it gets `ROLE_USER` (`AdminSpaceAccountType`); the web session keeps the roles of the sign-in until the
next sign-in.

The restriction prevents mistakes, not bad intent: an administrator can create a user with an email that it controls,
attach it to an account and sign in as this user through the recovery page or OAuth.

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

The two denial layers do not answer alike on the web, which matters when writing tests: `access_control` refuses
**before** any view is rendered, so the response is HTML with a bare status, while a voter refusal goes through the
recipe. On the API both answer the JSON error envelope (`ApiAccessDeniedHandler` on the firewall `api_area`).
