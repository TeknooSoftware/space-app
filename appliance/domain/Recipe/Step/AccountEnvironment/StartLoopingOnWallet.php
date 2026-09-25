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
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\WalletCursor;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

/**
 * Opens (or resumes) a loop over every environment of the account's wallet. The current environment is put
 * in the workplan under the `AccountEnvironment` key, the position lives in a `WalletCursor` also stored in
 * the workplan. When the wallet is exhausted (or empty), the recipe jumps to `EndLoopingOnWallet`.
 *
 * Unlike East Common's `StartLoopingOn`, this step is stateless: a worker consuming several tasks with the
 * same plan instance restarts the loop from the first environment each time.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class StartLoopingOnWallet
{
    public function __invoke(
        ManagerInterface $manager,
        AccountWallet $wallet,
        ?WalletCursor $walletCursor = null,
    ): self {
        if (null === $walletCursor) {
            $walletCursor = new WalletCursor($wallet);

            $manager->updateWorkPlan([
                WalletCursor::class => $walletCursor,
            ]);
        }

        if (!$walletCursor->valid()) {
            $manager->continue([], EndLoopingOnWallet::class);

            return $this;
        }

        $manager->updateWorkPlan([
            AccountEnvironment::class => $walletCursor->current(),
        ]);

        return $this;
    }
}
