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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc;

use Teknoo\East\Common\View\ParametersBag;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\ClustersInfoInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ClustersInfo implements ClustersInfoInterface
{
    public function __construct(
        private readonly ClusterCatalog $clusterCatalog,
    ) {
    }

    public function __invoke(
        ParametersBag $parametersBag,
        ?AccountWallet $accountWallet = null,
    ): ClustersInfoInterface {
        $parametersBag->set('accountWallet', $accountWallet);
        $parametersBag->set('clusterCatalog', $this->clusterCatalog);

        return $this;
    }
}
