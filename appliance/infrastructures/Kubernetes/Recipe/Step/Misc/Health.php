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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc;

use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\HealthInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Health implements HealthInterface
{
    public function __construct(
        private readonly ClusterCatalog $catalog,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ParametersBag $parametersBag,
    ): HealthInterface {
        $values = [];

        foreach ($this->catalog as $cluster) {
            $client = $cluster->getKubernetesClient();
            try {
                $values = [
                    $cluster->sluggyName => [
                        'health' => $client->health(),
                        'version' => $client->version(),
                    ],
                ];
            } catch (Throwable $error) {
                $values[$cluster->sluggyName]['error'] = $error;
            }
        }

        $parametersBag->set('k8s', $values);

        return $this;
    }
}
