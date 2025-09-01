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
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - AccountClusterList@teknoo.software)
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
use PHPUnit\Framework\TestCase;
use Stringable;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\AccountClusterList;
use Teknoo\Space\Recipe\Step\Account\InjectToView;
use Teknoo\Space\Recipe\Step\Misc\PrepareCriteria;

/**
 * Class AccountClusterListTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - AccountClusterList@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountClusterList::class)]
class AccountClusterListTest extends TestCase
{
    private AccountClusterList $AccountClusterList;

    private RecipeInterface&MockObject $recipe;

    private JumpIfNot&MockObject $jumpIfNot;

    private LoadObject&MockObject $loadObject;

    private ObjectAccessControlInterface&MockObject $objectAccessControl;

    private ExtractPage&MockObject $extractPage;

    private ExtractOrder&MockObject $extractOrder;

    private PrepareCriteria&MockObject $prepareCriteria;

    private LoadListObjects&MockObject $loadListObjects;

    private InjectToView&MockObject $injectToView;

    private RenderList&MockObject $renderList;

    private RenderError&MockObject $renderError;

    private SearchFormLoaderInterface&MockObject $searchFormLoader;

    private ListObjectsAccessControlInterface&MockObject $listObjectsAccessControl;

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
        $this->jumpIfNot = $this->createMock(JumpIfNot::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->prepareCriteria = $this->createMock(PrepareCriteria::class);
        $this->loadListObjects = $this->createMock(LoadListObjects::class);
        $this->injectToView = $this->createMock(InjectToView::class);
        $this->renderList = $this->createMock(RenderList::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->searchFormLoader = $this->createMock(SearchFormLoaderInterface::class);
        $this->listObjectsAccessControl = $this->createMock(ListObjectsAccessControlInterface::class);
        $this->defaultErrorTemplate = '42';
        $this->loadListObjectsWiths = [];

        $this->AccountClusterList = new AccountClusterList(
            recipe: $this->recipe,
            extractPage: $this->extractPage,
            extractOrder: $this->extractOrder,
            jumpIfNot: $this->jumpIfNot,
            loadObject: $this->loadObject,
            objectAccessControl: $this->objectAccessControl,
            prepareCriteria: $this->prepareCriteria,
            loadListObjects: $this->loadListObjects,
            injectToView: $this->injectToView,
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
            AccountClusterList::class,
            $this->AccountClusterList,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->AccountClusterList->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
