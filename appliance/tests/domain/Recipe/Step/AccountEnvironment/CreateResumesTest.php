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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\CreateResumes;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CreateResumes::class)]
class CreateResumesTest extends TestCase
{
    private CreateResumes $createResumes;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createResumes = new CreateResumes();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            CreateResumes::class,
            ($this->createResumes)(
                wallet: new AccountWallet([$this->createStub(AccountEnvironment::class)]),
                parametersBag: $this->createStub(ParametersBag::class),
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithParametersBagVerification(): void
    {
        $env = $this->createMock(AccountEnvironment::class);
        $resume = $this->createMock(AccountEnvironmentResume::class);
        $env->expects($this->once())
            ->method('resume')
            ->willReturn($resume);

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->once())
            ->method('set')
            ->with(
                'accountEnvsResumes',
                $this->callback(function ($resumes) use ($resume) {
                    return is_array($resumes) && count($resumes) === 1 && $resumes[0] === $resume;
                })
            );

        $this->assertInstanceOf(
            CreateResumes::class,
            ($this->createResumes)(
                wallet: new AccountWallet([$env]),
                parametersBag: $parametersBag,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithSpaceAccount(): void
    {
        $env = $this->createMock(AccountEnvironment::class);
        $resume = $this->createMock(AccountEnvironmentResume::class);
        $env->expects($this->once())
            ->method('resume')
            ->willReturn($resume);

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->once())
            ->method('set')
            ->with('accountEnvsResumes', $this->isArray());

        $spaceAccount = new SpaceAccount(
            account: $this->createStub(Account::class),
        );

        $this->assertInstanceOf(
            CreateResumes::class,
            ($this->createResumes)(
                wallet: new AccountWallet([$env]),
                parametersBag: $parametersBag,
                spaceAccount: $spaceAccount,
            ),
        );

        $this->assertIsArray($spaceAccount->environments);
        $this->assertCount(1, $spaceAccount->environments);
        $this->assertSame($resume, $spaceAccount->environments[0]);
    }

    public function testInvokeWithEmptyWallet(): void
    {
        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->once())
            ->method('set')
            ->with(
                'accountEnvsResumes',
                $this->callback(function ($resumes) {
                    return is_array($resumes) && 0 === count($resumes);
                })
            );

        $this->assertInstanceOf(
            CreateResumes::class,
            ($this->createResumes)(
                wallet: new AccountWallet([]),
                parametersBag: $parametersBag,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithMultipleEnvironments(): void
    {
        $env1 = $this->createMock(AccountEnvironment::class);
        $resume1 = $this->createMock(AccountEnvironmentResume::class);
        $env1->expects($this->once())
            ->method('resume')
            ->willReturn($resume1);

        $env2 = $this->createMock(AccountEnvironment::class);
        $resume2 = $this->createStub(AccountEnvironmentResume::class);
        $env2->expects($this->once())
            ->method('resume')
            ->willReturn($resume2);

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->once())
            ->method('set')
            ->with(
                'accountEnvsResumes',
                $this->callback(function ($resumes) use ($resume1, $resume2) {
                    return is_array($resumes)
                        && count($resumes) === 2
                        && $resumes[0] === $resume1
                        && $resumes[1] === $resume2;
                })
            );

        $spaceAccount = new SpaceAccount(
            account: $this->createStub(Account::class),
        );

        $this->assertInstanceOf(
            CreateResumes::class,
            ($this->createResumes)(
                wallet: new AccountWallet([$env1, $env2]),
                parametersBag: $parametersBag,
                spaceAccount: $spaceAccount,
            ),
        );

        $this->assertCount(2, $spaceAccount->environments);
    }
}
