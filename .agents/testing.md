# Testing Conventions

Thin reference for the testing setup. **See `documentation/` for full details.**

## PHPUnit Structure

`appliance/tests/` mirrors the `domain/` and `infrastructures/` directory structure. Test classes follow the
namespace pattern `Teknoo\Space\Tests\{Layer}\{SubPath}` and the file suffix `Test.php`.

The suite has been at **100% line coverage** since 2026-09-12 — do not lower it. `phpunit.dist.xml` also sets
`failOnDeprecation="true"`, `failOnWarning`, `failOnNotice` and `failOnRisky`: **a deprecation warning is a
defect**, to be tracked to its call site and removed, never silenced.

Coverage sources are `domain/`, `src/`, `infrastructures/` and `extensions/`, with `extensions/*/Tests` and
`extensions/*/config` excluded — an enabled extension is measured, its own tests are not.

→ `documentation/development.md#phpunit-structure`

## Behat Structure

Feature files live in **`appliance/features/`**, not in `tests/` — only the contexts, traits and fixtures are
under `appliance/tests/Behat/`. 43 feature files: `api.*` (the majority), `web.*`, and `worker.hooks.feature`.
The main step definitions are in `appliance/tests/Behat/SpaceContext.php`.

### Conventions adopted 2026-09-19

- **`Background:`** holds the strictly identical leading `Given` run of a file. Never reorder a step to widen a
  Background: `an account for …` / `a user, called …` act on the *last* created object.
- **Composite authentication steps** from `AuthenticationTrait` collapse the sign in / TOTP / JWT / logout
  ritual; use them instead of re-chaining the primitives.
- **A scenario title stays on one line.** A wrapped title is parsed as a description, and `behat --name` can
  never match it again.
- **Tags**: `@api`, `@web`, `@worker`, `@admin` mark the audience of a feature.
- Step patterns are matched **case-insensitively** (`/^…$/iu`), and `behat/gherkin` 4.17 does not parse `Rule:`.

→ `documentation/development.md#behat-feature-structure`

## Test Traits

In `appliance/tests/Behat/Traits/`:

| Trait                       | Purpose                                       |
|-----------------------------|-----------------------------------------------|
| `ApiTrait`                  | API request helpers                           |
| `AuthenticationTrait`       | Composite sign in / TOTP / JWT / logout steps |
| `BrowserActionTrait`        | Browser action helpers                        |
| `BrowserCrawlingTrait`      | Browser crawling/navigation                   |
| `BuilderTrait`              | Git/build helpers                             |
| `DockerComposeTrait`        | Docker Compose cluster helpers                |
| `HttpTrait`                 | HTTP request/response helpers                 |
| `JwtTrait`                  | JWT token generation                          |
| `KubernetesTrait`           | Kubernetes cluster helpers                    |
| `NotificationTrait`         | Notification/messaging helpers                |
| `PersistenceOperationTrait` | MongoDB persistence operations                |
| `PersistenceStepsTrait`     | Persistence step definitions                  |
| `WorkerTrait`               | Worker/AMQP helpers                           |

Beware `self::` in a trait property or parameter default: Symfony's `ReflectionClassResource` reflects Behat
traits standalone and fatals on it.

→ `documentation/development.md#test-traits`

## Fixtures & Running

PAAS YAML fixtures in `tests/Behat/Project/` cover 7 build scenarios: `Basic`, `WithConditions`, `WithDefaults`,
`WithExtends`, `WithHttpsBackend`, `WithJobs`, `WithExposeShortcuts` (`v1.2`).

```bash
./space.sh test            # units + behavior, with coverage
./space.sh units-tests
./space.sh behavior-test   # NB_THREADS=4; use behavior-test-mono-thread to serialise
```

**Always go through `./space.sh`.** `phpunit.xml` and `behat.yml` are gitignored and copied from their `.dist`
by the `setup-phpunit` / `setup-behat` Makefile targets; `setup-behat` additionally clears `var/cache/test` and
warms the test cache. Calling `vendor/bin/phpunit` directly on a clean checkout fails.

→ `documentation/development.md#running-tests` · `documentation/development.md#writing-behavior-tests-behat`

## Extensions

An enabled extension is picked up by the **appliance's own** configuration: `phpunit.xml` adds
`extensions/*/Tests/` to the suite, and `behat.yml` discovers extension features through
`ExtensionsDiscoveryExtension`. So an extension's tests live in `extensions/<Name>/Tests/` and its features in
`extensions/<Name>/features/`, and the ordinary `./space.sh test` runs them all.

A core unit test must **never** depend on the content of `extensions/`: `appliance/.gitignore` ignores
`/extensions/*`, so a CI checkout has nothing there. Use the `Sample` fixture extension in
`tests/fixtures/extension/` instead.
