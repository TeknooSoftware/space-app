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
use Teknoo\Space\Contracts\DbSource\Repository\PersistedVariableRepositoryInterface;
use Teknoo\Space\Loader\ProjectPersistedVariableLoader;
use Teknoo\Space\Service\PersistedVariableEncryption;

/**
 * Class ProjectPersistedVariableLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProjectPersistedVariableLoader::class)]
class ProjectPersistedVariableLoaderTest extends TestCase
{
    private ProjectPersistedVariableLoader $persistedVariableLoader;

    private PersistedVariableRepositoryInterface|MockObject $repository;

    private PersistedVariableEncryption|MockObject $persistedVariableEncryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PersistedVariableRepositoryInterface::class);
        $this->persistedVariableEncryption = $this->createMock(PersistedVariableEncryption::class);
        $this->persistedVariableLoader = new ProjectPersistedVariableLoader(
            $this->repository,
            $this->persistedVariableEncryption,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            ProjectPersistedVariableLoader::class,
            $this->persistedVariableLoader,
        );
    }
}
