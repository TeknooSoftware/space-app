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
use PHPUnit\Framework\MockObject\Stub;
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

    private RecipeInterface&Stub $recipe;

    private JumpIfNot&Stub $jumpIfNot;

    private LoadObject&Stub $loadObject;

    private ObjectAccessControlInterface&Stub $objectAccessControl;

    private ExtractPage&Stub $extractPage;

    private ExtractOrder&Stub $extractOrder;

    private PrepareCriteria&Stub $prepareCriteria;

    private LoadListObjects&Stub $loadListObjects;

    private InjectToView&Stub $injectToView;

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
        $this->extractPage = $this->createStub(ExtractPage::class);
        $this->extractOrder = $this->createStub(ExtractOrder::class);
        $this->jumpIfNot = $this->createStub(JumpIfNot::class);
        $this->loadObject = $this->createStub(LoadObject::class);
        $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);
        $this->prepareCriteria = $this->createStub(PrepareCriteria::class);
        $this->loadListObjects = $this->createStub(LoadListObjects::class);
        $this->injectToView = $this->createStub(InjectToView::class);
        $this->renderList = $this->createStub(RenderList::class);
        $this->renderError = $this->createStub(RenderError::class);
        $this->searchFormLoader = $this->createStub(SearchFormLoaderInterface::class);
        $this->listObjectsAccessControl = $this->createStub(ListObjectsAccessControlInterface::class);
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
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
