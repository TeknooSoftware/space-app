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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;

/**
 * Class AccountEnvironmentResumeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironmentResume::class)]
class AccountEnvironmentResumeTest extends TestCase
{
    private AccountEnvironmentResume $accountEnvironmentResume;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountEnvironmentResume = new AccountEnvironmentResume('foo', 'bar');
    }

    public function testConstruct(): void
    {
        $this->assertEquals(
            'foo',
            $this->accountEnvironmentResume->clusterName,
        );

        $this->assertEquals(
            'bar',
            $this->accountEnvironmentResume->envName,
        );
    }

    public function testConstructWithAccountEnvironmentId(): void
    {
        $resume = new AccountEnvironmentResume('cluster', 'env', 'id123');

        $this->assertEquals('cluster', $resume->clusterName);
        $this->assertEquals('env', $resume->envName);
        $this->assertEquals('id123', $resume->accountEnvironmentId);
    }

    public function testJsonSerialize(): void
    {
        $resume = new AccountEnvironmentResume('cluster', 'env', 'id456');

        $expected = [
            'clusterName' => 'cluster',
            'envName' => 'env',
            'accountEnvironmentId' => 'id456',
        ];

        $this->assertEquals($expected, $resume->jsonSerialize());
    }

    public function testJsonSerializeWithoutId(): void
    {
        $expected = [
            'clusterName' => 'foo',
            'envName' => 'bar',
            'accountEnvironmentId' => null,
        ];

        $this->assertEquals($expected, $this->accountEnvironmentResume->jsonSerialize());
    }
}
