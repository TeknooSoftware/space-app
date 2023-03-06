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

namespace Teknoo\Space\Recipe\Step\AccountCredential;

use DateTimeInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountCredentialWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UpdateCredentials
{
    public function __construct(
        private AccountCredentialWriter $writer,
        private DatesService $datesService,
        private bool $prefereRealDate,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        string $registryUrl,
        string $registryAccountName,
        string $registryPassword,
        AccountCredential $accountCredential,
        AccountHistory $accountHistory
    ): self {
        $newAccountCredential = $accountCredential->updateRegistry(
            $registryUrl,
            $registryAccountName,
            $registryPassword,
        );

        $this->writer->save($newAccountCredential);

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.credential_updated',
                    $dateTime
                );
            },
            $this->prefereRealDate,
        );

        $manager->updateWorkPlan([
            AccountCredential::class => $newAccountCredential,
        ]);

        $manager->cleanWorkPlan(
            'registryAccountName',
            'registryPassword',
        );

        return $this;
    }
}
