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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountCredential;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Recipe\Step\AccountCredential\RemoveCredentials;
use Teknoo\Space\Writer\AccountCredentialWriter;

/**
 * Class RemoveCredentialsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\AccountCredential\RemoveCredentials
 */
class RemoveCredentialsTest extends TestCase
{
    private RemoveCredentials $removeCredentials;

    private AccountCredentialWriter|MockObject $writer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountCredentialWriter::class);
        $this->removeCredentials = new RemoveCredentials($this->writer);
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            RemoveCredentials::class,
            ($this->removeCredentials)(
                $this->createMock(AccountCredential::class),
            ),
        );
    }
}
