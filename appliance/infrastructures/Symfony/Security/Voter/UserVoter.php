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

namespace Teknoo\Space\Infrastructures\Symfony\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Common\Object\User;
use Teknoo\Space\Object\DTO\SpaceUser;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserVoter implements VoterInterface
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
            !$subject instanceof AbstractUser
            && !$subject instanceof User
            && !$subject instanceof SpaceUser
        ) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $wrappedUser = $token->getUser();

        if (!$wrappedUser instanceof AbstractUser) {
            return VoterInterface::ACCESS_DENIED;
        }

        if ($subject instanceof AbstractUser) {
            $subject = $subject->getWrappedUser();
        }

        if ($subject instanceof SpaceUser) {
            $subject = $subject->user;
        }

        if ($subject->getId() !== $wrappedUser->getWrappedUser()->getId()) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }
}
