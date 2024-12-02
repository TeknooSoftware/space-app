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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\User\LoadUserInSpace;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Loader\Meta\SpaceUserLoader;

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

    private TokenStorageInterface|MockObject $tokenStorage;

    private SpaceUserLoader|MockObject $spaceUserLoader;

    private SpaceAccountLoader|MockObject $spaceAccountLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->spaceUserLoader = $this->createMock(SpaceUserLoader::class);
        $this->spaceAccountLoader = $this->createMock(SpaceAccountLoader::class);
        $this->loadUserInSpace = new LoadUserInSpace(
            $this->tokenStorage,
            $this->spaceUserLoader,
            $this->spaceAccountLoader,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            LoadUserInSpace::class,
            ($this->loadUserInSpace)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ParametersBag::class),
            )
        );
    }
}
