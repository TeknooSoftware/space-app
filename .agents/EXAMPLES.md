# Code Examples for Space

This file contains detailed code examples referenced from [AGENT.md](AGENT.md).

## Table of Contents

- [Extension Example](#extension-example)
- [Teknoo States Example](#teknoo-states-example)
- [Recipe Plan Example](#recipe-plan-example)
- [Recipe Step Example](#recipe-step-example)

---

## Extension Example

Extensions allow you to add functionality to Space without modifying core code.

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

### Extension Registration

Extensions are registered in `extensions/enabled.json`:

```json
[
    "Teknoo\\Space\\Extensions\\MyExtension\\Extension"
]
```

### Extension Class Implementation

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

---

## Teknoo States Example

The State pattern allows objects to change behavior based on their internal state.

### State Classes

```php
<?php

declare(strict_types=1);

use Teknoo\States\State\AbstractState;

// Define states as separate classes
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

### Main Class with Automated State Switching

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

// Main class with state switching based on properties
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
        $this->updateStates(); // Triggers automatic state switching
        return $this;
    }
}
```

### Usage

```php
$person = new Person();
$person->setName('John')->setCountry('en');
echo $person->sayHello(); // "Good morning, John"

$person->setCountry('fr');
echo $person->sayHello(); // "Bonjour, John"
```

---

## Recipe Plan Example

Plans orchestrate workflows by combining multiple steps. They implement `EditablePlanInterface` to allow extensions to
modify them.

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
        // Add steps with priority (lower = earlier execution)
        $recipe = $recipe->cook($this->health, HealthInterface::class, [], 10);
        $recipe = $recipe->cook($this->loadEnvironments, LoadEnvironments::class, [], 20);
        $recipe = $recipe->cook($this->render, Render::class, [], 50);

        // Error handler
        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        // Add data to workplan
        $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);

        return $recipe;
    }
}
```

### Key Concepts

- **`cook()`**: Adds a step to the recipe with a priority (lower = earlier)
- **`onError()`**: Defines error handling behavior
- **`addToWorkplan()`**: Adds data available to all steps
- **`EditablePlanTrait`**: Allows extensions to modify the plan

---

## Recipe Step Example

Steps are individual operations in a workflow. They receive dependencies via constructor injection and workflow data via
`__invoke()` parameters.

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
        // Create a promise to handle async result
        $fetchedPromise = new Promise(
            // Success callback
            static function (iterable $environments) use ($manager): void {
                $manager->updateWorkPlan([
                    AccountWallet::class => new AccountWallet($environments),
                ]);
            },
            // Error callback
            static fn (\Throwable $error) => $manager->error($error)
        );

        // Execute query with promise
        $this->loader->query(
            new LoadFromAccountQuery($accountInstance),
            $fetchedPromise,
        );

        return $this;
    }
}
```

### Key Concepts

- **Constructor Injection**: Dependencies (loaders, services) are injected via constructor
- **`__invoke()` Parameters**: Workflow data from the workplan is passed as parameters
- **`ManagerInterface`**: Used to update the workplan or signal errors
- **`Promise`**: Handles asynchronous operations with success/error callbacks
- **`updateWorkPlan()`**: Adds or updates data in the workflow context
