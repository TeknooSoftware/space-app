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

namespace Teknoo\Space\Tests\Unit\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobList::class)]
class JobListTest extends TestCase
{
    private JobList $jobList;

    private RecipeInterface&Stub $recipe;

    private LoadObject&Stub $loadObject;

    private ExtractPage&Stub $extractPage;

    private ExtractOrder&Stub $extractOrder;

    private PrepareCriteria&Stub $jobPrepareCriteria;

    private LoadListObjects&Stub $loadListObjects;

    private RenderList&Stub $renderList;

    private RenderError&Stub $renderError;

    private SearchFormLoaderInterface&Stub $searchFormLoader;

    private ListObjectsAccessControlInterface&Stub $listObjectsAccessControl;

    private string|Stringable $defaultErrorTemplate;

    private array $loadListObjectsWiths;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->loadObject = $this->createStub(LoadObject::class);
        $this->extractPage = $this->createStub(ExtractPage::class);
        $this->extractOrder = $this->createStub(ExtractOrder::class);
        $this->jobPrepareCriteria = $this->createStub(PrepareCriteria::class);
        $this->loadListObjects = $this->createStub(LoadListObjects::class);
        $this->renderList = $this->createStub(RenderList::class);
        $this->renderError = $this->createStub(RenderError::class);
        $this->searchFormLoader = $this->createStub(SearchFormLoaderInterface::class);
        $this->listObjectsAccessControl = $this->createStub(ListObjectsAccessControlInterface::class);
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
        $this->assertInstanceOf(
            JobList::class,
            $this->jobList,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->jobList->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
