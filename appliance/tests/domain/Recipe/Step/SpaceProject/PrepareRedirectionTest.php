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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\SpaceProject;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection;

/**
 * Class PrepareRedirectionTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection
 */
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
        self::assertInstanceOf(
            PrepareRedirection::class,
            ($this->prepareRedirection)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(SpaceProject::class),
            ),
        );
    }
}
