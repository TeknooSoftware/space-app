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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Object\Account\AccountAwareInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client as KubernetesClient;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ReloadNamespace
{
    public function __construct(
        private KubernetesClient $client,
    ) {
    }

    public function __invoke(
        Account $account,
        ManagerInterface $manager,
    ): self {
        $account->requireAccountNamespace(
            new class ($manager, $this->client) implements AccountAwareInterface {
                public function __construct(
                    public ManagerInterface $manager,
                    private KubernetesClient $client,
                ) {
                }

                public function passAccountNamespace(
                    Account $account,
                    ?string $name,
                    ?string $namespace,
                    ?string $prefixNamespace,
                    bool $useHierarchicalNamespaces,
                ): AccountAwareInterface {
                    $kubeNamespace = $prefixNamespace . $namespace;
                    $this->client->setNamespace($kubeNamespace);

                    $this->manager->updateWorkPlan(
                        [
                            'accountNamespace' => $namespace,
                            'kubeNamespace' => $kubeNamespace,
                        ]
                    );

                    return $this;
                }
            }
        );

        return $this;
    }
}
