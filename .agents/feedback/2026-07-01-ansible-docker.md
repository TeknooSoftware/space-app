# Feedback: Ansible + Docker Compose deployment target — 2026-07-01

## Task Summary

Executed the 22-step `.plans/ANSIBLE_DOCKER/` pack: added a **docker-compose / Ansible** deployment target
alongside the existing Kubernetes one, without changing Kubernetes behaviour (byte-for-byte, proven by the
full Behat suite at every phase close).

- **Phase 0 (01–06):** `ConfigClusterInterface` + `UnsupportedClusterTypeException`; renamed
  `Config\Cluster` → `KubernetesCluster` (implements the interface, kept `masterAddress`); added
  `DockerComposeCluster`; widened `ClusterCatalog` to `ConfigClusterInterface`; guarded ~15 Kubernetes-only
  steps with `instanceof KubernetesCluster`; retyped downstream consumers + Behat traits.
- **Phase 1 (07–09):** loaded the East PaaS `DockerCompose/di.php`; mapped 13 `SPACE_DC_*` env vars →
  `teknoo.east.paas.docker-compose.*`; offered "Docker Compose" in the cluster form-type list.
- **Phase 2 (10–16):** `ProvisioningPlanDirectory` (per-role plan selector); type-aware catalog builder;
  `AccountCluster` SSH fields (`clientKey`/`username`, plaintext, reusing `caCertificate` as known_hosts) +
  Doctrine mapping; `AccountClusterType` form; docker-compose provisioning plans + `PersistSshIdentity`;
  runtime type-dispatch via a custom `ProvisioningPlanBowl`; confirmed deploy-time identity needed no code
  change.
- **Phase 3 (17–20, Enterprise repo):** `InstallDockerHost` plan + `BuildInventory`/`BuildExtraVars`/
  `RunBootstrap` steps (reuse East `RunnerFactoryInterface`/`RunnerInterface`); Ansible `bootstrap.yml` +
  Traefik templates (rootless, apt+dnf); `InstallDockerHostCommand` CLI; admin route + `RecipeEndPoint`.
- **Phase 4 (21–22):** documented `SPACE_DC_*` + the docker-compose catalog entry; this feedback closeout.

**Result:** 1063 unit tests green; PHPStan max 0; PSR-12 clean; `ansible-playbook --syntax-check` clean; full
Behat exit 0 (Kubernetes unchanged). Security constraints held throughout: SSH **key-only, rootless, no
password anywhere**; SSH key + known_hosts stored plaintext reusing existing `AccountEnvironment` fields (no
schema change, no new cipher); per-account OCI registry stays Kubernetes-only.

## Missing Precision

- [ ] **`rename_refactoring` blast radius (step-02):** the spec said "rename `Config\Cluster` →
  `KubernetesCluster`" but did not warn that PhpStorm's `rename_refactoring` also rewrites the identifier
  inside **string literals, comments, translations, and Markdown**. That silently corrupted `.env.test` /
  Behat trait cluster names (`"Demo Kube Cluster"` → `"Demo Kube KubernetesCluster"`) and broke the K8s
  regression. The spec should mandate: run the rename, then immediately revert textual over-renames (or use
  FQCN-anchored targeted edits).
- [ ] **step-15 dispatch mechanism:** the spec assumed a directory call `$directory->environmentInstall($type)`
  at the invocation site, but `RecipeBowl.recipe` is **readonly / fixed at container-build time** while cluster
  `type` is only known per-request. The real solution (a custom `BowlInterface` reading the workplan's cluster
  type at cook time) was not anticipated in the spec.
- [ ] **`NewJobSetDefaults:54`** was not in the pack's enumerated ~15 guard sites, but PHPStan flagged it once
  `ClusterCatalog` was widened. Resolved by **skipping** non-Kubernetes clusters (not throwing) so future
  docker-compose job creation isn't blocked — a judgement call the spec didn't cover.
- [x] **Config surface (§7)** was precise: the 13 `SPACE_DC_*` vars + defaults matched the code 1:1.

## Blockers

- **Two-repo layout (mid–Phase 3):** `appliance/extensions/Enterprise/` is a mount of a **separate git repo**
  (`space-app-enterprise`); `/extensions/*` is git-ignored in space-app. An early step-17 commit captured only
  the `.plans/` docs, not the Enterprise code. Resolved: Enterprise code commits to the enterprise repo (branch
  `feature/ansible-docker-compose`); plan-doc Findings commit to space-app. Also backfilled the step-05
  Enterprise guard commit there (it had never been committed).
- **No target host / no container-integration harness:** step-18's playbook could only be validated with
  `ansible-playbook --syntax-check` + review — the functional apt/dnf/idempotency run (spec §9 scenario 5) is
  **out-of-band**. Likewise the docker-compose driver's runtime `require()` (step-07 §9 scenario 2) is proven
  at compile time by `make warmup`; this project has Behat only, no PHPUnit container-integration tests.

## Suggestions

- ✅ For any future class rename, treat `rename_refactoring` as textual and diff+revert non-code hits (saved as
  a durable lesson).
- ✅ When a build-time-fixed structure (`RecipeBowl`) must vary per request, wrap it in a thin `BowlInterface`
  that resolves the concrete plan at `execute()` time — keeps the Kubernetes path byte-identical (directory
  returns the same instance).
- ✅ **docker-compose end-to-end Behat coverage — FULL PARITY (done 2026-07-02):**
  `features/api.job.start.in-docker-compose.feature` (55 scenarios) + `api.admin.job.start.in-docker-compose.feature`
  (41) — a 1:1 docker-compose twin of **every** scenario in the two Kubernetes job-start features (SUCCESS →
  compose+traefik goldens + no-K8s; ERROR/403 → same failure + no compose). Modelled on the East PaaS
  docker-compose Behat test: a fake `RunnerFactory`/`Runner` + in-memory Flysystem drive the real
  `DockerCompose\Driver`. New `tests/Behat/Traits/DockerComposeTrait.php` with a **variant-aware golden**
  system (`composeVariantKey()` = paas-file × prefix-value × quota-mode → `tests/Behat/expected/compose/<key>/`,
  17 variants; encryption/K8s-version/ingress-annotations/HNC collapse). A null-default cluster override in
  `PersistenceStepsTrait` (both the no-prefix and prefixed project paths) keeps K8s scenarios byte-for-byte;
  a `docker-compose` entry was added to the test `SPACE_CLUSTER_CATALOG_JSON` (deploy resolves the target
  cluster by name from the catalog), plus a docker-compose `AccountCluster` Given for the per-account-cluster
  twins. Twins were generated by scripting the source features (swap cluster steps + final assertion only),
  guaranteeing identical scenario count/setup.
  **Lesson:** golden-variant keys must include every axis that changes output — an initial boolean `-prefixed`
  key collided `a-prefix` vs `demo` projects (18 mismatches); keying on the prefix *value* fixed it.
- 🐛 **Bug the new test exposed + fixed:** with a docker-compose cluster in the catalog, the **dashboard** 500'd
  for every user — `Infrastructures/Kubernetes/Recipe/Step/Misc/Health.php` iterates the *whole* catalog and
  **threw** `UnsupportedClusterTypeException` on the non-K8s cluster. step-05 applied throw-guards uniformly, but
  a catalog-iterating **display** step must **skip** non-K8s clusters (like `NewJobSetDefaults`). Fixed
  (throw → `continue`); K8s dashboards unchanged. Lesson: distinguish *operate-on-selected-cluster* guards
  (throw) from *iterate-all-for-display* guards (skip).
- ⚠️ **Follow-ups deferred (documented, not done):**
  - DC-only-account **registry-install / reinstall / refresh-quota** admin-endpoint dispatch — those resolve
    the cluster without a per-request `clusterName`, so naive type-dispatch would break K8s account creation.
    Only environment-install (§9.3) + the registry no-op are wired.
  - `InstallDockerHost` is **render-agnostic** (no `Render` step, shared by CLI + endpoint) → the admin
    endpoint triggers the bootstrap but renders no HTML result page.
  - Enterprise K8s guard branches (`CreateClient`/`CreateRole`/`CreateRoleBinding`) are **uncovered** — no
    Enterprise unit-test suites exist (out of scope to create them here).

## Lessons Learned

- **`rename_refactoring` renames strings/comments/docs too**, not just code identifiers — the single biggest
  time sink this pack. Broke the K8s Behat regression via `appliance/.env.test` and
  `tests/Behat/Traits/PersistenceStepsTrait.php` cluster names.
- **`RecipeBowl` is readonly and fixed at container-compile** (`config/di.recipe.plans.php:451`); per-request
  variation needs a custom `ProvisioningPlanBowl` (`infrastructures/Recipe/Bowl/ProvisioningPlanBowl.php`) that
  reads `$workPlan['clusterCatalog']->getCluster($workPlan['clusterName'])->type` at cook time. Kubernetes
  resolves to the same plan instance → byte-identical loop.
- **Keeping Kubernetes byte-for-byte** was achievable by making every change additive/optional: e.g.
  `PersistEnvironment` gained an optional `#[SensitiveParameter] string $clientKey = ''` (K8s passes nothing →
  default `''` → identical `AccountEnvironment`).
- **The `masterAddress` field name matters** — reusing it (not renaming to `address`) let deploy-time identity
  (`AddManagedEnvironmentToProject` / `UpdateProjectCredentialsFromAccount`) work for both targets with **no
  code change**; SSH just puts `user@host:port` in the same field.
- **Two-repo discipline:** commit Enterprise code in `space-app-enterprise`, plan docs in `space-app`. Verify
  with `git status` in *both* repos before assuming a step is committed.
