# Code Examples for Space

Concrete examples of Space patterns and conventions. Use as templates when writing new code.
See [../AGENTS.md](../AGENTS.md) for architecture context.

---

## Extension Example

### Extension Structure

```
extensions/MyExtension/
├── Extension.php           # Main extension class
├── Bundle/                 # Symfony bundle (optional)
├── config/                 # PHP-DI configuration files
├── routes/                 # Route definitions
├── Twig/                   # Twig templates
└── assets/                 # CSS, JS files
```

### Extension Class

```php
<?php

declare(strict_types=1);

namespace Teknoo\Space\Extensions\MyExtension;

use Teknoo\East\Foundation\Extension\ExtensionInterface;
use Teknoo\East\Foundation\Extension\ExtensionInitTrait;
use Teknoo\East\Foundation\Extension\ModuleInterface;
use Teknoo\East\FoundationBundle\Extension\Bundles;
use Teknoo\East\FoundationBundle\Extension\PHPDI;
use Teknoo\East\FoundationBundle\Extension\Routes;
use Teknoo\Space\Infrastructures\Twig\SpaceExtension\Twig;

class Extension implements ExtensionInterface
{
    use ExtensionInitTrait;

    private function configurePHPDI(PHPDI $phpdi): void
    {
        $phpdi->loadDefinition([
            ['file' => __DIR__ . '/config/di.php'],
        ]);
    }

    private function configureRoutes(Routes $routes): void
    {
        $routes->import(__DIR__ . '/routes/*.yaml');
    }

    private function injectTwigTemplates(Twig $twig): void
    {
        $twig->load(fn (?string $blockName): ?string => match ($blockName) {
            'space_left_menu' => '@MyExtension/menu/left.html.twig',
            default => null,
        });
    }

    public function executeFor(ModuleInterface $module): ExtensionInterface
    {
        match ($module::class) {
            Bundles::class => $module->register(MyBundle::class, ['all' => true]),
            PHPDI::class => $this->configurePHPDI($module),
            Routes::class => $this->configureRoutes($module),
            Twig::class => $this->injectTwigTemplates($module),
            default => null,
        };

        return $this;
    }

    public function __toString(): string
    {
        return 'My Extension';
    }
}
```

Register in `extensions/enabled.json`: `["Teknoo\\Space\\Extensions\\MyExtension\\Extension"]`

---

## Teknoo States Example

State pattern — objects change behavior based on internal state.

```php
<?php

declare(strict_types=1);

use Teknoo\States\State\AbstractState;

class English extends AbstractState
{
    public function sayHello(): \Closure
    {
        return function(): string {
            return 'Good morning, ' . $this->name;
        };
    }
}

class French extends AbstractState
{
    public function sayHello(): \Closure
    {
        return function(): string {
            return 'Bonjour, ' . $this->name;
        };
    }
}
```

```php
<?php

declare(strict_types=1);

use Teknoo\States\Attributes\Assertion\Property as PropertyAssertion;
use Teknoo\States\Attributes\StateClass;
use Teknoo\States\Automated\Assertion\Property\IsEqual;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

#[StateClass(English::class)]
#[StateClass(French::class)]
#[PropertyAssertion(English::class, ['country', IsEqual::class, 'en'])]
#[PropertyAssertion(French::class, ['country', IsEqual::class, 'fr'])]
class Person implements ProxyInterface, AutomatedInterface
{
    use ProxyTrait;
    use AutomatedTrait;

    private string $name;
    private string $country;

    public function __construct()
    {
        $this->initializeStateProxy();
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        $this->updateStates(); // triggers automatic state switching
        return $this;
    }
}
```

```php
$person = new Person();
$person->setName('John')->setCountry('en');
echo $person->sayHello(); // "Good morning, John"
$person->setCountry('fr');
echo $person->sayHello(); // "Bonjour, John"
```

---

## Recipe Plan Example

Plans orchestrate workflows by composing steps. Implement `EditablePlanInterface` so extensions
can modify them.

```php
<?php

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Plan;

use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\RecipeInterface;

class Dashboard implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly HealthInterface $health,
        private readonly LoadEnvironments $loadEnvironments,
        private readonly Render $render,
        private readonly RenderError $renderError,
        private readonly string $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->cook($this->health, HealthInterface::class, [], 10);        // priority 10
        $recipe = $recipe->cook($this->loadEnvironments, LoadEnvironments::class, [], 20); // priority 20
        $recipe = $recipe->cook($this->render, Render::class, [], 50);                // priority 50

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);

        return $recipe;
    }
}
```

- `cook($step, $class, $mapping, $priority)` — lower priority = earlier execution
- `onError()` — defines error handler
- `addToWorkplan()` — data available to all steps
- `EditablePlanTrait` — allows extensions to inject/modify steps

---

## Recipe Step Example

Steps are individual operations. Dependencies injected via constructor; workflow data via
`__invoke()` parameters matched by type from the workplan.

```php
<?php

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Step\AccountEnvironment;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountEnvironmentLoader;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Query\AccountEnvironment\LoadFromAccountQuery;

class LoadEnvironments
{
    public function __construct(
        private readonly AccountEnvironmentLoader $loader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ?Account $accountInstance = null,
    ): self {
        $fetchedPromise = new Promise(
            static function (iterable $environments) use ($manager): void {
                $manager->updateWorkPlan([
                    AccountWallet::class => new AccountWallet($environments),
                ]);
            },
            static fn (\Throwable $error) => $manager->error($error)
        );

        $this->loader->query(
            new LoadFromAccountQuery($accountInstance),
            $fetchedPromise,
        );

        return $this;
    }
}
```

- `__invoke()` params are resolved from the workplan by type
- `manager->updateWorkPlan()` — adds/updates data in workflow context
- `manager->error()` — signals failure up the chain
- `Promise` — handles async success/error callbacks
