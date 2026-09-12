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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountRefreshQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateQuota;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ReloadEnvironement;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;

/**
 * Class AccountRefreshQuotaTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountRefreshQuota::class)]
class AccountRefreshQuotaTest extends TestCase
{
    private AccountRefreshQuota $accountRefreshQuota;

    private RecipeInterface&Stub $recipe;

    private ReloadEnvironement&Stub $reloadEnvironement;

    private SelectClusterConfig&Stub $selectClusterConfig;

    private CreateQuota&Stub $createQuota;

    private ReinstallAccountErrorHandler&Stub $errorHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->reloadEnvironement = $this->createStub(ReloadEnvironement::class);
        $this->selectClusterConfig = $this->createStub(SelectClusterConfig::class);
        $this->createQuota = $this->createStub(CreateQuota::class);
        $this->errorHandler = $this->createStub(ReinstallAccountErrorHandler::class);

        $this->accountRefreshQuota = new AccountRefreshQuota(
            recipe: $this->recipe,
            reloadEnvironement: $this->reloadEnvironement,
            selectClusterConfig: $this->selectClusterConfig,
            createQuota: $this->createQuota,
            errorHandler: $this->errorHandler,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountRefreshQuota::class,
            $this->accountRefreshQuota,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountRefreshQuota->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
