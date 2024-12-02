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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Step\AccountEnvironment;

use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

use function array_flip;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractDeleteFromResumes
{
    abstract protected function delete(AccountEnvironment $accountEnvironment): void;

    public function __invoke(
        AccountWallet $wallet,
        SpaceAccount $spaceAccount,
    ): self {
        if (empty($spaceAccount->environmentResumes)) {
            return $this;
        }

        $idsInResumes = [];
        foreach ($spaceAccount->environmentResumes as $resume) {
            if (!empty($resume->accountEnvironmentId)) {
                $idsInResumes[] = $resume->accountEnvironmentId;
            }
        }

        $idsInResumes = array_flip($idsInResumes);

        /** @var AccountEnvironment $env */
        foreach ($wallet as $env) {
            if (!empty($env->getId()) && !isset($idsInResumes[$env->getId()])) {
                $this->delete($env);
            }
        }

        return $this;
    }
}
