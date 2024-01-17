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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Twig\Extension;

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
 * @covers \Teknoo\Space\Infrastructures\Twig\Extension\MFARoutesFinder
 */
class MFARoutesFinderTest extends TestCase
{
    private ?UserInterface $user;

    private MFARoutesFinder $mFARoutesFinder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(UserInterface::class);
        $this->mFARoutesFinder = new MFARoutesFinder(
            'generic',
            [
                'generic' => [
                     'disable' => 'foo',
                ],
            ]
        );
    }

    public function testGetFunctions(): void
    {
        self::assertIsArray(
            $this->mFARoutesFinder->getFunctions(),
        );
    }

    public function testGetName(): void
    {
        self::assertIsString(
            $this->mFARoutesFinder->getName(),
        );
    }

    public function testGetMFARoutesFinder(): void
    {
        self::assertIsString(
            $this->mFARoutesFinder->find(
                $this->user,
                MFARoutesFinder\Operation::DISABLE->value,
            ),
        );
    }
}
