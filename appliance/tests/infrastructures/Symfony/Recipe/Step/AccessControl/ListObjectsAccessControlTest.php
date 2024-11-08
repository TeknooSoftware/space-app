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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\AccessControl;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\AbstractAccessControl;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\ListObjectsAccessControl;

/**
 * Class ListObjectsAccessControlTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AbstractAccessControl::class)]
#[CoversClass(ListObjectsAccessControl::class)]
class ListObjectsAccessControlTest extends TestCase
{
    private ListObjectsAccessControl $listObjectsAccessControl;

    private AuthorizationCheckerInterface|MockObject $authorizationChecker;

    private TokenStorageInterface|MockObject $tokenStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->listObjectsAccessControl = new ListObjectsAccessControl(
            $this->authorizationChecker,
            $this->tokenStorage,
        );
    }

    public function testInvoke(): void
    {
        $this->authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        self::assertInstanceOf(
            ListObjectsAccessControl::class,
            ($this->listObjectsAccessControl)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(MessageInterface::class),
                [$this->createMock(ObjectInterface::class)],
            )
        );
    }
}
