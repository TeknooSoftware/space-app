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
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\ProjectList;
use Teknoo\Space\Recipe\Step\Account\LoadAccountFromRequest;
use Teknoo\Space\Recipe\Step\Misc\PrepareCriteria;

/**
 * Class ProjectListTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license https://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProjectList::class)]
class ProjectListTest extends TestCase
{
    private ProjectList $projectList;

    private RecipeInterface|MockObject $recipe;

    private ExtractPage|MockObject $extractPage;

    private ExtractOrder|MockObject $extractOrder;

    private LoadAccountFromRequest|MockObject $loadAccountFromRequest;

    private PrepareCriteria|MockObject $prepareCriteria;

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
        $this->extractPage = $this->createMock(ExtractPage::class);
        $this->extractOrder = $this->createMock(ExtractOrder::class);
        $this->loadAccountFromRequest = $this->createMock(LoadAccountFromRequest::class);
        $this->prepareCriteria = $this->createMock(PrepareCriteria::class);
        $this->loadListObjects = $this->createMock(LoadListObjects::class);
        $this->renderList = $this->createMock(RenderList::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->searchFormLoader = $this->createMock(SearchFormLoaderInterface::class);
        $this->listObjectsAccessControl = $this->createMock(ListObjectsAccessControlInterface::class);
        $this->defaultErrorTemplate = '42';
        $this->loadListObjectsWiths = [];
        $this->projectList = new ProjectList(
            recipe: $this->recipe,
            extractPage: $this->extractPage,
            extractOrder: $this->extractOrder,
            loadAccountFromRequest: $this->loadAccountFromRequest,
            prepareCriteria: $this->prepareCriteria,
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
            ProjectList::class,
            $this->projectList,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->projectList->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
