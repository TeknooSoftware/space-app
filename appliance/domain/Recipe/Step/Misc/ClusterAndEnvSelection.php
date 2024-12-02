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

namespace Teknoo\Space\Recipe\Step\Misc;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

use function explode;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ClusterAndEnvSelection
{
    public function __construct(
        private readonly ClusterCatalog $catalog,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ServerRequestInterface $request,
        ParametersBag $parametersBag,
        ?AccountWallet $accountWallet = null,
    ): self {
        $clusterSelected = null;
        $clusterSelectedStr = $request->getQueryParams()['cluster'] ?? null;
        $clusterSlug = null;
        $envName = '_all';
        $namespace = '_all';

        if (!empty($clusterSelectedStr)) {
            $clusterArray = explode('~', $clusterSelectedStr);
            $clusterSlug = $clusterArray[0];

            if (!empty($clusterArray[1])) {
                $envName = $clusterArray[1];
            }
        }

        if (null !== $accountWallet) {
            /** @var AccountEnvironment $accountEnv */
            foreach ($accountWallet as $accountEnv) {
                $currentValue = $accountEnv->getClusterName() . '~' . $accountEnv->getEnvName();

                if (empty($clusterSelectedStr) || $clusterSelectedStr === $currentValue) {
                    $clusterSelectedStr = $currentValue;
                    $clusterSelected = $this->catalog->getCluster($accountEnv->getClusterName());
                    $clusterSlug = $clusterSelected->sluggyName;
                    $envName = $accountEnv->getEnvName();
                    $namespace = $accountEnv->getNamespace();

                    break;
                }
            }
        } else {
            foreach ($this->catalog as $clusterConfig) {
                if (empty($clusterSelectedStr) || $clusterSelectedStr === $clusterConfig->sluggyName) {
                    $clusterSelectedStr = $clusterConfig->sluggyName;
                    $clusterSlug = $clusterConfig->sluggyName;
                    $clusterSelected = $clusterConfig;

                    break;
                }
            }
        }

        $manager->updateWorkPlan([
            'clusterName' => $clusterSelected?->name,
            'envName' => $envName,
        ]);

        $parametersBag->set('clusterSelectedStr', $clusterSelectedStr);
        $parametersBag->set('clusterSelected', $clusterSelected);
        $parametersBag->set('clusterSlug', $clusterSlug);
        $parametersBag->set('envName', $envName);
        $parametersBag->set('namespace', $namespace);

        return $this;
    }
}
