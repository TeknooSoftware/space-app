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
use PHPUnit\Framework\TestCase;
use Stringable;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\RefreshProjectCredentials;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;
use Teknoo\Space\Recipe\Step\Project\UpdateProjectCredentialsFromAccount;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection;

/**
 * Class RefreshProjectCredentialsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RefreshProjectCredentials::class)]
class RefreshProjectCredentialsTest extends TestCase
{
    private RefreshProjectCredentials $refreshProjectCredentials;

    private RecipeInterface&MockObject $recipe;

    private LoadObject&MockObject $loadObject;

    private ObjectAccessControlInterface&MockObject $objectAccessControl;

    private LoadAccountFromProject&MockObject $loadAccountFromProject;

    private LoadEnvironments&MockObject $loadEnvironments;

    private LoadAccountClusters&MockObject $loadAccountClusters;

    private LoadRegistryCredential&MockObject $loadRegistryCredential;

    private UpdateProjectCredentialsFromAccount&MockObject $updateProjectCredentialsFromAccount;

    private SaveObject&MockObject $saveObject;

    private PrepareRedirection&MockObject $spaceProjectPrepareRedirection;

    private RedirectClientInterface&MockObject $redirectClient;

    private RenderError&MockObject $renderError;

    private string|Stringable $defaultErrorTemplate;

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
        $this->loadEnvironments = $this->createMock(LoadEnvironments::class);
        $this->loadAccountClusters = $this->createMock(LoadAccountClusters::class);
        $this->loadRegistryCredential = $this->createMock(LoadRegistryCredential::class);
        $this->updateProjectCredentialsFromAccount = $this->createMock(UpdateProjectCredentialsFromAccount::class);
        $this->saveObject = $this->createMock(SaveObject::class);
        $this->spaceProjectPrepareRedirection = $this->createMock(PrepareRedirection::class);
        $this->redirectClient = $this->createMock(RedirectClientInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->refreshProjectCredentials = new RefreshProjectCredentials(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            objectAccessControl: $this->objectAccessControl,
            loadAccountFromProject: $this->loadAccountFromProject,
            loadEnvironments: $this->loadEnvironments,
            loadAccountClusters: $this->loadAccountClusters,
            loadRegistryCredential: $this->loadRegistryCredential,
            updateProjectCredentialsFromAccount: $this->updateProjectCredentialsFromAccount,
            saveObject: $this->saveObject,
            spaceProjectPrepareRedirection: $this->spaceProjectPrepareRedirection,
            redirectClient: $this->redirectClient,
            renderError: $this->renderError,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            RefreshProjectCredentials::class,
            $this->refreshProjectCredentials,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->refreshProjectCredentials->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
