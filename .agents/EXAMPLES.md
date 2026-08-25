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

use Stringable;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\ClustersInfoInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\HealthInterface;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\Misc\ClusterAndEnvSelection;

class Dashboard implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly HealthInterface $health,
        private readonly LoadEnvironments $loadEnvironments,
        private readonly ClustersInfoInterface $clustersInfo,
        private readonly ClusterAndEnvSelection $clusterAndEnvSelection,
        private readonly Render $render,
        private readonly RenderError $renderError,
        private readonly string|Stringable $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->cook($this->health, HealthInterface::class, [], 10);
        $recipe = $recipe->cook($this->loadEnvironments, LoadEnvironments::class, [], 10);
        $recipe = $recipe->cook($this->clustersInfo, ClustersInfoInterface::class, [], 20);
        $recipe = $recipe->cook($this->clusterAndEnvSelection, ClusterAndEnvSelection::class, [], 30);
        $recipe = $recipe->cook($this->render, Render::class, [], 50);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);

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

use DomainException;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountEnvironmentLoader;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Query\AccountEnvironment\LoadFromAccountQuery;

class LoadEnvironments
{
    public function __construct(
        private readonly AccountEnvironmentLoader $loader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        Account|SpaceAccount|null $accountInstance = null,
        bool $allowEmptyCredentials = false
    ): self {
        if ($accountInstance instanceof SpaceAccount) {
            $accountInstance = $accountInstance->account;
        }

        if (true === $allowEmptyCredentials && null === $accountInstance) {
            return $this;
        }

        $errorCallback = fn (): ChefInterface => $manager->updateWorkPlan([
            AccountWallet::class => new AccountWallet([])
        ]);

        if (false === $allowEmptyCredentials) {
            $errorCallback = static fn (\Throwable $error): ChefInterface => $manager->error(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account_environment.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            );

            if (null === $accountInstance) {
                $errorCallback(new RuntimeException('teknoo.space.error.space_account.missing'));

                return $this;
            }
        }

        $fetchedPromise = new Promise(
            static function (iterable $credentials) use ($manager): void {
                $manager->updateWorkPlan([
                    AccountWallet::class => new AccountWallet($credentials),
                ]);
            },
            $errorCallback
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
