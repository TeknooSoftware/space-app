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

use DateTimeInterface;
use SensitiveParameter;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountEnvironmentWriter;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistEnvironment
{
    public function __construct(
        private readonly AccountEnvironmentWriter $writer,
        private readonly DatesService $datesService,
        private readonly bool $preferRealDate,
    ) {
    }

    /**
     * @param array<string, mixed> $envMetadata
     */
    public function __invoke(
        ManagerInterface $manager,
        SpaceAccount|Account $spaceAccount,
        string $envName,
        string $kubeNamespace,
        string $serviceName,
        string $roleName,
        string $roleBindingName,
        string $caCertificate,
        #[SensitiveParameter]
        string $token,
        AccountHistory $accountHistory,
        ClusterConfig $clusterConfig,
        ?AccountEnvironmentResume $resume = null,
        array $envMetadata = [],
    ): self {
        if ($spaceAccount instanceof SpaceAccount) {
            $account = $spaceAccount->account;
        } else {
            $account = $spaceAccount;
        }

        $accountEnvironment = new AccountEnvironment(
            account: $account,
            clusterName: $clusterConfig->name,
            envName: $envName,
            namespace: $kubeNamespace,
            serviceAccountName: $serviceName,
            roleName: $roleName,
            roleBindingName: $roleBindingName,
            caCertificate: $caCertificate,
            clientCertificate: '',
            clientKey: '',
            token: $token,
            metadata: $envMetadata,
        );

        $promise = null;
        if (null !== $resume) {
            /** @var Promise<AccountEnvironment, mixed, mixed> $promise */
            $promise = new Promise(
                fn (AccountEnvironment $environment): string => $resume->accountEnvironmentId = $environment->getId(),
                fn (#[SensitiveParameter] Throwable $throwable) => throw $throwable,
            );
        }

        $this->writer->save(
            $accountEnvironment,
            $promise,
        );

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory): void {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.credential_persisted',
                    $dateTime
                );
            },
            $this->preferRealDate,
        );

        $manager->updateWorkPlan([
            AccountEnvironment::class => $accountEnvironment,
        ]);

        $manager->cleanWorkPlan(
            'caCertificate',
            'token',
            'clientCertificate',
            'clientKey',
        );

        return $this;
    }
}
