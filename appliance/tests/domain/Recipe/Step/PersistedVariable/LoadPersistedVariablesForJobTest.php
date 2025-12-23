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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\PersistedVariable;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Loader\ProjectPersistedVariableLoader;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class LoadPersistedVariablesForJobTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadPersistedVariablesForJob::class)]
class LoadPersistedVariablesForJobTest extends TestCase
{
    private LoadPersistedVariablesForJob $loadPersistedVariablesForJob;

    private AccountPersistedVariableLoader&MockObject $loaderAccountPV;

    private ProjectPersistedVariableLoader&MockObject $loaderPV;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loaderAccountPV = $this->createMock(AccountPersistedVariableLoader::class);
        $this->loaderPV = $this->createMock(ProjectPersistedVariableLoader::class);
        $this->loadPersistedVariablesForJob = new LoadPersistedVariablesForJob($this->loaderAccountPV, $this->loaderPV);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            LoadPersistedVariablesForJob::class,
            ($this->loadPersistedVariablesForJob)(
                $this->createStub(ManagerInterface::class),
                new SpaceProject($this->createStub(Project::class)),
                $this->createStub(NewJob::class),
            )
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithAccountPersistedVariables(): void
    {
        $account = $this->createStub(Account::class);
        $project = $this->createMock(Project::class);
        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject->method('getAccount')
            ->willReturn($account);
        $spaceProject->project = $project;
        $spaceProject->projectMetadata = null;

        $newJob = new NewJob();

        $apv1 = $this->createStub(AccountPersistedVariable::class);
        $apv1->method('getId')->willReturn('apv1-id');
        $apv1->method('getName')->willReturn('APV_VAR1');
        $apv1->method('getValue')->willReturn('apv-value1');
        $apv1->method('getEnvName')->willReturn('prod');
        $apv1->method('isSecret')->willReturn(false);
        $apv1->method('getEncryptionAlgorithm')->willReturn(null);

        $apv2 = $this->createStub(AccountPersistedVariable::class);
        $apv2->method('getId')->willReturn('apv2-id');
        $apv2->method('getName')->willReturn('APV_SECRET');
        $apv2->method('getValue')->willReturn('secret-value');
        $apv2->method('getEnvName')->willReturn('prod');
        $apv2->method('isSecret')->willReturn(true);
        $apv2->method('getEncryptionAlgorithm')->willReturn('aes-256');

        $this->loaderAccountPV->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) use ($apv1, $apv2) {
                $promise->success([$apv1, $apv2]);
                return $this->loaderAccountPV;
            });

        $this->loaderPV->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success([]);
                return $this->loaderPV;
            });

        $result = ($this->loadPersistedVariablesForJob)(
            $this->createStub(ManagerInterface::class),
            $spaceProject,
            $newJob,
        );

        $this->assertInstanceOf(LoadPersistedVariablesForJob::class, $result);
        $this->assertEquals('prod', $newJob->envName);
        $this->assertArrayHasKey('APV_VAR1', $newJob->variables);
        $this->assertArrayHasKey('APV_SECRET', $newJob->variables);
        $this->assertEquals('apv-value1', $newJob->variables['APV_VAR1']->value);
        $this->assertFalse($newJob->variables['APV_VAR1']->secret);
        $this->assertFalse($newJob->variables['APV_VAR1']->canPersist);
        $this->assertTrue($newJob->variables['APV_SECRET']->secret);
        $this->assertEquals('aes-256', $newJob->variables['APV_SECRET']->encryptionAlgorithm);
    }

    public function testInvokeWithProjectPersistedVariables(): void
    {
        $account = $this->createStub(Account::class);
        $project = $this->createStub(Project::class);
        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject
            ->method('getAccount')
            ->willReturn($account);
        $spaceProject->project = $project;
        $spaceProject->projectMetadata = null;

        $newJob = new NewJob();

        $ppv1 = $this->createStub(ProjectPersistedVariable::class);
        $ppv1->method('getId')->willReturn('ppv1-id');
        $ppv1->method('getName')->willReturn('PPV_VAR1');
        $ppv1->method('getValue')->willReturn('ppv-value1');
        $ppv1->method('getEnvName')->willReturn('staging');
        $ppv1->method('isSecret')->willReturn(false);
        $ppv1->method('getEncryptionAlgorithm')->willReturn(null);

        $this->loaderAccountPV->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success([]);
                return $this->loaderAccountPV;
            });

        $this->loaderPV->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) use ($ppv1) {
                $promise->success([$ppv1]);
                return $this->loaderPV;
            });

        $result = ($this->loadPersistedVariablesForJob)(
            $this->createStub(ManagerInterface::class),
            $spaceProject,
            $newJob,
        );

        $this->assertInstanceOf(LoadPersistedVariablesForJob::class, $result);
        $this->assertEquals('staging', $newJob->envName);
        $this->assertArrayHasKey('PPV_VAR1', $newJob->variables);
        $this->assertEquals('ppv-value1', $newJob->variables['PPV_VAR1']->value);
        $this->assertTrue($newJob->variables['PPV_VAR1']->canPersist);
    }

    public function testInvokeWithProjectMetadataAndProjectUrl(): void
    {
        $account = $this->createStub(Account::class);
        $project = $this->createStub(Project::class);

        $projectMetadata = $this->createMock(ProjectMetadata::class);
        $projectMetadata->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($callbacks) use ($projectMetadata) {
                if (isset($callbacks['projectUrl'])) {
                    $callbacks['projectUrl']('https://example.com/project');
                }
                return $projectMetadata;
            });

        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject
            ->method('getAccount')
            ->willReturn($account);
        $spaceProject->project = $project;
        $spaceProject->projectMetadata = $projectMetadata;

        $newJob = new NewJob();

        $this->loaderAccountPV->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success([]);
                return $this->loaderAccountPV;
            });

        $this->loaderPV->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success([]);
                return $this->loaderPV;
            });

        $result = ($this->loadPersistedVariablesForJob)(
            $this->createStub(ManagerInterface::class),
            $spaceProject,
            $newJob,
        );

        $this->assertInstanceOf(LoadPersistedVariablesForJob::class, $result);
        $this->assertArrayHasKey('PROJECT_URL', $newJob->variables);
        $this->assertEquals('https://example.com/project', $newJob->variables['PROJECT_URL']->value);
        $this->assertFalse($newJob->variables['PROJECT_URL']->persisted);
    }

    public function testInvokeWithMixedVariables(): void
    {
        $account = $this->createStub(Account::class);
        $project = $this->createStub(Project::class);

        $projectMetadata = $this->createMock(ProjectMetadata::class);
        $projectMetadata->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($callbacks) use ($projectMetadata) {
                if (isset($callbacks['projectUrl'])) {
                    $callbacks['projectUrl']('https://example.com/mixed');
                }
                return $projectMetadata;
            });

        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject
            ->method('getAccount')
            ->willReturn($account);
        $spaceProject->project = $project;
        $spaceProject->projectMetadata = $projectMetadata;

        $newJob = new NewJob();

        $apv = $this->createStub(AccountPersistedVariable::class);
        $apv->method('getId')->willReturn('apv-id');
        $apv->method('getName')->willReturn('ACCOUNT_VAR');
        $apv->method('getValue')->willReturn('account-val');
        $apv->method('getEnvName')->willReturn('dev');
        $apv->method('isSecret')->willReturn(true);
        $apv->method('getEncryptionAlgorithm')->willReturn('rsa');

        $ppv = $this->createStub(ProjectPersistedVariable::class);
        $ppv->method('getId')->willReturn('ppv-id');
        $ppv->method('getName')->willReturn('PROJECT_VAR');
        $ppv->method('getValue')->willReturn('project-val');
        $ppv->method('getEnvName')->willReturn('dev');
        $ppv->method('isSecret')->willReturn(false);
        $ppv->method('getEncryptionAlgorithm')->willReturn(null);

        $this->loaderAccountPV->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) use ($apv) {
                $promise->success([$apv]);
                return $this->loaderAccountPV;
            });

        $this->loaderPV->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) use ($ppv) {
                $promise->success([$ppv]);
                return $this->loaderPV;
            });

        $result = ($this->loadPersistedVariablesForJob)(
            $this->createStub(ManagerInterface::class),
            $spaceProject,
            $newJob,
        );

        $this->assertInstanceOf(LoadPersistedVariablesForJob::class, $result);
        $this->assertEquals('dev', $newJob->envName);
        $this->assertArrayHasKey('ACCOUNT_VAR', $newJob->variables);
        $this->assertArrayHasKey('PROJECT_VAR', $newJob->variables);
        $this->assertArrayHasKey('PROJECT_URL', $newJob->variables);
        $this->assertFalse($newJob->variables['ACCOUNT_VAR']->canPersist);
        $this->assertTrue($newJob->variables['PROJECT_VAR']->canPersist);
    }
}
