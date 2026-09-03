# 2026-09-03 — Letting ordinary users start an Enterprise docker host setup

**Status**: ✅ Resolved

Stage 2 of the Enterprise docker host setup work (stage 1 added the *admin* API surface —
see [2026-09-03-enterprise-api-setup-docker.md](2026-09-03-enterprise-api-setup-docker.md)).

## Task Summary

The setup flow could only be *started* by an admin: the confirm entry point existed only at
`/admin/enterprise/docker-host/install/{clusterId}` and its API twin. A member of the account owning the
cluster had to ask an operator to bootstrap their own host. The two follow-up pages
(`pending/{taskId}`, `setup/{id}`) were already user-facing, so the web side only missed the entry point;
the API side missed the whole non-admin surface.

No recipe or step changed: `PrepareInstallDockerHost` loads the `AccountCluster` named by `{clusterId}`
and runs `ObjectAccessControlInterface` on it, and `AccountCluster` being an `AccountComponentInterface`
the core `AccountVoter` grants exactly when the signed in user belongs to the owning account.

Core app (2 files):

- **`config/packages/security.yaml`** — new `access_control` rule
  `{ path: '^/api/v1/admin', roles: [ROLE_ADMIN, ~ROLE_RECOVERY] }`, placed before the `^/api` line.
  There was a real hole: `^/admin` does not match `/api/v1/admin`, so any plain `ROLE_USER` with a valid
  JWT could call every core admin API route (`AdminVoter` grants, it never vetoes).
- **`tests/Behat/Traits/BrowserCrawlingTrait.php`** — new `Then it is redirected to the login page`. The
  existing `… with an error` variant also requires a `#login-error` node, which an anonymous bounce does
  not render.

Enterprise extension:

- **Routes** — `space_enterprise_setup_docker_confirm` (`/enterprise/docker-host/install/{clusterId}`) in
  `routes/space.entreprise.setup-docker.yaml`, and a new
  `routes/space.entreprise.api.v1.setup-docker.yaml` with the three API routes (the admin paths minus
  `admin/`). Same endpoints, same recipes; only defaults and views differ.
- **Views** — `setup_docker/host_confirm.json.twig` split into `host_confirm.admin.json.twig` +
  `host_confirm.user.json.twig`, which differ only in the route name inside `meta.url`, so the url a
  client gets back stays in the route family it called (core does the same for `api/AdminJob/*` vs
  `api/Job/*`). `get.json.twig` / `pending.json.twig` carry no route name and stay shared.
- **Mercure `task_url`** repointed from `space_enterprise_admin_setup_docker_get` to
  `space_enterprise_setup_docker_get`. A user-started setup previously pushed the browser at a page it
  could not open. Matches core `JobUpdaterNotifier`, which publishes `space_job_get` even for
  admin-started jobs.
- **Twig entry points fixed** — `templates/cluster/list_item.html.twig` and
  `cluster/edit_item_action.html.twig` called `path('space_enterprise_admin_setup_docker_confirm',
  {'id': …})`, but the parameter is `{clusterId}`: the url could not generate at all. Now pass `clusterId`
  and pick admin vs user route with `is_granted('ROLE_ADMIN')`. This is what actually makes the flow
  reachable from the UI.
- **Behat** — `features/web.setup_docker.feature` (3 scenarios) and `features/api.setup_docker.feature`
  (4), plus the denial cases the stage-1 admin features were missing (non-admin → 403, anonymous → login
  page / 401). Action steps in `EnterpriseContext` now take an `:role`
  (`admin` | `user` | `anonymous`) selecting route family and credentials instead of existing once per
  audience — same idiom as core `ApiTrait::theApiIsCalledToEditAProject`.

**Verification**: `APP_ENV=test bin/console cache:warmup` OK; `debug:router` shows the 4 new routes;
Enterprise suite 20/20 scenarios (390 steps); `make qa` exits 0 (lint + phpstan max + phpcs + audit);
`make test` exits 0 (1161 PHPUnit tests / 3565 assertions, 47/47 Behat features).

## Missing Precision

- **A firewall-level denial and a recipe-level denial do not look alike, and the plan assumed they did.**
  A non-admin JWT on `/api/v1/admin/...` is refused by the new `access_control` rule *before any view
  runs*: the response is **`text/html`** with status 403, not the usual JSON envelope. So the core
  `an 403 error` step (which asserts `data.code`) cannot be used there — only `the user must have a 403
  error` (status only) works, and `get a JSON reponse` must be dropped from that scenario. A denial
  refused by the `AccountVoter`, in contrast, goes through the recipe's `RenderError` and *does* answer
  the JSON envelope, so the user-route scenario asserts `an 403 error` as usual. Both shapes are now
  documented in `extensions/Enterprise/documentation/SETUP_DOCKER.md`.
- **The missing-token 401 body is Lexik's**, `{"code": 401, "message": "JWT Token not found"}`, so
  `an 401 error about "JWT Token not found"` works. Confirmed by deliberately asserting a wrong message
  and reading the diff, rather than by guessing.
- **`submitValuesThroughAPI()` always attaches the JWT**, so an "unauthenticated API call" step cannot go
  through it, and `encodeAPIBody()` is private. Fine in practice: a request refused by the firewall never
  reaches form handling, so the step sends `{}` and asserts the status.

## Blockers

None. The recipe really was audience-agnostic, as the plan predicted, and the whole non-admin surface came
down to routes, two views and a service argument.

## Suggestions

- **`^/api/v1/admin` deserves a JSON access-denied handler.** Every other API failure in Space answers a
  JSON envelope; this one answers an HTML error page. A `Symfony\…\AccessDeniedHandlerInterface` on the
  `api_area` firewall (or an `api` check in the error controller) would make the API uniform. Out of scope
  here — the rule closing the hole was the point — but a client parsing JSON gets a surprise.
- **`phpcs` and Behat step phrases fight each other.** Two `#[When('…')]` attributes crossed 120 chars
  purely from the step wording and had to be wrapped over three lines. Worth knowing before inventing long
  step phrases.

## Lessons Learned

- **A route prefix is not a security boundary.** `^/admin` in `access_control` looked like it covered "the
  admin area", but `/api/v1/admin/...` never matched it. Anything mounted under a second prefix needs its
  own rule, and first-match-wins means it has to be declared *before* the broader `^/api` line.
- **A Twig `path()` call with a wrong parameter name is invisible until the block renders.** Both cluster
  templates had passed `{'id': …}` to a `{clusterId}` route since the feature was written, and neither
  PHPStan, phpcs nor `cache:warmup` say anything: only rendering the account-cluster list does. Behat
  coverage of the UI entry points, not just of the target routes, would have caught it.
- **Pin denials, not just happy paths.** The three new denial scenarios per surface are what proved the
  new `access_control` rule works, that the `AccountVoter` refuses another account, and that the anonymous
  bounce lands on the login form. They also caught the html-vs-JSON asymmetry above.
- **`:role` beats one step per audience.** Adding an `as :role` alias to a step and matching on the route
  family keeps the two API features readable and stops `EnterpriseContext` doubling in size.
