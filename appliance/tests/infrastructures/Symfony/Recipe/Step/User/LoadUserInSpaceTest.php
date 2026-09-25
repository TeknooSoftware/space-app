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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\User\LoadUserInSpace;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Loader\Meta\SpaceUserLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\DTO\SpaceView;

/**
 * Class LoadUserInSpaceTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadUserInSpace::class)]
class LoadUserInSpaceTest extends TestCase
{
    private LoadUserInSpace $loadUserInSpace;

    private TokenStorageInterface&Stub $tokenStorage;

    private SpaceUserLoader&MockObject $spaceUserLoader;

    private SpaceAccountLoader&MockObject $spaceAccountLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = $this->createStub(TokenStorageInterface::class);
        $this->spaceUserLoader = $this->createMock(SpaceUserLoader::class);
        $this->spaceAccountLoader = $this->createMock(SpaceAccountLoader::class);
        $this->loadUserInSpace = new LoadUserInSpace(
            $this->tokenStorage,
            $this->spaceUserLoader,
            $this->spaceAccountLoader,
        );
    }

    public function testInvokeWithoutToken(): void
    {
        $this->spaceUserLoader
            ->expects($this->never())
            ->method('load');

        $this->spaceAccountLoader
            ->expects($this->never())
            ->method('fetch');

        $this->assertInstanceOf(
            LoadUserInSpace::class,
            ($this->loadUserInSpace)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ParametersBag::class),
            )
        );
    }

    public function testInvokeWithoutUserInToken(): void
    {
        $this->tokenStorage
            ->method('getToken')
            ->willReturn($this->createStub(TokenInterface::class));

        $this->spaceUserLoader
            ->expects($this->never())
            ->method('load');

        $this->spaceAccountLoader
            ->expects($this->never())
            ->method('fetch');

        $this->assertInstanceOf(
            LoadUserInSpace::class,
            ($this->loadUserInSpace)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ParametersBag::class),
            )
        );
    }

    private function prepareToken(): User
    {
        $user = (new User())->setId('user-id');
        $symfonyUser = $this->createStub(AbstractUser::class);
        $symfonyUser->method('getWrappedUser')->willReturn($user);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($symfonyUser);

        $this->tokenStorage
            ->method('getToken')
            ->willReturn($token);

        return $user;
    }

    public function testInvoke(): void
    {
        $user = $this->prepareToken();
        $account = new Account();

        $this->spaceUserLoader
            ->expects($this->once())
            ->method('load')
            ->with('user-id', $this->isInstanceOf(PromiseInterface::class))
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) use ($user): LoaderInterface {
                    $promise->success(new SpaceUser(user: $user));

                    return $this->spaceUserLoader;
                }
            );

        $this->spaceAccountLoader
            ->expects($this->once())
            ->method('fetch')
            ->with($this->isInstanceOf(QueryElementInterface::class), $this->isInstanceOf(PromiseInterface::class))
            ->willReturnCallback(
                function (QueryElementInterface $query, PromiseInterface $promise) use ($account): LoaderInterface {
                    $promise->success(new SpaceAccount(account: $account));

                    return $this->spaceAccountLoader;
                }
            );

        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->exactly(2))
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with(
                'space',
                $this->callback(
                    fn (SpaceView $view): bool => $view->user instanceof SpaceUser
                        && $view->account instanceof SpaceAccount
                        && $view->user->user === $user
                        && $view->account->account === $account
                )
            )
            ->willReturnSelf();

        $this->assertInstanceOf(
            LoadUserInSpace::class,
            ($this->loadUserInSpace)(
                $manager,
                $bag,
            )
        );
    }

    public function testInvokeWithLoadFailure(): void
    {
        $this->prepareToken();

        $this->spaceUserLoader
            ->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise): LoaderInterface {
                    $promise->fail(new RuntimeException('not found'));

                    return $this->spaceUserLoader;
                }
            );

        $this->spaceAccountLoader
            ->expects($this->never())
            ->method('fetch');

        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('error')
            ->with($this->isInstanceOf(RuntimeException::class))
            ->willReturnSelf();

        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with('space', $this->isInstanceOf(SpaceView::class))
            ->willReturnSelf();

        $this->assertInstanceOf(
            LoadUserInSpace::class,
            ($this->loadUserInSpace)(
                $manager,
                $bag,
            )
        );
    }
}
