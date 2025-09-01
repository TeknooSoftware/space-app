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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;

/**
 * Class NewJobTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewJob::class)]
class NewJobTest extends TestCase
{
    private NewJob $newJob;

    private string $newJobId;

    /**
     * @var MockObject[]
     */
    private array $variables;

    private string $projectId;

    private string $accountId;

    private string $envName;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->newJobId = '42';
        $this->variables = [
            $this->createMock(JobVar::class),
            $this->createMock(JobVar::class),
        ];
        $this->projectId = '42';
        $this->accountId = '42';
        $this->envName = '42';
        $this->newJob = new NewJob(
            newJobId: $this->newJobId,
            variables: $this->variables,
            projectId: $this->projectId,
            accountId: $this->accountId,
            envName: $this->envName
        );
    }

    public function testExport(): void
    {
        $this->variables[0]->expects($this->once())
            ->method('export')
            ->willReturnSelf();
        $this->variables[1]->expects($this->once())
            ->method('export')
            ->willReturnSelf();

        $this->assertInstanceOf(
            NewJob::class,
            $export = $this->newJob->export(),
        );

        $this->assertNotSame(
            $export,
            $this->newJob,
        );
    }
}
