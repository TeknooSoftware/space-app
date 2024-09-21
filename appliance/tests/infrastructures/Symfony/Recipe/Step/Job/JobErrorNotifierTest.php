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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Job;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobErrorPublisher;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Notifier\JobError;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\JobErrorNotifier;

/**
 * Class JobErrorNotifierTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobErrorNotifier::class)]
class JobErrorNotifierTest extends TestCase
{
    private JobErrorNotifier $jobErrorNotifier;

    private JobError|MockObject $jobError;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobError = $this->createMock(JobError::class);
        $this->jobErrorNotifier = new JobErrorNotifier($this->jobError);
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            JobErrorNotifier::class,
            ($this->jobErrorNotifier)(
                new Exception('foo'),
                'bar',
            )
        );
    }
}
