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

namespace Teknoo\Space\Tests\Unit\Loader;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Contracts\DbSource\Repository\AccountPersistedVariableRepositoryInterface;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Service\PersistedVariableEncryption;

/**
 * Class AccountPersistedVariableLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license https://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountPersistedVariableLoader::class)]
class AccountPersistedVariableLoaderTest extends TestCase
{
    private AccountPersistedVariableLoader $accountPersistedVariableLoader;

    private AccountPersistedVariableRepositoryInterface|MockObject $repository;

    private PersistedVariableEncryption|MockObject $persistedVariableEncryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(AccountPersistedVariableRepositoryInterface::class);
        $this->persistedVariableEncryption = $this->createMock(PersistedVariableEncryption::class);
        $this->accountPersistedVariableLoader = new AccountPersistedVariableLoader(
            $this->repository,
            $this->persistedVariableEncryption,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            AccountPersistedVariableLoader::class,
            $this->accountPersistedVariableLoader,
        );
    }
}
