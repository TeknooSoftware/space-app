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

namespace Teknoo\Space\Recipe\Step\AccountEnvironment;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\Task\DeleteEnvironmentsTask;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

use function array_flip;

/**
 * Web side of an environment removal: compares the account's wallet with the resumes submitted in the form
 * (same diff as `AbstractDeleteFromResumes`) and builds one `DeleteEnvironmentsTask` listing the removed
 * environments, for `CallNewTask` to queue to the `new_task` worker. The cluster side effects (Kubernetes
 * namespace deletion) happen there, never in the HTTP request.
 *
 * When nothing was removed, the step jumps straight to `DeleteEnvFromResumes`, skipping `CallNewTask` and
 * `AddTaskToHistory` which follow it at the same position of the account edition plans.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PrepareDeleteEnvironmentsTask
{
    public function __invoke(
        ManagerInterface $manager,
        AccountWallet $wallet,
        SpaceAccount $spaceAccount,
    ): self {
        $toDelete = [];

        if (null !== $spaceAccount->environments) {
            $idsInResumes = [];
            foreach ($spaceAccount->environments as $resume) {
                if (!empty($resume->accountEnvironmentId)) {
                    $idsInResumes[] = $resume->accountEnvironmentId;
                }
            }

            $idsInResumes = array_flip($idsInResumes);

            /** @var AccountEnvironment $env */
            foreach ($wallet as $env) {
                if (!empty($env->getId()) && !isset($idsInResumes[$env->getId()])) {
                    $toDelete[] = [
                        'envName' => $env->getEnvName(),
                        'clusterName' => $env->getClusterName(),
                        'namespace' => $env->getNamespace(),
                    ];
                }
            }
        }

        if (empty($toDelete)) {
            $manager->continue([], DeleteEnvFromResumes::class);

            return $this;
        }

        $manager->updateWorkPlan([
            NewTaskInterface::class => new DeleteEnvironmentsTask(
                accountId: (string) $spaceAccount->account->getId(),
                environments: $toDelete,
            ),
        ]);

        return $this;
    }
}
