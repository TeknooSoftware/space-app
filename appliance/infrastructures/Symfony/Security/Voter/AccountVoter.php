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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Symfony\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Contracts\Object\AccountComponentInterface;
use Teknoo\Space\Object\DTO\SpaceAccount;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountVoter implements VoterInterface
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function vote(
        TokenInterface $token,
        $subject,
        array $attributes
    ): int {
        if (
            !$subject instanceof Account
            && !$subject instanceof SpaceAccount
            && !$subject instanceof AccountComponentInterface
        ) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $wrappedUser = $token->getUser();

        if (!$wrappedUser instanceof AbstractUser) {
            return VoterInterface::ACCESS_DENIED;
        }

        if ($subject instanceof SpaceAccount) {
            $subject = $subject->account;
        }

        $user = $wrappedUser->getWrappedUser();

        /** @var Promise<void, -1|0|1, mixed> $promise */
        $promise = new Promise(
            fn () => VoterInterface::ACCESS_GRANTED,
            fn () => VoterInterface::ACCESS_ABSTAIN,
        );

        $subject->verifyAccessToUser($user, $promise);

        return $promise->fetchResult() ?? VoterInterface::ACCESS_ABSTAIN;
    }
}
