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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Job;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Object\ImageRegistryInterface;
use Teknoo\East\Paas\Contracts\Object\SourceRepositoryInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;

/**
 * Class PrepareNewJobFormTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PrepareNewJobForm::class)]
class PrepareNewJobFormTest extends TestCase
{
    private PrepareNewJobForm $prepareNewJobForm;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->prepareNewJobForm = new PrepareNewJobForm();
    }

    public function testInvoke(): void
    {
        $project = new Project($this->createMock(Account::class));
        $project->setSourceRepository($this->createMock(SourceRepositoryInterface::class))
            ->setImagesRegistry($this->createMock(ImageRegistryInterface::class))
            ->setClusters([$this->createMock(Cluster::class)]);

        self::assertInstanceOf(
            PrepareNewJobForm::class,
            ($this->prepareNewJobForm)(
                $this->createMock(ManagerInterface::class),
                new SpaceProject($project),
                $this->createMock(NewJob::class),
                $this->createMock(ParametersBag::class),
                'foo',
            ),
        );
    }
}
