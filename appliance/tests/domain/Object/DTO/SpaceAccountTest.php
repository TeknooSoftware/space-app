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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

/**
 * Class SpaceAccountTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceAccount::class)]
class SpaceAccountTest extends TestCase
{
    private SpaceAccount $spaceAccount;

    private Account&MockObject $account;

    private AccountData&MockObject $accountData;

    private iterable $variables;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->accountData = $this->createMock(AccountData::class);
        $this->variables = [];
        $this->spaceAccount = new SpaceAccount(
            account: $this->account,
            accountData: $this->accountData,
            variables: $this->variables
        );
    }

    public function testGetId(): void
    {
        $this->account
            ->method('getId')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            $this->spaceAccount->getId()
        );
    }

    public function testToString(): void
    {
        $this->account
            ->method('__toString')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            (string)$this->spaceAccount
        );
    }

    public function testConstructWithNullAccountData(): void
    {
        $account = $this->createMock(Account::class);

        $spaceAccount = new SpaceAccount(
            account: $account,
            accountData: null
        );

        $this->assertInstanceOf(AccountData::class, $spaceAccount->accountData);
    }

    public function testConstructWithProvidedAccountData(): void
    {
        $account = $this->createMock(Account::class);
        $accountData = $this->createMock(AccountData::class);

        $spaceAccount = new SpaceAccount(
            account: $account,
            accountData: $accountData
        );

        $this->assertSame($accountData, $spaceAccount->accountData);
    }

    public function testConstructWithVariablesAndEnvironments(): void
    {
        $account = $this->createMock(Account::class);
        $variable = $this->createMock(AccountPersistedVariable::class);
        $environment = new AccountEnvironmentResume('cluster1', 'env1');

        $spaceAccount = new SpaceAccount(
            account: $account,
            accountData: null,
            variables: [$variable],
            environments: [$environment]
        );

        $this->assertSame([$variable], iterator_to_array($spaceAccount->variables));
        $this->assertEquals([$environment], $spaceAccount->environments);
    }

    public function testExportToMeDataWithDefaultGroups(): void
    {
        $account = $this->createMock(Account::class);
        $accountData = $this->createMock(AccountData::class);

        $spaceAccount = new SpaceAccount(
            account: $account,
            accountData: $accountData
        );

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray());

        $result = $spaceAccount->exportToMeData($normalizer);

        $this->assertSame($spaceAccount, $result);
    }

    public function testExportToMeDataWithCustomGroups(): void
    {
        $account = $this->createMock(Account::class);
        $accountData = $this->createMock(AccountData::class);
        $variable = $this->createMock(AccountPersistedVariable::class);
        $environment = new AccountEnvironmentResume('cluster1', 'env1');

        $spaceAccount = new SpaceAccount(
            account: $account,
            accountData: $accountData,
            variables: [$variable],
            environments: [$environment]
        );

        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray());

        $result = $spaceAccount->exportToMeData($normalizer, ['groups' => ['crud']]);

        $this->assertSame($spaceAccount, $result);
    }
}
