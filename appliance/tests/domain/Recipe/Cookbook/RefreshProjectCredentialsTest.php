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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Cookbook\RefreshProjectCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredentials;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;
use Teknoo\Space\Recipe\Step\Project\UpdateProjectCredentialsFromAccount;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection;

/**
 * Class RefreshProjectCredentialsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Cookbook\RefreshProjectCredentials
 */
class RefreshProjectCredentialsTest extends TestCase
{
    private RefreshProjectCredentials $refreshProjectCredentials;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    private LoadAccountFromProject|MockObject $loadAccountFromProject;

    private LoadCredentials|MockObject $loadCredentials;

    private LoadRegistryCredentials|MockObject $loadRegistryCredentials;

    private UpdateProjectCredentialsFromAccount|MockObject $updateProjectCredentialsFromAccount;

    private SaveObject|MockObject $saveObject;

    private PrepareRedirection|MockObject $spaceProjectPrepareRedirection;

    private RedirectClientInterface|MockObject $redirectClient;

    private RenderError|MockObject $renderError;

    private string $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->loadAccountFromProject = $this->createMock(LoadAccountFromProject::class);
        $this->loadCredentials = $this->createMock(LoadCredentials::class);
        $this->loadRegistryCredentials = $this->createMock(LoadRegistryCredentials::class);
        $this->updateProjectCredentialsFromAccount = $this->createMock(UpdateProjectCredentialsFromAccount::class);
        $this->saveObject = $this->createMock(SaveObject::class);
        $this->spaceProjectPrepareRedirection = $this->createMock(PrepareRedirection::class);
        $this->redirectClient = $this->createMock(RedirectClientInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->refreshProjectCredentials = new RefreshProjectCredentials(
            $this->recipe,
            $this->loadObject,
            $this->objectAccessControl,
            $this->loadAccountFromProject,
            $this->loadCredentials,
            $this->loadRegistryCredentials,
            $this->updateProjectCredentialsFromAccount,
            $this->saveObject,
            $this->spaceProjectPrepareRedirection,
            $this->redirectClient,
            $this->renderError,
            $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            RefreshProjectCredentials::class,
            $this->refreshProjectCredentials,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->refreshProjectCredentials->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
