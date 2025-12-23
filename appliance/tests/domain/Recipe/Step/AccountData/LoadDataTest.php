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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountData;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Loader\AccountDataLoader;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Recipe\Step\AccountData\LoadData;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class LoadDataTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadData::class)]
class LoadDataTest extends TestCase
{
    private LoadData $loadData;

    private AccountDataLoader&MockObject $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(AccountDataLoader::class);
        $this->loadData = new LoadData($this->loader);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            LoadData::class,
            ($this->loadData)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(Account::class),
                true,
            )
        );
    }

    public function testInvokeWithSuccessCallback(): void
    {
        $accountData = $this->createStub(AccountData::class);

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($accountData) {
                $promise->success($accountData);
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($accountData) {
                    return isset($workplan[AccountData::class])
                        && $workplan[AccountData::class] === $accountData;
                })
            );

        $this->assertInstanceOf(
            LoadData::class,
            ($this->loadData)(
                manager: $manager,
                accountInstance: $this->createStub(Account::class),
                allowEmptyDatas: true,
            )
        );
    }

    public function testInvokeWithErrorAndAllowEmptyDatasFalse(): void
    {
        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) {
                $promise->fail(new \Exception('Test error', 500));
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof \DomainException
                        && 'teknoo.space.error.space_account.account_data.fetching' === $exception->getMessage()
                        && 500 === $exception->getCode();
                })
            );

        $this->assertInstanceOf(
            LoadData::class,
            ($this->loadData)(
                manager: $manager,
                accountInstance: $this->createStub(Account::class),
                allowEmptyDatas: false,
            )
        );
    }

    public function testInvokeWithErrorCodeZeroAndAllowEmptyDatasFalse(): void
    {
        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) {
                $promise->fail(new \Exception('Test error', 0));
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof \DomainException
                        && 404 === $exception->getCode();
                })
            );

        $this->assertInstanceOf(
            LoadData::class,
            ($this->loadData)(
                manager: $manager,
                accountInstance: $this->createStub(Account::class),
                allowEmptyDatas: false,
            )
        );
    }

    public function testInvokeWithErrorAndAllowEmptyDatasTrue(): void
    {
        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) {
                $promise->fail(new \Exception('Test error', 500));
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('error');

        $this->assertInstanceOf(
            LoadData::class,
            ($this->loadData)(
                manager: $manager,
                accountInstance: $this->createStub(Account::class),
                allowEmptyDatas: true,
            )
        );
    }
}
