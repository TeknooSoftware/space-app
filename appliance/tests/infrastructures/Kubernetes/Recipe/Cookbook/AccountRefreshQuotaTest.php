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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Cookbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRefreshQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRegistryDeployment;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateStorage;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\UpdateCredentials;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;

/**
 * Class AccountRefreshQuotaTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRefreshQuota
 * @covers \Teknoo\Space\Recipe\Cookbook\Traits\PrepareAccountTrait
 */
class AccountRefreshQuotaTest extends TestCase
{
    private AccountRefreshQuota $accountRefreshQuota;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private PrepareRedirection|MockObject $prepareRedirection;

    private SetRedirectClientAtEnd|MockObject $redirectClient;

    private LoadHistory|MockObject $loadHistory;

    private LoadCredentials|MockObject $loadCredentials;

    private ReloadNamespace|MockObject $reloadNamespace;

    private CreateQuota|MockObject $createQuota;

    private UpdateAccountHistory|MockObject $updateAccountHistory;

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
        $this->loadCredentials = $this->createMock(LoadCredentials::class);
        $this->reloadNamespace = $this->createMock(ReloadNamespace::class);
        $this->createQuota = $this->createMock(CreateQuota::class);
        $this->updateAccountHistory = $this->createMock(UpdateAccountHistory::class);
        $this->errorHandler = $this->createMock(ReinstallAccountErrorHandler::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->accountRefreshQuota = new AccountRefreshQuota(
            $this->recipe,
            $this->loadObject,
            $this->prepareRedirection,
            $this->redirectClient,
            $this->loadHistory,
            $this->loadCredentials,
            $this->reloadNamespace,
            $this->createQuota,
            $this->updateAccountHistory,
            $this->errorHandler,
            $this->objectAccessControl,
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
            CookbookInterface::class,
            $this->accountRefreshQuota->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
