# 2026-09-03 — API routes for the Enterprise "setup docker" flow

**Status**: ✅ Resolved

## Task Summary

Exposed the Enterprise docker host setup through the API, so a host bootstrap can be triggered and followed
without a browser. Everything lives in `appliance/extensions/Enterprise/` (the remounted enterprise repo);
no core-app file changed.

- **Routes** — new `routes/space.entreprise.api.v1.admin.setup-docker.yaml` with three entries.
  `Extension::configureRoutes()` imports `routes/*.{php,yaml}` flat, with no prefix (core's `/api/v1/admin`
  prefix in `config/routes/api.yaml` only covers `config/routes/api/v1/*`), so the full path is spelled
  inline:
  - `space_enterprise_api_v1_admin_setup_docker_confirm` — `POST|PUT /api/v1/admin/enterprise/docker-host/install/{clusterId}`
  - `space_enterprise_api_v1_admin_setup_docker_pending` — `GET /api/v1/admin/enterprise/docker-host/pending/{taskId}`
  - `space_enterprise_api_v1_admin_setup_docker_get` — `GET /api/v1/admin/enterprise/docker-host/setup/{id}`

  Confirm and get reuse the existing endpoints unchanged: `PrepareInstallDockerHost` already makes `route`
  optional when `api` is set and its `JumpIf(testValue: 'api')` renders instead of redirecting, and
  `SetupDockerJobGet` passes `api` through to `Render`. `cleanHtml` is dropped (`TemplateTrait` forces it off
  in api mode) and so is `route`.
- **Pending plan** — `Recipe/Plan/SetupDockerPending` (fetch step → `Render`, `onError` → `RenderError`),
  modelled on core `JobPending` minus its project steps, plus a second instance of the core step class
  `…\Recipe\Step\Mercure\FetchJobIdFromPending` bound to the Enterprise Mercure topic
  (`teknoo.space.enterprise.setup_docker.fetch_task_id_from_pending`), a `RecipeEndPoint`, and a PHP-DI entry.
- **Serialization** — `SetupDockerJob` gained `NormalizableInterface` + `AutoTrait` +
  `#[ClassGroup('default','api','digest')]`, with `#[Normalize]` on `name`, `masterAddress` and `history`.
  `account` is deliberately left out so the whole account graph is not dragged in.
- **Views** — `setup_docker/host_confirm.json.twig` (rewritten; it was dead and broken), `get.json.twig`,
  `pending.json.twig`.
- **Tests** — `features/api.admin.setup_docker.feature` (6 scenarios: the web feature's 3, each in a
  form-url-encoded and a JSON-body variant), 6 new steps in `Tests/Behat/Context/EnterpriseContext.php`,
  `Tests/Recipe/Plan/SetupDockerPendingTest.php`, and an `exportToMeData()` case on `SetupDockerJobTest`.

**Verification**: `APP_ENV=test bin/console cache:warmup` OK; `debug:router` shows the three routes;
`make qa` exits 0 (lint + phpstan max + phpcs + audit); `make test` exits 0
(1161 PHPUnit tests / 3565 assertions, 45/45 Behat features, Enterprise suite 9/9 scenarios / 197 steps).

## Missing Precision

- **The plan's Behat body for the form-url-encoded variant was too small.** It said to send only
  `install_docker_host_confirmation.name`. A form-url-encoded body goes through Symfony's
  `HttpFoundationRequestHandler`, which submits the *whole* form — the absent `masterAddress` then hits
  `SetupDockerDto::$masterAddress` (non-nullable `string`) and the request answers **500**
  `InvalidTypeException: Expected argument of type "string", "null" given at property path "masterAddress"`.
  Only the JSON path is partial (`FormHandling` calls `submit($values, false)`). The feature now sends all
  four fields in the url-encoded variants, with placeholder values — which also demonstrates that submitted
  values carry no authority, since `PopulateSetupDockerDto::refresh` re-reads the cluster server side.
- **The `@class` meta is also emitted inside `data`.** The `api`-group export of `SetupDockerJob` is
  `['@class', 'history', 'name', 'masterAddress']`, not just the three `#[Normalize]`d properties — `AutoTrait`
  prepends it. Worth knowing before writing an exact-keys assertion.

## Blockers

- **The bootstrap-runner double was only installed for the first scenario of the run.** `EnterpriseContext`
  guarded the installation behind a static `$bootstrapRunnerInstalled`, on the stated assumption that "the
  kernel is booted once for the whole suite". It is not: FriendsOfBehat's SymfonyExtension reboots the kernel
  between scenarios, so `getContainer()->set(RunnerFactoryInterface::class, …)` is lost every time and every
  scenario after the first reached the real `SymfonyProcessRunner` (`Ansible playbook execution failed`).
  The existing web feature never caught it because only its *first* scenario asserts a successful bootstrap
  and its third one expects a failure anyway — a real runner failing looks the same. Fixed by re-registering
  the double on every scenario (the Background step still runs before `a docker-compose orchestrator`, which
  is what resolves the real factory).

## Suggestions

- **`space_api_v1_*` route naming**: the new routes deliberately keep the web URL parameters
  (`{clusterId}`, `{taskId}`, `{id}`) and skip core's `/account/{accountId}/` segment, because a
  `SetupDockerJob` hangs off an account cluster, not off a project. Worth stating in `.agents/api.md` so the
  next agent does not "fix" it into the core shape.
- **`TaskUrlPublisher::publish()` receives only `taskId` + `taskUrl` from `SetupDockerJobUpdaterNotifier`**,
  so no setup id travels on the Mercure topic and `pending.json.twig` cannot build an `api_url` the way
  `api/AdminJob/pending.json.twig` does. If API clients need it, the notifier has to publish the job id.
- **Partial submit for form-url-encoded API bodies** would remove the 500 above. Out of scope here, but
  `FormHandling` could `submit($parsedBody[$form->getName()] ?? [], false)` in api mode rather than deferring
  to `handleRequest`.

## Lessons Learned

- **Extension routes are imported flat.** `Extension::configureRoutes()` does a bare
  `$routes->import(__DIR__.'/routes/*.{php,yaml}')`; the `/api/v1/admin` prefix core applies in
  `config/routes/api.yaml` does not reach an extension. Spell the whole path in the extension YAML.
- **East endpoints really do serve web and API from one recipe.** Adding an API surface to an existing flow
  needed no recipe or plan change for confirm/get — only routes with `api: 'json'` and JSON views. Check for
  `IngredientWithCondition(fn (array &$workplan) => empty($workplan['api']), …)` and a
  `JumpIf(testValue: 'api')` in the plan: if both are there, the plan is already API-ready.
- **A Behat double installed once per run is a trap.** The kernel is rebooted between scenarios, so anything
  pushed into the Symfony container with `->set()` must be pushed again in every Background.
- **PHPStan max dislikes chained offsets on a decoded JSON body.** `json_decode(...)['data']['x']` is
  `mixed`-offset access 15 times over. A single `responseValue(string ...$keys): mixed` walker that returns
  `null` on a missing key keeps the steps readable and the analysis clean.
