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
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountCredentialWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
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
        #[SensitiveParameter]
        string $registryPassword,
        AccountWallet $accountWallet,
        AccountHistory $accountHistory
    ): self {
        $newWallet = [];

        foreach ($accountWallet as $accountCredential) {
            $newAccountCredential = $accountCredential->updateRegistry(
                $registryUrl,
                $registryAccountName,
                $registryPassword,
            );

            $newWallet[] = $newAccountCredential;

            $this->writer->remove($accountCredential);
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
        }

        $manager->updateWorkPlan([
            AccountWallet::class => new AccountWallet($newWallet),
        ]);

        $manager->cleanWorkPlan(
            'registryAccountName',
            'registryPassword',
        );

        return $this;
    }
}
