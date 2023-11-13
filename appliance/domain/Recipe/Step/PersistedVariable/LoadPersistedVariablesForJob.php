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

namespace Teknoo\Space\Recipe\Step\PersistedVariable;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Loader\PersistedVariableLoader;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\PersistedVariable;
use Teknoo\Space\Query\AccountPersistedVariable\LoadFromAccountQuery;
use Teknoo\Space\Query\PersistedVariable\LoadFromProjectQuery;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadPersistedVariablesForJob
{
    public function __construct(
        private AccountPersistedVariableLoader $loaderAccountPV,
        private PersistedVariableLoader $loaderPV,
    ) {
    }

    private function loadFromAccount(
        Account $account,
        NewJob $newJob,
    ): void {
        /** @var Promise<iterable<AccountPersistedVariable>, mixed, mixed> $fetchedFromAccountPromise */
        $fetchedFromAccountPromise = new Promise(
            /** @var \Teknoo\Space\Object\Persisted\AccountPersistedVariable[] $apvs */
            static function (iterable $apvs) use ($newJob): void {
                foreach ($apvs as $var) {
                    $newJob->envName = $newJob->envName ?? $var->getEnvironmentName();
                    $newJob->variables[$name = $var->getName()] = new JobVar(
                        id: $var->getId(),
                        name: $name,
                        value: $var->getValue(),
                        secret: $var->isSecret(),
                        persisted: true,
                        persistedVar: $var
                    );
                }
            }
        );

        $this->loaderAccountPV->query(
            new LoadFromAccountQuery($account),
            $fetchedFromAccountPromise,
        );
    }

    private function loadFromProject(
        SpaceProject $project,
        NewJob $newJob,
    ): void {
        /** @var Promise<iterable<PersistedVariable>, mixed, mixed> $fetchedFromProjectPromise */
        $fetchedFromProjectPromise = new Promise(
            /** @var \Teknoo\Space\Object\Persisted\PersistedVariable[] $persistedVariables */
            static function (iterable $persistedVariables) use ($newJob, $project): void {
                $project->projectMetadata?->visit(
                    [
                        'projectUrl' => function (string $projectUrl) use (&$newJob): void {
                            $newJob->variables['PROJECT_URL'] = new JobVar(
                                name: 'PROJECT_URL',
                                value: $projectUrl,
                                persisted: false,
                            );
                        },
                    ],
                );

                foreach ($persistedVariables as $var) {
                    $newJob->envName = $newJob->envName ?? $var->getEnvironmentName();
                    $newJob->variables[$name = $var->getName()] = new JobVar(
                        id: $var->getId(),
                        name: $name,
                        value: $var->getValue(),
                        secret: $var->isSecret(),
                        persisted: true,
                        persistedVar: $var,
                    );
                }
            }
        );

        $this->loaderPV->query(
            new LoadFromProjectQuery($project->project),
            $fetchedFromProjectPromise,
        );
    }


    public function __invoke(
        ManagerInterface $manager,
        SpaceProject $project,
        NewJob $newJob,
    ): self {
        $newJob->variables = [];
        $this->loadFromAccount(
            account: $project->getAccount(),
            newJob: $newJob,
        );

        $this->loadFromProject(
            project: $project,
            newJob: $newJob,
        );

        return $this;
    }
}
