# Recipe System

Thin reference for the Recipe pattern. **See `documentation/` for full details.**

## Bowl Types

- **Bowl** — standard bowl wrapping a step with optional mapping data
- **ProvisioningPlanBowl** (`infrastructures/Recipe/Bowl/`) — resolves the correct account-provisioning plan
  at request time based on cluster `type` (kubernetes vs docker-compose). Required because a `RecipeBowl`'s
  recipe is fixed at container-build time.

→ `documentation/architecture.md#6-bowl-pattern-provisioningplanbowl`

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

- Plans: `config/di.recipe.plans.php` (51 plans)
- Steps: `config/di.recipe.steps.php` (56 steps, 17 categories)

Extensions register via their own `di.php` files loaded by the East Foundation extension system.

→ `documentation/architecture.md#4-php-di-configuration` · `documentation/infrastructure.md#php-di-config-files`

## Enterprise Extension Reference

Enterprise may add custom plans/steps (e.g. BigBang, Trivy audit). They follow the same `cook()`/`__invoke()`
conventions. See `documentation/architecture.md#extension-system`.
