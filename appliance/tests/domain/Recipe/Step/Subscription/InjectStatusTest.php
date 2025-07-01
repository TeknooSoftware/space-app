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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Subscription;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Subscription\InjectStatus;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(InjectStatus::class)]
class InjectStatusTest extends TestCase
{
    private InjectStatus $injectStatus;

    private ProjectLoader&MockObject $projectLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->injectStatus = new InjectStatus(
            projectLoader: $this->projectLoader = $this->createMock(ProjectLoader::class),
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            InjectStatus::class,
            ($this->injectStatus)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ParametersBag::class),
                new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environments: []
                ),
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [
                        [
                            'category' => 'compute',
                            'type' => 'cpu',
                            'capacity' => '5',
                            'require' => '2',
                        ]
                    ]
                ),
            ),
        );
    }
}
