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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\Space\Infrastructures\Twig\Extension\UpdateQueryField;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UpdateQueryField::class)]
class UpdateQueryFieldTest extends TestCase
{
    private UpdateQueryField $updateQueryField;

    private UrlGeneratorInterface|MockObject $generator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = $this->createMock(UrlGeneratorInterface::class);

        $this->updateQueryField = new UpdateQueryField(
            $this->generator
        );
    }

    public function testGetFunctions()
    {
        self::assertIsArray($this->updateQueryField->getFunctions());
    }

    public function testGetName()
    {
        self::assertEquals('app_update_query_field', $this->updateQueryField->getName());
    }

    public function testUpdateQueryField()
    {
        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturn('http://localhost/foo/bar?foo=bar');

        self::assertIsString(
            $this->updateQueryField->updateQueryField(
                new Request(),
                'foo',
                'bar'
            )
        );
    }
}
