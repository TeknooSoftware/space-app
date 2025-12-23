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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Twig\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\Space\Infrastructures\Twig\Extension\MFARoutesFinder;

/**
 * Class MFARoutesFinderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(MFARoutesFinder::class)]
class MFARoutesFinderTest extends TestCase
{
    private UserInterface&Stub $user;

    private MFARoutesFinder $mFARoutesFinder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createStub(UserInterface::class);
        $this->mFARoutesFinder = new MFARoutesFinder(
            'generic',
            [
                'generic' => [
                     'disable' => 'foo',
                ],
            ]
        );
    }

    public function testGetMFARoutesFinder(): void
    {
        $this->assertIsString(
            $this->mFARoutesFinder->find(
                $this->user,
                MFARoutesFinder\Operation::DISABLE->value,
            ),
        );
    }
}
