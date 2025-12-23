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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountRefreshQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateQuota;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ReloadEnvironement;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
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

    private LoadObject&Stub $loadObject;

    private PrepareRedirection&Stub $prepareRedirection;

    private SetRedirectClientAtEnd&Stub $redirectClient;

    private LoadHistory&Stub $loadHistory;

    private LoadEnvironments&Stub $loadEnvironments;

    private LoadAccountClusters&Stub $loadAccountClusters;

    private ReloadNamespace&Stub $reloadNamespace;

    private ReloadEnvironement&Stub $reloadEnvironement;

    private SelectClusterConfig&Stub $selectClusterConfig;

    private CreateQuota&Stub $createQuota;

    private UpdateAccountHistory&Stub $updateAccountHistory;

    private JumpIf&Stub $jumpIf;

    private Render&Stub $render;

    private ReinstallAccountErrorHandler&Stub $errorHandler;

    private ObjectAccessControlInterface&Stub $objectAccessControl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->loadObject = $this->createStub(LoadObject::class);
        $this->prepareRedirection = $this->createStub(PrepareRedirection::class);
        $this->redirectClient = $this->createStub(SetRedirectClientAtEnd::class);
        $this->loadHistory = $this->createStub(LoadHistory::class);
        $this->loadEnvironments = $this->createStub(LoadEnvironments::class);
        $this->reloadNamespace = $this->createStub(ReloadNamespace::class);
        $this->reloadEnvironement = $this->createStub(ReloadEnvironement::class);
        $this->loadAccountClusters = $this->createStub(LoadAccountClusters::class);
        $this->selectClusterConfig = $this->createStub(SelectClusterConfig::class);
        $this->createQuota = $this->createStub(CreateQuota::class);
        $this->updateAccountHistory = $this->createStub(UpdateAccountHistory::class);
        $this->jumpIf = $this->createStub(JumpIf::class);
        $this->render = $this->createStub(Render::class);
        $this->errorHandler = $this->createStub(ReinstallAccountErrorHandler::class);
        $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);
        $this->accountRefreshQuota = new AccountRefreshQuota(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            prepareRedirection: $this->prepareRedirection,
            redirectClient: $this->redirectClient,
            loadHistory: $this->loadHistory,
            loadEnvironments: $this->loadEnvironments,
            loadAccountClusters: $this->loadAccountClusters,
            reloadNamespace: $this->reloadNamespace,
            reloadEnvironement: $this->reloadEnvironement,
            selectClusterConfig: $this->selectClusterConfig,
            createQuota: $this->createQuota,
            updateAccountHistory: $this->updateAccountHistory,
            jumpIf: $this->jumpIf,
            render: $this->render,
            errorHandler: $this->errorHandler,
            objectAccessControl: $this->objectAccessControl,
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
