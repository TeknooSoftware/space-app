# 2026-08-21 — Stage 1: generic "new task" worker

## Task Summary

Generalized the hard-wired "new job" async path into a task-agnostic one, so that stage 2 (running the
Enterprise Docker-host installer in a worker instead of the web tier) has a core to build on.

- **New contract** `Teknoo\Space\Contracts\DTO\NewTaskInterface` (`domain/Contracts/DTO/`) — extends
  `Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface`; declares get-only property hooks
  `taskId`, `accountId`, `variables` plus `export()`, `getMessage()`, `toArray()`.
- **New service** `Teknoo\Space\Service\NewTaskRecipeRegistry` (`domain/Service/`) — `register()` /
  `get()` over a `class-string<NewTaskInterface> => BaseRecipeInterface` map, `DomainException` on miss.
  Registered in `config/di.recipe.plans.php`, mapping `NewJob` → `NewJobInterface` plan.
- **`NewJob` DTO** — `newJobId` became a promoted `taskId`; `newJobId` survives as a get-only virtual
  property hook for BC; gained `toArray()` (projectId, accountId, envName, taskId, extra.task_id).
- **Renames** — `NewJobHandler`→`NewTaskHandler` (drops the injected `NewJobInterface $recipe`, resolves
  the plan from the registry), `JobErrorPublisher`→`TaskErrorPublisher`,
  `Recipe\Step\Job\NewJobNotifier`→`Recipe\Step\Task\NewTaskNotifier` (+ its contract into
  `Contracts\Recipe\Step\Task`).
- **Transport** `new_job`→`new_task`, now routing the **interface**
  (`Teknoo\Space\Contracts\DTO\NewTaskInterface: 'new_task'`), so future task DTOs need no config.
- **Identifier sweep** `newJobId`→`taskId`, `new_job_id`→`task_id`, `pendingJobRoute`→`pendingTaskRoute`,
  env `MESSENGER_NEW_JOB_DSN`→`MESSENGER_NEW_TASK_DSN`, `SPACE_NEW_JOB_WAITING_TIME`→
  `SPACE_NEW_TASK_WAITING_TIME`, incl. 3 route path placeholders, the form field, 5 Twig templates,
  3 feature files, compose stacks (`cli_new_job`→`cli_new_task`), `bin/config.sh`, and all docs.

**Verified**: `make qa` exit 0 (lint + PHPStan max + phpcs PSR-12 + audit), `make test` exit 0
(1088 unit tests / 3251 assertions, 43/43 Behat features). Re-verified by an independent subagent.

## Missing Precision

- **`JobErrorPublisher` namespace was wrong in the spec** — stated as
  `Teknoo\Space\Infrastructures\Symfony\Messenger`, actually lives in `…\Symfony\Mercure`.
  Renamed in place.
- **The spec's `extra` handling was self-contradictory** — it showed the handler appending
  `'extra' => ['task_id' => …]` while also saying "the extra field must now be included into export in
  NewJob". Resolved with the user: `toArray()` may carry `extra`, and the handler merges over it.
- **"Fix messenger to allow 3 retry only for new task"** — `max_retries: 3` was already in place; the
  user confirmed the bullet was an error. See Lessons for the real latent issue.
- **The minimal interface excludes `projectId`/`envName`** (user's explicit choice), which silently
  forbids widening every `NewJob`-typed signature. See Lessons.

## Blockers

- **phpcs 4.0.4 cannot tokenize property-hook bodies.** `public string $newJobId { get => $this->taskId; }`
  raises `PSR2.Classes.PropertyDeclaration.Multiple` + `.ScopeMissing` — the sniff reads `$this` as a
  second property declaration. Block form (`get { return …; }`) fails identically. 4.0.4 is the latest
  release, so no upgrade path. Resolved with a two-sniff `phpcs:disable`/`enable` pair and an inline
  comment explaining why. **Abstract hooks in interfaces (`{ get; }`) are unaffected** — that is why the
  pre-existing `domain/Object/Config/ConfigClusterInterface.php` always passed.
- **`sed 's/\$newJobNotifier/…/'` misses `$this->newJobNotifier`** — the `$` belongs to `this`, so the
  member name is unanchored. Cost one red PHPUnit run (2 errors + 3 "dynamic property" deprecations,
  which `failOnDeprecation` turns fatal). Sweep bare member names, not `$`-prefixed ones.

## Suggestions

- `documentation/worker.md` had a pre-existing typo `NewJobN` in the message-flow diagram (fixed).
- `phpstan.neon` is gitignored and only `includes: phpstan.baseline.neon`, whose `paths:` omit `tests/`.
  Test code is therefore checked by phpcs only, never by PHPStan — worth documenting in AGENTS.md, since
  "PHPStan at max level" reads as covering everything.
- Two translation keys thrown by `NewTaskNotifier` — `teknoo.space.error.new_job.mercure_unavailable`
  and `teknoo.space.error.new_job.error` — have no entry in `translations/messages.en.yaml`.
  Pre-existing (confirmed against `HEAD`), not a regression.
- The harness-supplied `gitStatus` snapshot at session start reported the tree clean when it was not;
  re-run `git status` rather than trusting it.

## Lessons Learned

- **A "minimal" DTO contract constrains far more than the DTO.** Excluding `projectId`/`envName` from
  `NewTaskInterface` means `NewTaskNotifier`/`NewTaskNotifierInterface` must keep the concrete `NewJob`
  parameter type — its degraded-route branch reads `$newJob->projectId`. Widening would have forced
  either an interface change or untyped `toArray()` lookups. Decide the contract's field set *before*
  planning which signatures widen.
- **`UnrecoverableMessageHandlingException` silently defeats `retry_strategy`.** `NewTaskHandler` wraps
  every failure in it, so the transport's `max_retries: 3` can never fire. Left as-is per the user's
  decision, but it means the retry config on `new_task` is currently decorative.
- **Routing a messenger *interface* is the cheap extension point.** `HandlersLocator` walks parents and
  interfaces, so `NewTaskInterface: 'new_task'` plus `__invoke(NewTaskInterface $task)` makes every
  future task DTO auto-route with zero config. Confirmed with `debug:messenger`.
- **Grep counts are per-token, not per-concept.** An early inventory reported "~1400 rows" for the form
  field; that was the count of `new_job` (all field rows), while `newJobId` was only 119 occurrences.
  Count the exact identifier before sizing a sweep.
- **`cache:warmup` in both `dev` and `test` is the cheapest check for DI/YAML damage** — it catches
  service-id and messenger-routing breakage long before Behat does.
