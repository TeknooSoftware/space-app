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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;

/**
 * Class LoadAccountFromProjectTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadAccountFromProject::class)]
class LoadAccountFromProjectTest extends TestCase
{
    private LoadAccountFromProject $loadAccountFromProject;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->loadAccountFromProject = new LoadAccountFromProject();
    }

    public function testInvoke(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->isArray());

        $this->assertInstanceOf(
            LoadAccountFromProject::class,
            ($this->loadAccountFromProject)(
                new SpaceProject($this->createStub(Project::class)),
                $manager,
                $this->createStub(Account::class),
            )
        );
    }

    public function testInvokeWithProject(): void
    {
        $account = $this->createStub(Account::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) use ($account) {
                    return isset($data[Account::class])
                        && $data[Account::class] === $account
                        && isset($data['parameters'])
                        && is_array($data['parameters']);
                })
            );

        $this->assertInstanceOf(
            LoadAccountFromProject::class,
            ($this->loadAccountFromProject)(
                $this->createStub(Project::class),
                $manager,
                $account,
            )
        );
    }

    public function testInvokeWithAccountFromProject(): void
    {
        $account = $this->createStub(Account::class);

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('getAccount')
            ->willReturn($account);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) use ($account) {
                    return $data[Account::class] === $account;
                })
            );

        $this->assertInstanceOf(
            LoadAccountFromProject::class,
            ($this->loadAccountFromProject)(
                new SpaceProject($project),
                $manager,
                null,
            )
        );
    }

    public function testInvokeWithAccountIdMatching(): void
    {
        $accountId = 'account-123';

        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('getId')
            ->willReturn($accountId);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) use ($account, $accountId) {
                    return $data[Account::class] === $account
                        && isset($data['parameters']['accountId'])
                        && $data['parameters']['accountId'] === $accountId;
                })
            );

        $this->assertInstanceOf(
            LoadAccountFromProject::class,
            ($this->loadAccountFromProject)(
                $this->createStub(Project::class),
                $manager,
                $account,
                $accountId,
            )
        );
    }

    public function testInvokeWithAccountIdMismatch(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('getId')
            ->willReturn('account-123');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('teknoo.space.error.space_account.account.fetching');
        $this->expectExceptionCode(404);

        ($this->loadAccountFromProject)(
            $this->createStub(Project::class),
            $this->createStub(ManagerInterface::class),
            $account,
            'account-456',
        );
    }

    public function testInvokeWithExistingParameters(): void
    {
        $accountId = 'account-123';
        $existingParams = ['foo' => 'bar', 'baz' => 'qux'];

        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('getId')
            ->willReturn($accountId);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) use ($account, $accountId, $existingParams) {
                    return $data[Account::class] === $account
                        && $data['parameters']['accountId'] === $accountId
                        && 'bar' === $data['parameters']['foo']
                        && 'qux' === $data['parameters']['baz'];
                })
            );

        $this->assertInstanceOf(
            LoadAccountFromProject::class,
            ($this->loadAccountFromProject)(
                $this->createStub(Project::class),
                $manager,
                $account,
                $accountId,
                $existingParams,
            )
        );
    }

    public function testInvokeWithSpaceProjectAndAccountIdMatching(): void
    {
        $accountId = 'account-123';

        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('getId')
            ->willReturn($accountId);

        $project = $this->createStub(Project::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) use ($account, $accountId) {
                    return $data[Account::class] === $account
                        && isset($data['parameters']['accountId'])
                        && $data['parameters']['accountId'] === $accountId;
                })
            );

        $this->assertInstanceOf(
            LoadAccountFromProject::class,
            ($this->loadAccountFromProject)(
                new SpaceProject($project),
                $manager,
                $account,
                $accountId,
            )
        );
    }
}
