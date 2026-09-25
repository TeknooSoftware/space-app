# Symfony Forms

Thin reference for the form layer. **See `documentation/` for full details.**

## Form Type Organization

Form types live in `infrastructures/Symfony/Form/Type/`, one subdirectory per category:

- **Account**: `AccountType`, `AccountClusterType`, `SpaceAccountType`, `AdminSpaceAccountType`,
  `SpaceSubscriptionType`, `CodeGeneratorType`, `VarsSetType`, `VarsType`
- **AccountData**: `AccountDataType`
- **AccountEnvironment**: `AccountEnvironmentResumesType`
- **Project**: `SpaceProjectType`, `VarsSetType`, `VarsType`
- **ProjectMetadata**: `ProjectMetadataType`
- **Job**: `NewJobType`, `ApiNewJobType`, `JobVarType` — there is no `JobType`
- **User**: `UserType`, `SpaceUserType`, `AdminSpaceUserType`, `PasswordType`, `SpacePasswordType`,
  `ApiKeysAuthType`, `JWTConfigurationType`
- **Contact**: `SupportType`, `AttachmentType`
- **Search**: `AccountSearchType`, `AccountClusterSearchType`, `JobSearchType`, `MediaSearchType`,
  `ProjectSearchType`, `UserSearchType`, plus the shared `DefaultSearchTrait`

`VarsSetType` and `VarsType` exist twice, once under `Account/` and once under `Project/`: they are distinct
classes in distinct namespaces, not a duplication to factor out.

→ `documentation/development.md#form-types`

## Data Mappers

Custom data mappers in `infrastructures/Symfony/Form/DataMapper/`:

- **AbstractVarsMapper** — base class for variable set mappers
- **AccountVarsMapper** — maps account-level persisted variables
- **ProjectVarsMapper** — maps project-level persisted variables

These handle the `VarsSetType` → `VarsType` nested form structure for variable CRUD.

## Form Templates & Theming

- **`templates/TeknooSpace/fields.html.twig`** — Bootstrap 5 field rendering theme
- **`templates/TeknooSpace/fields_light.html.twig`** — simplified field rendering (no Bootstrap classes)

Themes are applied **per template**, not globally: `config/packages/twig.yaml` has no `form_themes` key. The
rendering templates declare them, e.g. `templates/TeknooSpace/dashboard.form.html.twig:30`:

```twig
{% form_theme formView with ['bootstrap_5_layout.html.twig', '@TeknooSpace/fields.html.twig'] %}
```

The base page layout is `templates/TeknooSpace/dashboard.layout.html.twig`; the form page extends it through
`dashboard.form.html.twig` and the list page through `dashboard.list.html.twig`.

→ `documentation/development.md#form-types`

## Extensions

An extension may ship form types of its own. They follow the same `Type/` structure and are registered by the
extension's bundle, not from `config/` here.
