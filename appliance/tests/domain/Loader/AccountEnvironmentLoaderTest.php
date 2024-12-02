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

namespace Teknoo\Space\Tests\Unit\Loader;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Contracts\DbSource\Repository\AccountEnvironmentRepositoryInterface;
use Teknoo\Space\Loader\AccountEnvironmentLoader;

/**
 * Class AccountEnvironmentLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironmentLoader::class)]
class AccountEnvironmentLoaderTest extends TestCase
{
    private AccountEnvironmentLoader $accountEnvironmentLoader;

    private AccountEnvironmentRepositoryInterface|MockObject $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(AccountEnvironmentRepositoryInterface::class);
        $this->accountEnvironmentLoader = new AccountEnvironmentLoader($this->repository);
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            AccountEnvironmentLoader::class,
            $this->accountEnvironmentLoader,
        );
    }
}
