# 2026-08-26 — Stage 2: setup docker on the task worker

## Task Summary

Moved the Enterprise "install docker host" feature off the synchronous request and onto the task worker
introduced in `9e2147d`, and gave the operation a persisted trace with a page of its own. Everything landed
under `appliance/extensions/Enterprise/`; no core file was touched.

- **DTOs** — `InstallDockerHostConfirmationDto` → `SetupDockerDto`, now a `NewTaskInterface`; new
  `RunSetupDockerDto` (second hop, deliberately *not* a `NewTaskInterface` so the core `new_task` routing
  does not capture it). For both, the sensitive payload carried through `SensitiveContentInterface` is the
  SSH `clientKey`, where `NewJob` carries its variables.
- **Persistence** — `SetupDockerJob` (an `AccountComponentInterface` holding an East PaaS `History`) plus
  loader, writer, ODM repository, repository contract, ODM XML and PHP-DI definitions.
- **Plans** — `InstallDockerHostConfirmation` → `PrepareInstallDockerHost` (modelled on core `JobStart`,
  calling `CallNewTaskInterface`); new `RunInstallDockerHost` (hop 1) and `SetupDockerJobGet` (history
  page); `InstallDockerHost` retyped to `RunSetupDockerDto` and now reports into the job history.
- **Registration** — `RunInstallDockerHost` is bound to `SetupDockerDto` by **decorating** the core
  `NewTaskRecipeRegistry`, leaving its definition alone.

## Missing Precision

- The spec said "Create a new DTO `RunSetupDockeDto`" — a typo. Confirmed with the user: `RunSetupDockerDto`.
- The spec asked to "copy behavior of route `space_job_get`". Copying `JobGet` literally would have dragged
  in `ExtractProject` / `LoadProjectMetadata` / `InjectToViewMetadata`, all of which are East PaaS
  project-specific and meaningless for a job attached to an *account*. The display plan is therefore
  `LoadObject` → `ObjectAccessControlInterface` → `Render`, all three reused verbatim.

## Blockers

Three latent bugs sat in the exact files this work rewrites; the user approved fixing them.

1. `InstallDockerHostConfirmationType` declared namespace `...\Enterprise\Form\Type` while living under
   `Infrastructures/Symfony/Form/Type/` — not autoloadable under PSR-4. Namespace corrected, route
   `formClass` updated.
2. The install route supplied neither `accountClusterLoader` nor `accountClusterKey`, both of which step 05
   maps, so `LoadObject` could never resolve. Added; step 06 also mapped `Teknoo\East\Paas\Object\Cluster`
   (never in the workplan) instead of the loaded `AccountCluster`.
3. `install_docker_host_confirm.html.twig` extended `@TeknooEastCommon/layout.html.twig`, which does not
   exist in this app — a guaranteed 500. Only surfaced once (1) and (2) were fixed and the page became
   reachable. Rebuilt on `@TeknooSpace/dashboard.form.html.twig` like every other Space form page, which
   also means the read-only fields became hidden inputs and the values are printed from the DTO.

The cancel link also pointed at `space_enterprise_admin_cluster_list`, a route that exists nowhere; it now
resolves `space_admin_account_clusters_list` from the DTO's `accountId`, falling back to the dashboard.

## Suggestions

- **`prependExtensionConfig` for `framework.messenger` needs the parameter set in `prepend()`, not
  `services.yaml`.** `FrameworkExtension::load()` resolves the transport DSN before the declaring bundle's
  own `load()` runs, so a parameter defined in the bundle's `services.yaml` produces
  *"You have requested a non-existent parameter"* at cache warmup. Setting it with
  `$container->setParameter()` inside `prepend()` fixes it. Same trap applies to any bundle-declared
  transport, mapping or config that interpolates its own parameters.
- **A bundle living outside its own root needs an absolute mapping dir.** `%kernel.project_dir%` points at
  the appliance, so the Doctrine mapping uses `dirname(__DIR__, 4) . '/config/doctrine'`. A unit test
  asserts the directory resolves and contains the XML, because an off-by-one in that depth fails silently
  (the documents simply stay unmapped).
- **The Behat harness has two extension-shaped gaps**, neither fixable without touching core:
  - `A memory document database` (core `PersistenceOperationTrait`) hard-lists the documents it builds
    in-memory, so an extension's documents are absent. `buildRepository()` is public, so an extension step
    can register its own — worth knowing before assuming Behat can't see a new document.
  - `a docker-compose orchestrator` swaps the DockerCompose *Driver* only; `RunBootstrap` asks the container
    for `RunnerFactoryInterface` directly, so a scenario otherwise attempts real SSH and the playbook
    "fails" for the wrong reason. The double must be installed *before* that step (registering the Driver
    resolves the real factory) and only once per run — Symfony refuses to replace an initialized service —
    so the per-scenario outcome is switched through a static flag the double reads at call time.
- **`EnterpriseContext` must not reuse `SpaceContext`'s traits** (its own docblock says so): both are
  registered in the same suite, so the steps would be declared twice. Its public API is enough —
  `current()`, `executeRequest()`, `getPathFromRoute()`, `recall()`, `buildRepository()`, `getRepository()`,
  `createForm()`, `getResponse()`. Getting the context's constructor resolved needs it registered as a
  public service, which the bundle now does itself in the test env rather than editing core
  `services_test.yaml`.

## Lessons Learned

- **A Recipe `with` mapping is a workplan *key*, not a literal.** `['message' => 'some.translation.key']`
  looks right and silently resolves nothing; literals need `new Teknoo\Recipe\Value('...')`. That is how
  one `PushSetupDockerHistory` step serves every reporting point in the plan.
- **`AccountCluster` exposes its state through `VisitableInterface`, not getters.** There is no
  `getMasterAddress()`; values are read with `visit(['masterAddress' => fn ($v) => ...])`, and `visit()`
  *skips properties that are not set* — so a step copying onto a DTO has to blank the targets first or a
  stale submitted value survives.
- **`Account` uses the state pattern (`ProxyTrait`)**, so `verifyAccessToUser` is reached through `__call`.
  A test must mock `__call`, exactly as the core `AccountHistoryTest` does.
- **Implementing `AccountComponentInterface` is what buys access control.** The existing `AccountVoter`
  votes on any implementor, so the new history page needed no voter and no new access-control step.
- The double registration of a messenger handler (`#[AsMessageHandler]` *and* an explicit tag with
  `sign: true`) shows up twice in `debug:messenger`. Core's `RunJobHandler` does the same; kept for
  consistency rather than diverging.

**Verification**: `make qa` exit 0 (lint, phpstan 429 files, phpcs PSR12, composer audit) and `make test`
exit 0 (1165 unit tests / 3591 assertions, 44/44 Behat features including the 3 new scenarios).
