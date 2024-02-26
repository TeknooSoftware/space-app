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

namespace Teknoo\Space\Recipe\Step\AccountCredential;

use DateTimeInterface;
use SensitiveParameter;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountCredentialWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistCredentials
{
    public function __construct(
        private AccountCredentialWriter $writer,
        private DatesService $datesService,
        private bool $preferRealDate,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ObjectInterface $object,
        string $registryUrl,
        string $registryAccountName,
        string $registryConfigName,
        #[SensitiveParameter]
        string $registryPassword,
        string $serviceName,
        string $roleName,
        string $roleBindingName,
        string $caCertificate,
        string $token,
        string $persistentVolumeClaimName,
        AccountHistory $accountHistory,
        ClusterConfig $clusterConfig,
    ): self {
        if ($object instanceof SpaceAccount) {
            $object = $object->account;
        }

        if (!$object instanceof Account) {
            return $this;
        }

        $accountCredential = new AccountCredential(
            account: $object,
            clusterName: $clusterConfig->name,
            registryUrl: $registryUrl,
            registryAccountName: $registryAccountName,
            registryConfigName: $registryConfigName,
            registryPassword: $registryPassword,
            serviceAccountName: $serviceName,
            roleName: $roleName,
            roleBindingName: $roleBindingName,
            caCertificate: $caCertificate,
            clientCertificate: '',
            clientKey: '',
            token: $token,
            persistentVolumeClaimName: $persistentVolumeClaimName,
        );

        $this->writer->save($accountCredential);

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.credential_persisted',
                    $dateTime
                );
            },
            $this->preferRealDate,
        );

        $manager->updateWorkPlan([
            AccountCredential::class => $accountCredential,
        ]);

        $manager->cleanWorkPlan(
            'registryAccountName',
            'registryPassword',
            'caCertificate',
            'token',
            'clientCertificate',
            'clientKey',
        );

        return $this;
    }
}
