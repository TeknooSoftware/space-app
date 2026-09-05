# Symfony Forms

Thin reference for the form layer. **See `documentation/` for full details.**

## Form Type Organization

33+ form types across 7 categories in `infrastructures/Symfony/Form/Type/`:

- **Account** (9): `AccountType`, `AccountClusterType`, `SpaceAccountType`, `VarsSetType`, `VarsType`,
  `CodeGeneratorType`, `AdminSpaceAccountType`, `SpaceSubscriptionType`, `AccountEnvironmentResumesType`
- **Project** (4): `ProjectMetadataType`, `SpaceProjectType`, `VarsSetType`, `VarsType`
- **Job** (4): `JobType`, `JobVarType`, `NewJobType`, `ApiNewJobType`
- **User** (9): `UserType`, `AdminSpaceUserType`, `SpaceUserType`, `PasswordType`, `SpacePasswordType`,
  `ApiKeysAuthType`, `JWTConfigurationType`
- **Contact** (2): `SupportType`, `AttachmentType`
- **Search** (6): `AccountSearchType`, `ProjectSearchType`, `UserSearchType`, `JobSearchType`,
  `AccountClusterSearchType`, `MediaSearchType`
- **AccountEnvironment** (1): `AccountEnvironmentResumesType`

→ `documentation/development.md#form-types`

## Data Mappers

Custom data mappers in `infrastructures/Symfony/Form/DataMapper/`:

- **AbstractVarsMapper** — base class for variable set mappers
- **AccountVarsMapper** — maps account-level persisted variables
- **ProjectVarsMapper** — maps project-level persisted variables

These handle the `VarsSetType` → `VarsType` nested form structure for variable CRUD.

## Form Templates

- **fields.html.twig** — Bootstrap 5 field rendering theme
- **fields_light.html.twig** — simplified field rendering (no Bootstrap classes)

Applied via `{% form_theme form '...' %}` or globally in `config/packages/twig.yaml`.

→ `documentation/development.md#form-types`

## Bootstrap 5 Form Theme

All forms use Bootstrap 5 styling through custom form themes. The `_space_layout.html.twig` file provides
the base layout; individual form types override blocks as needed.

## Enterprise Extension Reference

Enterprise may add form types for additional features (e.g. webhook configuration, Trivy audit settings).
These follow the same `Type/` directory structure and data mapper conventions. See
`documentation/architecture.md#5-two-repo-layout`.
