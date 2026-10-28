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

namespace Teknoo\Space\Infrastructures\Symfony\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Job;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\AbstractAccessControl;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\AccountCluster;

use function in_array;
use function is_object;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AdminVoter implements VoterInterface
{
    /**
     * The objects an administrator of Space manages, with the attributes of the access control steps it is granted
     * on: it lists the projects and the clusters of an account but can not open them, and it gets nothing on the
     * other objects (environments, variables, objects of extensions...). Instances are checked with `instanceof`, to
     * accept the Doctrine proxies.
     */
    private const array GRANTS = [
        Account::class => [AbstractAccessControl::ATTRIBUTE_ACCESS, AbstractAccessControl::ATTRIBUTE_LIST],
        SpaceAccount::class => [AbstractAccessControl::ATTRIBUTE_ACCESS, AbstractAccessControl::ATTRIBUTE_LIST],
        User::class => [AbstractAccessControl::ATTRIBUTE_ACCESS, AbstractAccessControl::ATTRIBUTE_LIST],
        SpaceUser::class => [AbstractAccessControl::ATTRIBUTE_ACCESS, AbstractAccessControl::ATTRIBUTE_LIST],
        Job::class => [AbstractAccessControl::ATTRIBUTE_ACCESS, AbstractAccessControl::ATTRIBUTE_LIST],
        Project::class => [AbstractAccessControl::ATTRIBUTE_LIST],
        SpaceProject::class => [AbstractAccessControl::ATTRIBUTE_LIST],
        AccountCluster::class => [AbstractAccessControl::ATTRIBUTE_LIST],
    ];

    public function __construct(
        private readonly string $adminRoleName,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function vote(
        TokenInterface $token,
        $subject,
        array $attributes,
        ?Vote $vote = null,
    ): int {
        if (
            !$token->getUser() instanceof AbstractUser
            || !is_object($subject)
            || !in_array($this->adminRoleName, $token->getRoleNames(), true)
        ) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach (self::GRANTS as $className => $grantedAttributes) {
            if (!$subject instanceof $className) {
                continue;
            }

            foreach ($attributes as $attribute) {
                if (in_array($attribute, $grantedAttributes, true)) {
                    $vote?->addReason('teknoo.space.vote.granted.user_is_admin');

                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
