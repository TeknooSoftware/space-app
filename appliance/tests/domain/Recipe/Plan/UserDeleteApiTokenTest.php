<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Stringable;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\UserDeleteApiToken;
use Teknoo\Space\Recipe\Step\ApiKey\RemoveKey;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(UserDeleteApiToken::class)]
class UserDeleteApiTokenTest extends TestCase
{
    private UserDeleteApiToken $plan;

    private RecipeInterface&Stub $recipe;

    private RemoveKey&Stub $removeKey;

    private SaveObject&Stub $saveObject;

    private RedirectClientInterface&Stub $redirectClient;

    private RenderError&Stub $renderError;

    private string|Stringable $defaultErrorTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->removeKey = $this->createStub(RemoveKey::class);
        $this->saveObject = $this->createStub(SaveObject::class);
        $this->redirectClient = $this->createStub(RedirectClientInterface::class);
        $this->renderError = $this->createStub(RenderError::class);
        $this->defaultErrorTemplate = 'tpl';

        $this->plan = new UserDeleteApiToken(
            recipe: $this->recipe,
            removeKey: $this->removeKey,
            saveObject: $this->saveObject,
            redirectClient: $this->redirectClient,
            renderError: $this->renderError,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(UserDeleteApiToken::class, $this->plan);
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->plan->train($this->createStub(ChefInterface::class))
        );
    }
}
