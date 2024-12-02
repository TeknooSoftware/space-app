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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Environment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\DeleteNamespaceFromResumes;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\AbstractDeleteFromResumes;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DeleteNamespaceFromResumes::class)]
#[CoversClass(AbstractDeleteFromResumes::class)]
class DeleteNamespaceFromResumesTest extends TestCase
{
    private ClusterCatalog&MockObject $clusterCatalog;

    private DeleteNamespaceFromResumes $deleteNamespaceFromResumes;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clusterCatalog = $this->createMock(ClusterCatalog::class);

        $this->deleteNamespaceFromResumes = new DeleteNamespaceFromResumes(
            $this->clusterCatalog,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            DeleteNamespaceFromResumes::class,
            ($this->deleteNamespaceFromResumes)(
                new AccountWallet([$this->createMock(AccountEnvironment::class)]),
                new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environmentResumes: []
                ),
            ),
        );
    }
}
