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

namespace Teknoo\Space\Infrastructures\Symfony\Security\Authenticator;

use ChrisHemmings\OAuth2\Client\Provider\DigitalOceanResourceOwner;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Mrjoops\OAuth2\Client\Provider\JiraResourceOwner;
use Omines\OAuth2\Client\Provider\GitlabResourceOwner;
use Stevenmaguire\OAuth2\Client\Provider\MicrosoftResourceOwner;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Security\Authenticator\Exception\NotManagedResourceException;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserConverter implements UserConverterInterface
{
    public function extractEmail(ResourceOwnerInterface $owner, PromiseInterface $promise): UserConverterInterface
    {
        try {
            $email = match (true) {
                $owner instanceof DigitalOceanResourceOwner => (string) $owner->getEmail(),
                $owner instanceof GitlabResourceOwner => $owner->getEmail(),
                $owner instanceof GithubResourceOwner => (string) $owner->getEmail(),
                $owner instanceof GoogleUser => (string) $owner->getEmail(),
                $owner instanceof JiraResourceOwner => (string) $owner->getEmail(),
                $owner instanceof MicrosoftResourceOwner => (string) $owner->getEmail(),
                default => throw new NotManagedResourceException(
                    'teknoo.space.error.oauth.converter.resource_unknown'
                ),
            };

            $promise->success($email);
        } catch (Throwable $error) {
            $promise->fail($error);
        }

        return $this;
    }

    public function convertToUser(ResourceOwnerInterface $owner, PromiseInterface $promise): UserConverterInterface
    {
        try {
            $user = match (true) {
                $owner instanceof DigitalOceanResourceOwner => (new User())
                    ->setEmail((string) $owner->getEmail()),

                $owner instanceof GitlabResourceOwner => (new User())
                    ->setEmail($owner->getEmail())
                    ->setLastName($owner->getName())
                    ->setFirstName($owner->getUsername()),

                $owner instanceof GithubResourceOwner => (new User())
                    ->setEmail((string) $owner->getEmail())
                    ->setLastName((string) $owner->getName())
                    ->setFirstName((string) $owner->getNickname()),

                $owner instanceof GoogleUser => (new User())
                    ->setEmail((string) $owner->getEmail())
                    ->setLastName((string) $owner->getLastName())
                    ->setFirstName((string) $owner->getFirstName()),

                $owner instanceof JiraResourceOwner => (new User())
                    ->setEmail((string) $owner->getEmail())
                    ->setLastName((string) $owner->getName())
                    ->setFirstName((string) $owner->getNickname()),

                $owner instanceof MicrosoftResourceOwner => (new User())
                    ->setEmail((string) $owner->getEmail())
                    ->setLastName((string) $owner->getLastname())
                    ->setFirstName((string) $owner->getFirstname()),

                default => throw new NotManagedResourceException(
                    'teknoo.space.error.oauth.converter.resource_unknown'
                ),
            };

            $promise->success($user);
        } catch (Throwable $error) {
            $promise->fail($error);
        }

        return $this;
    }
}
