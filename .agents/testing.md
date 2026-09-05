# Testing Conventions

Thin reference for the testing setup. **See `documentation/` for full details.**

## PHPUnit Structure

`appliance/tests/` mirrors `domain/` and `infrastructures/` directory structure. **280 test files** total.
Test classes follow the namespace pattern `Teknoo\Space\Tests\{Layer}\{SubPath}`.

→ `documentation/development.md#phpunit-structure`

## Behat Structure

43 feature files in `appliance/features/`: **API** (32: `api.account`, `api.admin.*`, `api.job.*`,
`api.project.*`, `api.user`, `api.login`, `api.settings`, `api.jwt`, `api.project.variables`,
`api.project.refresh-credentials`), **Web** (10: `web.account`, `web.job`, `web.project`, `web.login`,
`web.subscription`, `web.user.settings` + variants), **Worker hook** (1: `worker.hooks`).

→ `documentation/development.md#behat-feature-structure`

## Test Traits (12)

| Trait | Purpose |
|-------|---------|
| `ApiTrait` | API request helpers |
| `BrowserActionTrait` | Browser action helpers |
| `BrowserCrawlingTrait` | Browser crawling/navigation |
| `BuilderTrait` | Git/build helpers |
| `DockerComposeTrait` | Docker Compose cluster helpers |
| `HttpTrait` | HTTP request/response helpers |
| `JwtTrait` | JWT token generation |
| `KubernetesTrait` | Kubernetes cluster helpers |
| `NotificationTrait` | Notification/messaging helpers |
| `PersistenceOperationTrait` | MongoDB persistence operations |
| `PersistenceStepsTrait` | Persistence step definitions |
| `WorkerTrait` | Worker/AMQP helpers |

→ `documentation/development.md#test-traits`

## Fixtures & Running

PAAS YAML fixtures in `tests/` cover 6 build scenarios. Run all tests with `./space.sh test`,
unit tests with `./space.sh units-tests`, or Behat with `./space.sh behavior-test`.

→ `documentation/development.md#running-tests` · `documentation/development.md#writing-behavior-tests`

## Enterprise Extension Reference

Enterprise tests follow the same structure. Enterprise feature files live in the `space-app-enterprise`
repo. See `documentation/architecture.md#5-two-repo-layout`.
