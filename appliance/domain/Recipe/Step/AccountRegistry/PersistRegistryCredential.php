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

namespace Teknoo\Space\Recipe\Step\AccountRegistry;

use DateTimeInterface;
use SensitiveParameter;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountRegistryWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistRegistryCredential
{
    public function __construct(
        private AccountRegistryWriter $writer,
        private DatesService $datesService,
        private bool $preferRealDate,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ObjectInterface $object,
        string $kubeNamespace,
        string $registryUrl,
        string $registryAccountName,
        string $registryConfigName,
        #[SensitiveParameter]
        string $registryPassword,
        string $persistentVolumeClaimName,
        AccountHistory $accountHistory,
    ): self {
        if ($object instanceof SpaceAccount) {
            $object = $object->account;
        }

        if (!$object instanceof Account) {
            return $this;
        }

        $accountRegistry = new AccountRegistry(
            account: $object,
            registryNamespace: $kubeNamespace,
            registryUrl: $registryUrl,
            registryAccountName: $registryAccountName,
            registryConfigName: $registryConfigName,
            registryPassword: $registryPassword,
            persistentVolumeClaimName: $persistentVolumeClaimName,
        );

        $this->writer->save($accountRegistry);

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.registry_persisted',
                    $dateTime
                );
            },
            $this->preferRealDate,
        );

        $manager->updateWorkPlan([
            AccountRegistry::class => $accountRegistry,
        ]);

        $manager->cleanWorkPlan(
            'registryAccountName',
            'registryPassword',
        );

        return $this;
    }
}
