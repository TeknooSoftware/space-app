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

namespace Teknoo\Space\Tests\Unit\Infrastructures\AnsibleDockerCompose\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Plan\AccountRegistryInstall;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Plan\AccountRegistryReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredential;

/**
 * Class AccountRegistryReinstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountRegistryReinstall::class)]
class AccountRegistryReinstallTest extends TestCase
{
    private AccountRegistryReinstall $accountRegistryReinstall;

    private RecipeInterface&Stub $recipe;

    private RemoveRegistryCredential&Stub $removeRegistryCredential;

    private AccountRegistryInstall&Stub $accountRegistryInstall;

    private ReinstallAccountErrorHandler&Stub $errorHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->removeRegistryCredential = $this->createStub(RemoveRegistryCredential::class);
        $this->accountRegistryInstall = $this->createStub(AccountRegistryInstall::class);
        $this->errorHandler = $this->createStub(ReinstallAccountErrorHandler::class);

        $this->accountRegistryReinstall = new AccountRegistryReinstall(
            recipe: $this->recipe,
            removeRegistryCredential: $this->removeRegistryCredential,
            accountRegistryInstall: $this->accountRegistryInstall,
            errorHandler: $this->errorHandler,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountRegistryReinstall::class,
            $this->accountRegistryReinstall,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountRegistryReinstall->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
