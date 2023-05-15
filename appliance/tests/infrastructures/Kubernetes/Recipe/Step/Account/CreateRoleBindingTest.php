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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Account;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRoleBinding;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class CreateRoleBindingTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRoleBinding
 */
class CreateRoleBindingTest extends TestCase
{
    private CreateRoleBinding $createRoleBinding;

    private Client|MockObject $client;

    private DatesService|MockObject $datesService;

    private bool $prefereRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->prefereRealDate = true;
        $this->createRoleBinding = new CreateRoleBinding(
            $this->client,
            $this->datesService,
            $this->prefereRealDate
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            CreateRoleBinding::class,
            ($this->createRoleBinding)(
                $this->createMock(ManagerInterface::class),
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                $this->createMock(AccountHistory::class),
            )
        );
    }
}
