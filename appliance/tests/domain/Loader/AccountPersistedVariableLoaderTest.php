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

namespace Teknoo\Space\Tests\Unit\Loader;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Contracts\DbSource\Repository\AccountPersistedVariableRepositoryInterface;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Service\PersistedVariableEncryption;

/**
 * Class AccountPersistedVariableLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountPersistedVariableLoader::class)]
class AccountPersistedVariableLoaderTest extends TestCase
{
    private AccountPersistedVariableLoader $accountPersistedVariableLoader;

    private AccountPersistedVariableRepositoryInterface&Stub $repository;

    private PersistedVariableEncryption&Stub $persistedVariableEncryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createStub(AccountPersistedVariableRepositoryInterface::class);
        $this->persistedVariableEncryption = $this->createStub(PersistedVariableEncryption::class);
        $this->accountPersistedVariableLoader = new AccountPersistedVariableLoader(
            $this->repository,
            $this->persistedVariableEncryption,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountPersistedVariableLoader::class,
            $this->accountPersistedVariableLoader,
        );
    }
}
