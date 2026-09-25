# Recipe System

Thin reference for the Recipe pattern. **See `documentation/` for full details.**

## Bowl Types

- **Bowl** — standard bowl wrapping a step with optional mapping data
- **ProvisioningPlanBowl** (`infrastructures/Recipe/Bowl/`) — resolves the correct account-provisioning plan
  at run time based on cluster `type` (kubernetes vs docker-compose). Required because a `RecipeBowl`'s
  recipe is fixed at container-build time. Executed in the `new_task` worker by
  `Recipe/Plan/Task/AccountProvisioningTask`, never in the web request; the web side only queues a
  `Object/DTO/Task/*` task with `CallNewTask`. Environment removal follows the same rule (`DeleteEnvironmentsTask` →
  `Recipe/Plan/Task/AccountEnvironmentsDeletionTask`, `DeleteNamespaces` step).

→ `documentation/architecture.md#6-bowl-pattern--provisioningplanbowl`

## Creating a Plan

Plans orchestrate workflows by composing steps. Implement `EditablePlanInterface` so extensions can modify
them at container-build time.

1. **Constructor** — inject step instances via constructor property promotion
2. **`populateRecipe(RecipeInterface $recipe)`** — call `$recipe->cook($step, $class, $mapping, $priority)`
   for each step. Lower priority = earlier execution.
3. **`onError($bowl)`** — define error handler
4. **`addToWorkplan($key, $value)`** — data available to all steps
5. Use `EditablePlanTrait` for extension support

See the full Plan example in `.agents/EXAMPLES.md#recipe-plan-example`.

## Creating a Step

Steps are individual operations. Dependencies via constructor; workflow data via `__invoke()` parameters
resolved from the workplan by type (not position).

1. **`__invoke()`** — params matched by type FQCN from the workplan
2. **`manager->updateWorkPlan([...])`** — add/update data in workflow context
3. **`manager->error($exception)`** — signal failure up the chain
4. **`Promise`** — handles async success/error callbacks

See the full Step example in `.agents/EXAMPLES.md#recipe-step-example`.

## Registration

- Plans: `config/di.recipe.plans.php`
- Steps: `config/di.recipe.steps.php`

Neither file is a complete inventory of its own kind: `di.recipe.plans.php` also registers steps that only one
plan uses — `ApiKey\RemoveKey` is there, which is why the step categories imported by `di.recipe.steps.php` are
one fewer than the subdirectories of `domain/Recipe/Step/`. When looking for where a step is wired, grep both.

Extensions register via their own `di.php` files loaded by the East Foundation extension system.

→ `documentation/architecture.md#4-php-di-configuration` · `documentation/infrastructure.md#php-di-config-files`

## Extending a core plan

An extension does not have to write a plan from scratch. The two established ways in:

- **Decorate the plan service** in PHP-DI and append steps to it, leaving the core definition untouched.
- **Extend a core end-point plan** — `ListObjectEndPoint`, `DeleteObjectEndPoint` and their siblings can be
  subclassed, the subclass's `cook()` calls adding to an inherited recipe instead of building one.

Same-position steps keep their insertion order, and a step may `continue()` to a later uniquely-named step.
A second `StartLoopingOn` loop in one recipe is impossible, because steps are named by class.

**Steps are singletons shared by every execution of a worker.** Never keep per-run state in a step property —
store it in the workplan instead (that is what `WalletCursor` is for).

See `documentation/architecture.md#extension-system`.
