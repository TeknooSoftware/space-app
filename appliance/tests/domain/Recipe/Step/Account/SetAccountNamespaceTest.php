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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Recipe\Step\Account\SetAccountNamespace;

/**
 * Class SetAccountNamespaceTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SetAccountNamespace::class)]
class SetAccountNamespaceTest extends TestCase
{
    private SetAccountNamespace $setAccountNamespace;

    private FindSlugService&MockObject $findSlugService;

    private AccountLoader&MockObject $accountLoader;

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
            'foo'
        );
    }

    public function testInvoke(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->any())
            ->method('getId')
            ->willReturn('account-999');

        $account->expects($this->any())
            ->method('namespaceIsItDefined')
            ->willReturnCallback(
                function (PromiseInterface $promise) use ($account) {
                    $promise->success('foo');

                    return $account;
                }
            );

        $account->expects($this->once())
            ->method('setNamespace')
            ->with('foo-1')
            ->willReturnSelf();

        $this->findSlugService
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (
                    LoaderInterface $loader,
                    string $slugField,
                    SluggableInterface $sluggable,
                    array $parts,
                    string $glue = '-'
                ) {
                    $this->assertInstanceOf(AccountLoader::class, $loader);
                    $this->assertEquals('namespace', $slugField);

                    $this->assertEquals(
                        ['foo'],
                        $parts,
                    );

                    $this->assertEquals(
                        'account-999',
                        $sluggable->getId(),
                    );

                    $this->assertSame(
                        $sluggable,
                        $sluggable->prepareSlugNear(
                            $loader,
                            $this->findSlugService,
                            'namespace'
                        )
                    );

                    $sluggable->setSlug('foo-1');

                    return $this->findSlugService;
                }
            );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['accountNamespace' => 'foo-1'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            SetAccountNamespace::class,
            ($this->setAccountNamespace)(
                $manager,
                $account,
            ),
        );
    }
}
