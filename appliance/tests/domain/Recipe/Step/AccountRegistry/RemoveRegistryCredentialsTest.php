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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountRegistry;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredentials;
use Teknoo\Space\Writer\AccountRegistryWriter;

/**
 * Class RemoveRegistrysTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredentials
 */
class RemoveRegistryCredentialsTest extends TestCase
{
    private RemoveRegistryCredentials $removeRegistryCredentials;

    private AccountRegistryWriter|MockObject $writer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountRegistryWriter::class);
        $this->removeRegistryCredentials = new RemoveRegistryCredentials($this->writer);
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            RemoveRegistryCredentials::class,
            ($this->removeRegistryCredentials)(
                $this->createMock(AccountRegistry::class),
            ),
        );
    }
}
