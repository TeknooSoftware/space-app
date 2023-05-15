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

namespace Teknoo\Space\Tests\Unit\Recipe\Cookbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Cookbook\ProjectList;
use Teknoo\Space\Recipe\Step\Project\PrepareCriteria;

/**
 * Class ProjectListTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Cookbook\ProjectList
 */
class ProjectListTest extends TestCase
{
    private ProjectList $projectList;

    private RecipeInterface|MockObject $recipe;

    private ExtractPage|MockObject $extractPage;

    private ExtractOrder|MockObject $extractOrder;

    private PrepareCriteria|MockObject $prepareCriteria;

    private LoadListObjects|MockObject $loadListObjects;

    private RenderList|MockObject $renderList;

    private RenderError|MockObject $renderError;

    private SearchFormLoaderInterface|MockObject $searchFormLoader;

    private ListObjectsAccessControlInterface|MockObject $listObjectsAccessControl;

    private string $defaultErrorTemplate;

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
        $this->prepareCriteria = $this->createMock(PrepareCriteria::class);
        $this->loadListObjects = $this->createMock(LoadListObjects::class);
        $this->renderList = $this->createMock(RenderList::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->searchFormLoader = $this->createMock(SearchFormLoaderInterface::class);
        $this->listObjectsAccessControl = $this->createMock(ListObjectsAccessControlInterface::class);
        $this->defaultErrorTemplate = '42';
        $this->loadListObjectsWiths = [];
        $this->projectList = new ProjectList(
            $this->recipe,
            $this->extractPage,
            $this->extractOrder,
            $this->prepareCriteria,
            $this->loadListObjects,
            $this->renderList,
            $this->renderError,
            $this->searchFormLoader,
            $this->listObjectsAccessControl,
            $this->defaultErrorTemplate,
            $this->loadListObjectsWiths,
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
            CookbookInterface::class,
            $this->projectList->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
