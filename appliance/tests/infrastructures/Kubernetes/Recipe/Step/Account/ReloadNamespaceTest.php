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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Account;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;

/**
 * Class ReloadNamespaceTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace
 */
class ReloadNamespaceTest extends TestCase
{
    private ReloadNamespace $reloadNamespace;

    private Client|MockObject $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->reloadNamespace = new ReloadNamespace(
            $this->client,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            ReloadNamespace::class,
            ($this->reloadNamespace)(
                $this->createMock(Account::class),
                $this->createMock(ManagerInterface::class),
            ),
        );
    }
}
