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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Recipe\Step\Account\ExtractFromAccountDTO;

/**
 * Class ExtractFromAccountDTOTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ExtractFromAccountDTO::class)]
class ExtractFromAccountDTOTest extends TestCase
{
    private ExtractFromAccountDTO $extractFromAccountDTO;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->extractFromAccountDTO = new ExtractFromAccountDTO();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            ExtractFromAccountDTO::class,
            ($this->extractFromAccountDTO)(
                $this->createMock(ManagerInterface::class),
                new SpaceAccount($this->createMock(Account::class)),
            )
        );
    }

    public function testInvokeWithWorkPlanUpdate(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('namespaceIsItDefined')
            ->willReturnCallback(function ($callback) use ($account) {
                $this->assertIsCallable($callback);
                return $account;
            });

        $spaceAccount = new SpaceAccount($account);
        $spaceAccount->accountData = $this->createMock(AccountData::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->callback(function ($workplan) use ($account, $spaceAccount) {
                return isset($workplan[Account::class])
                    && $account === $workplan[Account::class]
                    && isset($workplan[AccountData::class])
                    && $spaceAccount->accountData === $workplan[AccountData::class];
            }));

        $this->assertInstanceOf(
            ExtractFromAccountDTO::class,
            ($this->extractFromAccountDTO)(
                manager: $manager,
                spaceAccount: $spaceAccount,
            )
        );
    }

    public function testInvokeWithNamespaceCallback(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('namespaceIsItDefined')
            ->willReturnCallback(function ($callback) use ($account) {
                // Execute the callback to test it
                $callback('test-namespace');
                return $account;
            });

        $spaceAccount = new SpaceAccount($account);
        $spaceAccount->accountData = $this->createMock(AccountData::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->exactly(2))
            ->method('updateWorkPlan')
            ->willReturnCallback(function ($workplan) use ($manager) {
                static $callCount = 0;
                $callCount++;

                if (1 === $callCount) {
                    // First call with Account and AccountData
                    $this->assertArrayHasKey(Account::class, $workplan);
                    $this->assertArrayHasKey(AccountData::class, $workplan);
                } elseif (2 === $callCount) {
                    // Second call from namespaceIsItDefined callback
                    $this->assertArrayHasKey('accountNamespace', $workplan);
                    $this->assertEquals('test-namespace', $workplan['accountNamespace']);
                }

                return $manager;
            });

        $this->assertInstanceOf(
            ExtractFromAccountDTO::class,
            ($this->extractFromAccountDTO)(
                manager: $manager,
                spaceAccount: $spaceAccount,
            )
        );
    }
}
