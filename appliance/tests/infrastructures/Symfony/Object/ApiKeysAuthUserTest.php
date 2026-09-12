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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Security\Voter;
namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\Space\Infrastructures\Symfony\Object\ApiKeysAuthUser;
use Teknoo\Space\Object\Persisted\ApiKeyToken;

/**
 * Class ApiKeysAuthUserTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ApiKeysAuthUser::class)]
class ApiKeysAuthUserTest extends TestCase
{
    private ApiKeysAuthUser $apiKeysAuthUser;

    private User $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = (new User())->setEmail('foo@bar');
        $this->apiKeysAuthUser = new ApiKeysAuthUser(
            $this->user,
            new ApiKeyToken(name: 'foo', token: 'bar', tokenHash: 'hashed'),
        );
    }

    public function testGetPassword(): void
    {
        $this->assertSame('hashed', $this->apiKeysAuthUser->getPassword());
    }

    public function testGetWrappedUser(): void
    {
        $this->assertSame($this->user, $this->apiKeysAuthUser->getWrappedUser());
    }

    public function testEraseCredentials(): void
    {
        $this->apiKeysAuthUser->eraseCredentials();
        $this->assertSame('hashed', $this->apiKeysAuthUser->getPassword());
    }

    public function testGetPasswordHasherName(): void
    {
        $this->assertSame(ApiKeysAuthUser::class, $this->apiKeysAuthUser->getPasswordHasherName());
    }
}
