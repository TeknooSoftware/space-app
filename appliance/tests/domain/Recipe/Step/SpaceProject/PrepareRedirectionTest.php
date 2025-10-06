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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\SpaceProject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection;

use function array_key_exists;

/**
 * Class PrepareRedirectionTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PrepareRedirection::class)]
class PrepareRedirectionTest extends TestCase
{
    private PrepareRedirection $prepareRedirection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->prepareRedirection = new PrepareRedirection();
    }

    public function testInvoke(): void
    {
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('project-123');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return 'project-123' === $data['id']
                        && isset($data['parameters'])
                        && 'project-123' === $data['parameters']['id']
                        && null === $data['parameters']['objectSaved']
                        && !isset($data['parameters']['accountId']);
                })
            );

        $this->assertInstanceOf(
            PrepareRedirection::class,
            ($this->prepareRedirection)(
                $manager,
                $spaceProject,
                'foo'
            ),
        );
    }

    public function testInvokeWithAccountId(): void
    {
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('project-123');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return isset($data['parameters']['accountId'])
                        && 'account-456' === $data['parameters']['accountId'];
                })
            );

        $this->assertInstanceOf(
            PrepareRedirection::class,
            ($this->prepareRedirection)(
                $manager,
                $spaceProject,
                'foo',
                null,
                [],
                'account-456'
            ),
        );
    }

    public function testInvokeWithAdminRoute(): void
    {
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('project-123');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return array_key_exists('accountId', $data['parameters'])
                        && null === $data['parameters']['accountId'];
                })
            );

        $this->assertInstanceOf(
            PrepareRedirection::class,
            ($this->prepareRedirection)(
                $manager,
                $spaceProject,
                'route_admin_foo',
                null,
                [],
                null
            ),
        );
    }

    public function testInvokeWithObjectSaved(): void
    {
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('project-123');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return 'saved-object' === $data['parameters']['objectSaved'];
                })
            );

        $this->assertInstanceOf(
            PrepareRedirection::class,
            ($this->prepareRedirection)(
                $manager,
                $spaceProject,
                'foo',
                'saved-object',
            ),
        );
    }

    public function testInvokeWithBoolObjectSaved(): void
    {
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('project-123');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return true === $data['parameters']['objectSaved'];
                })
            );

        $this->assertInstanceOf(
            PrepareRedirection::class,
            ($this->prepareRedirection)(
                $manager,
                $spaceProject,
                'foo',
                true,
            ),
        );
    }

    public function testInvokeWithExistingParameters(): void
    {
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('project-123');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return 'project-123' === $data['parameters']['id']
                        && 'bar' === $data['parameters']['foo']
                        && 'qux' === $data['parameters']['baz'];
                })
            );

        $this->assertInstanceOf(
            PrepareRedirection::class,
            ($this->prepareRedirection)(
                $manager,
                $spaceProject,
                'foo',
                null,
                ['foo' => 'bar', 'baz' => 'qux']
            ),
        );
    }

    public function testInvokeWithAccountIdAndAdminRoute(): void
    {
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('project-123');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return isset($data['parameters']['accountId'])
                        && 'account-789' === $data['parameters']['accountId'];
                })
            );

        $this->assertInstanceOf(
            PrepareRedirection::class,
            ($this->prepareRedirection)(
                $manager,
                $spaceProject,
                'some_admin_route',
                null,
                [],
                'account-789'
            ),
        );
    }
}
