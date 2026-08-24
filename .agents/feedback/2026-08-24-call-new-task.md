# 2026-08-24 — Stage 1b: `CallNewJob` → `CallNewTask`

## Task Summary

Generalized the *dispatch* side of the task pipeline, completing what stage 1
([2026-08-21](2026-08-21-new-task-worker.md), committed as `6ac8ee1`) did for the worker side.

- **Moved + reworked the contract** — `domain/Contracts/Recipe/Step/Job/CallNewJobInterface.php` →
  `domain/Contracts/Recipe/Step/Task/CallNewTaskInterface.php`, matching where `NewTaskNotifierInterface`
  already lives.
- **Moved + reworked the step** — `infrastructures/Symfony/Recipe/Step/Job/CallNewJob.php` →
  `.../Recipe/Step/Task/CallNewTask.php`. New signature:
  `__invoke(ManagerInterface $manager, NewTaskInterface $newTask, ParametersBag $parametersBag, ?SpaceProject $project = null): CallNewTaskInterface`.
  The dispatch closure and the `Promise` generic retype from `NewJob` to `NewTaskInterface`.
- **Gated the project-specific output** — `taskId` + `accountId` go on the `ParametersBag`
  unconditionally (both are on `NewTaskInterface`); `projectId` on the bag and `projectId` +
  `projectName` in `routeParameters` sit inside `if ($newTask instanceof NewJob)`, which throws
  `RuntimeException('teknoo.space.error.call_new_task.missing_project')` if the project is absent.
  `routeParameters` always carries `taskId`. This mirrors the idiom the user had already introduced in
  `NewTaskNotifier`.
- **Hardened the two API JSON templates** — `templates/TeknooSpace/api/Job/new.json.twig` and
  `.../api/AdminJob/new.json.twig` referenced `projectId`/`accountId` *unguarded* inside an
  `{% if taskId is defined %}` branch. They now emit `task_queue_id` either way and build `meta.url`
  only when the identifiers are present.
- **Wiring** — `JobStart` (property `$callNewTask`, still cooked at priority 80 with the empty mapping),
  both `config/services.yaml` blocks, `config/di.recipe.plans.php` (still the 15th `JobStart` arg),
  `tests/domain/Recipe/Plan/JobStartTest.php`, `documentation/domain.md`.

**Verified**: `make qa` exit 0, `make test` exit 0 (1092 unit tests / 3262 assertions, 43/43 Behat
features). `cache:warmup` OK in dev + test + prod, `lint:container` OK, and `debug:container` confirms
the interface alias resolves to `…\Step\Task\CallNewTask`. Independently re-verified by a fresh subagent
(10/10 checks, zero discrepancies).

## Missing Precision

- **The spec's "become" snippet still returned `CallNewJobInterface`** while the instruction said to
  rename to `CallNewTaskInterface` — a typo, confirmed with the user.
- **Only `projectId` was named for gating**, but `projectName` (also in `routeParameters`) derives from
  the same `$project` and cannot survive a null. Both had to move inside the gate.
- **"Rework related behat tests" had no target** — no `.feature` file or Behat context references
  `CallNewJob`; the step is exercised black-box. Confirmed with the user that keeping the existing 43
  features green was the intent.

## Blockers

None. The two risks flagged during planning were both resolved by reading source rather than guessing:

- **Does Recipe resolve an interface-typed step parameter?** Yes. `BowlTrait::findParameterValueFromWorkplan`
  (`vendor/teknoo/recipe/src/Bowl/BowlTrait.php:334-378`) resolves in order: workplan key matching the
  *parameter name* → workplan key matching the *type FQCN* → first workplan object `instanceof` the
  parameter type → default. `isInstanceOf()` uses `ReflectionClass::isInstance`, so interfaces match.
- **Is reordering the parameters safe?** Yes — resolution is per-parameter by name/type, never by
  position. Only direct calls (tests) had to change.

## Suggestions

- `documentation/domain.md` still described these contracts in job-only terms ("Create new deployment
  job", "Notify about new jobs"); refreshed.
- `domain/Contracts/Recipe/Step/Task/NewTaskNotifierInterface.php:30` carries a now-unused
  `use …\DTO\NewJob;` left over from the notifier widening. Harmless (phpcs/PHPStan pass); left alone to
  keep this diff scoped.
- The old `Step/Job/` directories still hold `FetchJobIdFromPendingInterface`, `JobErrorNotifier`,
  `JobUpdaterNotifier`, `PersistJobVar` — genuinely job-specific, correctly left behind. Worth a note in
  AGENTS.md that `Step/Task/` means "task-generic" and `Step/Job/` means "job-only", since the split is
  now load-bearing.

## Lessons Learned

- **Prior sessions' reports go stale — re-read the file.** My stage-1 report said
  `NewTaskNotifierInterface` kept a concrete `NewJob` parameter. By this session the user had widened it
  to `NewTaskInterface` with an `instanceof NewJob` gate and a `spaceDashboardRoute` fallback. An
  exploring subagent caught the contradiction; verifying against the file (and `git log`) turned a wrong
  assumption into the exact precedent to copy. Never plan from a previous turn's summary.
- **The strongest regression tripwire was in Behat, not in the unit test.**
  `ApiTrait::aPendingJobId` (`tests/Behat/Traits/ApiTrait.php:1212-1244`) regenerates the pending URL
  *including* `projectId` and asserts equality with `meta.url`, across 12 API features — so dropping
  `projectId` fails loudly. The 3 `web.job.start.*` features only assert the `/job/pending/` prefix and
  would have sailed through. When gating a value, find out *which* test actually pins it before assuming
  coverage.
- **`ParametersBag` is write-only from a step** (`set()` only; read via `transform()`), and a missing key
  is an **undefined** Twig variable, not null. That is why a partially-populated bag is a template crash
  rather than a silent blank — and why the guards were worth adding.
- **Don't `createStub()` an interface with property hooks.** `NewTaskInterface` declares
  `public string $taskId { get; }`; PHPUnit's doubler support for hooks is unproven here. An inline
  anonymous class with plain public properties satisfies `{ get; }` and is deterministic.
- **Assert on collaborators, not just the return type.** The pre-existing `CallNewJobTest` asserted only
  `assertInstanceOf` — it pinned neither the bag nor the workplan, and never exercised dispatch at all
  (its `EncryptionInterface` stub makes `encrypt()` a no-op, so the closure never fired). A test that
  cannot fail when the logic changes is not coverage. Went from 1 case to 5.
- The stage-1 sed lesson held again: sweep the **bare** member name (`callNewJob`), since
  `s/\$callNewJob/…/` never matches `$this->callNewJob`.
