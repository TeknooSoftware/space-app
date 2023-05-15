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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc;

use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Object\Account\AccountAwareInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardInfoInterface;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DashboardInfo implements DashboardInfoInterface
{
    private function getKubernetesNamespace(?Account $account): string
    {
        $urlGenerator = new class () implements AccountAwareInterface {
            public function __construct(
                public string $namespace = '_all',
            ) {
            }

            public function passAccountNamespace(
                Account $account,
                ?string $name,
                ?string $namespace,
                ?string $prefixNamespace,
                bool $useHierarchicalNamespaces,
            ): AccountAwareInterface {
                $this->namespace = $prefixNamespace . $namespace;

                return $this;
            }
        };

        $account?->requireAccountNamespace($urlGenerator);

        return $urlGenerator->namespace;
    }

    public function __invoke(
        ManagerInterface $manager,
        ParametersBag $parametersBag,
        ?Account $account = null,
    ): DashboardInfoInterface {
        $values = [];

        try {
            $values = [
                'namespace' => $this->getKubernetesNamespace($account),
            ];
        } catch (Throwable $error) {
            $values['error'] = $error;
        }

        $parametersBag->set('dashboard', $values);

        return $this;
    }
}
