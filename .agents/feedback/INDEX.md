# Feedback Index

Central knowledge base from AI agent sessions. **Read at every session start.**
After each task: create `YYYY-MM-DD-task-name.md` here, then add an entry below.

---

## Recent Feedback Entries

### 2026-09-03 — [API routes for the Enterprise "setup docker" flow](2026-09-03-enterprise-api-setup-docker.md)

**Status**: ✅ Resolved — three admin API routes (confirm / pending / get) + JSON views on the existing
Enterprise recipes; new `SetupDockerPending` plan with an Enterprise-topic instance of the core Mercure
waiter; `SetupDockerJob` made normalizable. New 6-scenario Behat feature. Two latent bugs found on the way:
a form-url-encoded API body must carry every form field (Symfony submits the whole form → 500 on the
non-nullable `masterAddress`), and the Behat bootstrap-runner double was only installed for the first
scenario of the run because the kernel is rebooted between scenarios. make qa + make test exit 0
(1161 tests, 45/45 features).

### 2026-08-26 — [Stage 2: setup docker on the task worker](2026-08-26-setup-docker-task-worker.md)

**Status**: ✅ Resolved — `SetupDockerDto`/`RunSetupDockerDto` + persisted `SetupDockerJob`; two-hop worker
flow (`PrepareInstallDockerHost` → `RunInstallDockerHost` → `InstallDockerHost`) with a history page;
doctrine mapping + messenger transport declared from the Enterprise bundle. Three latent bugs in the
rewritten files fixed (unautoloadable form-type namespace, two missing route defaults, a template extending
a non-existent layout). make qa + make test exit 0 (1165 tests, 44/44 features).

### 2026-08-24 — [Stage 1b: `CallNewJob` → `CallNewTask`](2026-08-24-call-new-task.md)

**Status**: ✅ Resolved — dispatch step generalized to `NewTaskInterface`; `projectId`/`projectName` gated
behind `instanceof NewJob` (throws when a job has no project); both API JSON templates hardened; moved to
`Step\Task`. make qa + make test exit 0 (1092 tests, 43/43 features), independently re-verified.

### 2026-08-21 — [Stage 1: generic "new task" worker](2026-08-21-new-task-worker.md)

**Status**: ✅ Resolved — `NewTaskInterface` + `NewTaskRecipeRegistry` added; `NewJobHandler`→
`NewTaskHandler` now resolves its plan from the registry; transport `new_job`→`new_task` routing the
interface; `newJobId`→`taskId` swept project-wide. make qa + make test exit 0, independently re-verified.

### 2026-08-17 — [Migrate Enterprise Bundle to Infrastructure/Symfony/Bundle](2026-08-17-migrate-enterprise-bundle.md)

**Status**: ✅ Resolved — 14 files moved, 3 PHP namespaces updated, services.yaml relative paths fixed,
Extension.php import updated, DOCKER_COMPOSE_CHANGELOG.md (15 refs) + development.md updated. 13 PHPUnit tests pass.

### 2026-07-01 — [Ansible + Docker Compose deployment](2026-07-01-ansible-docker.md)

**Status**: ✅ Resolved — 22-step pack complete; docker-compose target added, Kubernetes byte-for-byte
unchanged (full Behat exit 0). 🔄 A few DC-only follow-ups deferred + documented (registry/reinstall/refresh
dispatch, render-agnostic endpoint, out-of-band playbook run).

### 2026-02-02 — [Example Task](2026-02-02-example-task.md)

**Status**: ✅ Resolved — Created feedback system (index, templates, workflow integration).

---

## Common Patterns

### Documentation Gaps

- **Rename-tool blast radius**: a spec that says "rename class X → Y" must warn that PhpStorm's
  `rename_refactoring` also rewrites the identifier in strings/comments/translations/Markdown. Revert textual
  over-renames after, or use FQCN-anchored targeted edits. (2026-07-01)

- **phpcs 4.0.4 cannot tokenize property-hook bodies**: `{ get => $this->x; }` trips
  `PSR2.Classes.PropertyDeclaration.Multiple` + `.ScopeMissing` (it reads `$this` as a second property).
  Block form fails too; 4.0.4 is the latest release. Suppress those two sniffs narrowly. Abstract hooks
  (`{ get; }`) in interfaces are fine. (2026-08-21)
- **`sed 's/\$name/…/'` misses `$this->name`**: the `$` binds to `this`, so member names are unanchored.
  Sweep the bare identifier. (2026-08-21)
- **Teknoo Recipe resolves step params by name/type, never by position**:
  `BowlTrait::findParameterValueFromWorkplan` tries workplan key = param *name* → key = *type FQCN* →
  first workplan object `instanceof` the type (`ReflectionClass::isInstance`, so **interfaces work**) →
  default. Reordering a step's parameters is therefore safe; only direct calls in tests care. (2026-08-24)
- **A previous session's report is not evidence**: a stage-1 summary said a signature kept a concrete type
  that the user had since widened. Re-read the file and `git log` before planning on top of it. (2026-08-24)

### Recurring Blockers

- **Build-time-fixed structures vs per-request data**: `RecipeBowl` is readonly and fixed at container
  compile; per-request variation (e.g. cluster `type`) needs a thin `BowlInterface` resolving the concrete
  plan at `execute()` time. (2026-07-01)
- **`UnrecoverableMessageHandlingException` defeats `retry_strategy`**: a handler that wraps failures in
  it makes the transport's `max_retries` unreachable — the retry config becomes decorative. (2026-08-21)
- **No target host / no container-integration harness**: Ansible playbooks and driver `require()` can only be
  syntax-checked / warmup-checked here; functional runs are out-of-band. This project is Behat-only. (2026-07-01)

- **`ParametersBag` is write-only from a step** (`set()` only, read via `transform()`), and a missing key
  is an *undefined* Twig variable, not null — so a partially-populated bag crashes the template. (2026-08-24)
- **Behat doubles pushed with `$container->set()` must be re-pushed every scenario**: FriendsOfBehat's
  SymfonyExtension reboots the kernel between scenarios, so a "install it once for the run" static guard
  silently leaves every scenario after the first talking to the real service. (2026-09-03)
- **API form bodies are not symmetric**: `FormHandling` does a partial `submit($json, false)` for
  `application/json`, but defers to Symfony's request handler for form-url-encoded — which submits the whole
  form, so any field absent from the body is set to `null`. Non-nullable DTO properties then 500. (2026-09-03)
- **`createStub()` on an interface with PHP property hooks (`{ get; }`) is unproven** in this project; use
  an inline anonymous class with plain public properties instead. (2026-08-24)

### Frequently Missing Context

- **Two-repo layout**: `appliance/extensions/Enterprise/*` is git-ignored in space-app and mounts a separate
  repo (`space-app-enterprise`). Enterprise code commits there; plan-doc Findings commit to space-app. Check
  `git status` in **both** repos. (2026-07-01)
- **Field-name reuse for parity**: keeping `masterAddress` / reusing `client_key` + `ca_cert` let a new
  deployment target ride the existing persistence + deploy-identity path with no schema change. (2026-07-01)

---

## Legend

- ⚠️ Needs attention — not yet addressed
- ✅ Resolved — fixed, docs updated
- 📝 Documented — added to AGENTS.md or other docs
- 🔄 In Progress — currently being addressed

## Adding New Entries

1. Create `YYYY-MM-DD-task-name.md` in this directory
2. Add entry at top of "Recent Feedback Entries": `### YYYY-MM-DD — [Task Name](file.md) **Status**: icon`
3. Update "Common Patterns" if applicable

File sections: Task Summary · Missing Precision · Blockers · Suggestions · Lessons Learned.
See [AGENTS.md](../../AGENTS.md#task-management--feedback-loop) for full format.
