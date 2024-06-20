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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Service\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Service\Account\CodeGenerator;

/**
 * Class CodeGeneratorTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CodeGenerator::class)]
class CodeGeneratorTest extends TestCase
{
    private CodeGenerator $codeGenerator;

    private string $codeGeneratorSalt;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->codeGeneratorSalt = '42';
        $this->codeGenerator = new CodeGenerator($this->codeGeneratorSalt);
    }

    public function testVerify(): void
    {
        self::assertInstanceOf(
            CodeGenerator::class,
            $this->codeGenerator->verify(
                'foo',
                'bar',
                $this->createMock(PromiseInterface::class),
            )
        );
    }

    public function testGenerateCode(): void
    {
        self::assertInstanceOf(
            CodeGenerator::class,
            $this->codeGenerator->generateCode(
                'foo',
                $this->createMock(PromiseInterface::class),
            )
        );
    }
}
