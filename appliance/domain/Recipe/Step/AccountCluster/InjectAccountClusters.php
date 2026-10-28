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

namespace Teknoo\Space\Recipe\Step\AccountCluster;

use DomainException;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountClusterLoader;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Teknoo\Space\Query\AccountCluster\LoadFromAccountQuery;
use Throwable;

/**
 * Injects the clusters of the account into the view, as `accountClusters`, to show them read only on the edit of an
 * account by the administration. The template renders only their name and their type, never their credentials.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class InjectAccountClusters
{
    public function __construct(
        private readonly AccountClusterLoader $loader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ParametersBag $parametersBag,
        ?Account $accountInstance = null,
    ): self {
        if (null === $accountInstance) {
            return $this;
        }

        /** @var Promise<iterable<AccountCluster>, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (iterable $fetchedClusters) use ($parametersBag): void {
                $clusters = [];
                /** @var AccountCluster $accountCluster */
                foreach ($fetchedClusters as $accountCluster) {
                    $clusters[] = $accountCluster;
                }

                $parametersBag->set('accountClusters', $clusters);
            },
            static fn (Throwable $error): ChefInterface => $manager->error(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account_environment.fetching',
                    code: match (true) {
                        $error->getCode() > 0 => $error->getCode(),
                        default => 404,
                    },
                    previous: $error,
                )
            ),
        );

        $this->loader->query(
            new LoadFromAccountQuery($accountInstance),
            $fetchedPromise,
        );

        return $this;
    }
}
