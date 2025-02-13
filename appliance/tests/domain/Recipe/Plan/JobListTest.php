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

namespace Teknoo\Space\Tests\Unit\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stringable;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\JobList;
use Teknoo\Space\Recipe\Step\Job\PrepareCriteria;

/**
 * Class JobListTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license https://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobList::class)]
class JobListTest extends TestCase
{
    private JobList $jobList;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private ExtractPage|MockObject $extractPage;

    private ExtractOrder|MockObject $extractOrder;

    private PrepareCriteria|MockObject $jobPrepareCriteria;

    private LoadListObjects|MockObject $loadListObjects;

    private RenderList|MockObject $renderList;

    private RenderError|MockObject $renderError;

    private SearchFormLoaderInterface|MockObject $searchFormLoader;

    private ListObjectsAccessControlInterface|MockObject $listObjectsAccessControl;

    private string|Stringable $defaultErrorTemplate;

    private array $loadListObjectsWiths;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->extractPage = $this->createMock(ExtractPage::class);
        $this->extractOrder = $this->createMock(ExtractOrder::class);
        $this->jobPrepareCriteria = $this->createMock(PrepareCriteria::class);
        $this->loadListObjects = $this->createMock(LoadListObjects::class);
        $this->renderList = $this->createMock(RenderList::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->searchFormLoader = $this->createMock(SearchFormLoaderInterface::class);
        $this->listObjectsAccessControl = $this->createMock(ListObjectsAccessControlInterface::class);
        $this->defaultErrorTemplate = '42';
        $this->loadListObjectsWiths = [];
        $this->jobList = new JobList(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            extractPage: $this->extractPage,
            extractOrder: $this->extractOrder,
            jobPrepareCriteria: $this->jobPrepareCriteria,
            loadListObjects: $this->loadListObjects,
            renderList: $this->renderList,
            renderError: $this->renderError,
            searchFormLoader: $this->searchFormLoader,
            listObjectsAccessControl: $this->listObjectsAccessControl,
            defaultErrorTemplate: $this->defaultErrorTemplate,
            loadListObjectsWiths: $this->loadListObjectsWiths,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            JobList::class,
            $this->jobList,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->jobList->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
