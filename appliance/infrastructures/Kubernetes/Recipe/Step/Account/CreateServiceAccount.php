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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account;

use DateTimeInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Kubernetes\Model\ServiceAccount;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateServiceAccount
{
    private const SERVICE_SUFFIX = '-account';

    public function __construct(
        private KubernetesClient $client,
        private DatesService $datesService,
        private bool $prefereRealDate,
    ) {
    }

    private function createAccount(string $name, string $namespace): ServiceAccount
    {
        return new ServiceAccount([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                ],
            ],
        ]);
    }

    public function __invoke(
        ManagerInterface $manager,
        string $kubeNamespace,
        string $accountNamespace,
        AccountHistory $accountHistory
    ): self {
        $serviceName = $accountNamespace . self::SERVICE_SUFFIX;

        $this->client->setNamespace($kubeNamespace);
        $account = $this->createAccount($serviceName, $kubeNamespace);
        $accountRepository = $this->client->serviceAccounts();
        if (!$accountRepository->exists((string) $account->getMetadata('name'))) {
            $accountRepository->apply($account);
        }

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $serviceName) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.service_account',
                    $dateTime,
                    false,
                    [
                        'service_account' => $serviceName
                    ]
                );
            },
            $this->prefereRealDate,
        );

        $manager->updateWorkPlan(['serviceName' => $serviceName]);

        return $this;
    }
}
