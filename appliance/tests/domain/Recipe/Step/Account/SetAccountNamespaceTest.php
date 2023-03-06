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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Recipe\Step\Account\SetAccountNamespace;

/**
 * Class SetAccountNamespaceTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\Account\SetAccountNamespace
 */
class SetAccountNamespaceTest extends TestCase
{
    private SetAccountNamespace $setAccountNamespace;

    private FindSlugService|MockObject $findSlugService;

    private AccountLoader|MockObject $accountLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->findSlugService = $this->createMock(FindSlugService::class);
        $this->accountLoader = $this->createMock(AccountLoader::class);

        $this->setAccountNamespace = new SetAccountNamespace(
            $this->findSlugService,
            $this->accountLoader,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            SetAccountNamespace::class,
            ($this->setAccountNamespace)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(Account::class),
            ),
        );
    }
}
