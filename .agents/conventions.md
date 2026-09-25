# Coding & Data Conventions

Thin reference for agent patterns. **See `documentation/` for full details.**

## Loader/Writer Pattern

Each persisted entity has a one-to-one Loader–Writer pair: **Loaders** read from MongoDB, **Writers** persist
changes. Loaders implement `LoaderInterface`, Writers implement `WriterInterface`. Meta writers (`SpaceAccountWriter`,
`SpaceUserWriter`, `SpaceProjectWriter`) bridge East Foundation entities with the
Space domain layer. Registered in `config/di.persistent_data.php`.

→ `documentation/architecture.md#2-loaderwriter-pattern` · `documentation/infrastructure.md#loaderwriter-persistence`

## Object Layout

`domain/Object/` is split by role, and the distinction drives how an object is wired:

- **`Persisted/`** — entities stored in MongoDB, each with a Loader/Writer pair and an ODM mapping.
- **`DTO/`** — transport objects between layers, not persisted.
- **`DTO/Task/`** — the asynchronous tasks implementing `NewTaskInterface`, queued by `CallNewTask` and run by
  the `new_task` worker. This is the pivot of the current architecture: account provisioning, environment
  deletion and quota refresh are all tasks.
- **`Config/`** — configuration value objects, notably the `ConfigClusterInterface` family below.

Key interfaces, both from vendor packages rather than Space itself:

- **`IdentifiedObjectInterface`** (Teknoo East Common) — entities that carry a persistent `id`
- **`NormalizableInterface`** (Teknoo East Foundation) — objects that can be flattened to/loaded from arrays

A recipe ingredient matches by **workplan key**, while a step parameter matches by **instance** (first match
wins) — so a task DTO must not implement `ObjectInterface`, or it will be picked up as the generic object.

**A new field on a persisted object must be nullable with a runtime fallback**, so existing installations keep
working with no data migration.

See the full DTO list in `documentation/domain.md#data-transfer-objects-dtos`.

## Query Pattern

Query objects (`domain/Query/`) represent read operations following CQRS-like patterns. They are immutable
and passed to Loader instances. Examples: `LoadFromAccountQuery`, `SearchQuery`, `DeleteVariablesQuery`.
See `documentation/domain.md#query-objects`.

## ClusterConfig Abstraction

`ConfigClusterInterface` is the target-agnostic contract for cluster configuration. Implementations:

- **KubernetesCluster** — Kubernetes-specific members (`storageProvisioner`, `token`, clients)
- **DockerComposeCluster** — SSH connection data (`clientKey`, `username`, `caCertificate`/known_hosts)

See `documentation/domain.md#cluster-configuration-configclusterinterface`.

## Readonly + Property Promotion

Use `readonly` properties wherever the value never changes after construction, and prefer constructor
property promotion. This is a review convention, not something PHPStan checks — max level will not flag a
mutable property that could have been readonly.

→ `documentation/development.md#type-declarations`

## Extensions

An extension may add Loaders, Writers, DTOs and Query objects of its own, following the same patterns and
registering them from its own configuration. See `documentation/architecture.md#5-extension-repositories`
for the mounting model.
