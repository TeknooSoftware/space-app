<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
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
 * @license https://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountRefreshQuota::class)]
class AccountRefreshQuotaTest extends TestCase
{
    private AccountRefreshQuota $accountRefreshQuota;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private PrepareRedirection|MockObject $prepareRedirection;

    private SetRedirectClientAtEnd|MockObject $redirectClient;

    private LoadHistory|MockObject $loadHistory;

    private LoadEnvironments|MockObject $loadEnvironments;

    private LoadAccountClusters|MockObject $loadAccountClusters;

    private ReloadNamespace|MockObject $reloadNamespace;

    private ReloadEnvironement|MockObject $reloadEnvironement;

    private SelectClusterConfig|MockObject $selectClusterConfig;

    private CreateQuota|MockObject $createQuota;

    private UpdateAccountHistory|MockObject $updateAccountHistory;

    private JumpIf|MockObject $jumpIf;

    private Render|MockObject $render;

    private ReinstallAccountErrorHandler|MockObject $errorHandler;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->prepareRedirection = $this->createMock(PrepareRedirection::class);
        $this->redirectClient = $this->createMock(SetRedirectClientAtEnd::class);
        $this->loadHistory = $this->createMock(LoadHistory::class);
        $this->loadEnvironments = $this->createMock(LoadEnvironments::class);
        $this->reloadNamespace = $this->createMock(ReloadNamespace::class);
        $this->reloadEnvironement = $this->createMock(ReloadEnvironement::class);
        $this->loadAccountClusters = $this->createMock(LoadAccountClusters::class);
        $this->selectClusterConfig = $this->createMock(SelectClusterConfig::class);
        $this->createQuota = $this->createMock(CreateQuota::class);
        $this->updateAccountHistory = $this->createMock(UpdateAccountHistory::class);
        $this->jumpIf = $this->createMock(JumpIf::class);
        $this->render = $this->createMock(Render::class);
        $this->errorHandler = $this->createMock(ReinstallAccountErrorHandler::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
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
        self::assertInstanceOf(
            AccountRefreshQuota::class,
            $this->accountRefreshQuota,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountRefreshQuota->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
