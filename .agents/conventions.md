# Coding & Data Conventions

Thin reference for agent patterns. **See `documentation/` for full details.**

## Loader/Writer Pattern

Each persisted entity has a one-to-one Loader–Writer pair: **Loaders** read from MongoDB, **Writers** persist
changes. Loaders implement `LoaderInterface`, Writers implement `WriterInterface`. Meta writers
(`SpaceAccountWriter`, `SpaceUserWriter`, `SpaceProjectWriter`) bridge East Foundation entities with the
Space domain layer. Registered in `config/di.persistent_data.php`.

→ `documentation/architecture.md#2-loaderwriter-pattern` · `documentation/infrastructure.md#loaderwriter-persistence`

## DTO Pattern

DTOs live in `domain/Object/DTO/` and facilitate data exchange between layers. Key interfaces:

- **IdentifiedObjectInterface** — entities that carry a persistent `id`
- **NormalizableInterface** — objects that can be flattened to/loaded from arrays

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

Use `readonly` properties wherever the value never changes after construction. Prefer constructor property
promotion. PHPStan at max level enforces this convention.

→ `documentation/development.md#type-declarations`

## Enterprise Extension Reference

Enterprise bundles may add their own Loaders/Writers/DTOs. They follow the same patterns — see
`documentation/architecture.md#5-two-repo-layout` for the two-repo mounting model.
