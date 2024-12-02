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

namespace Teknoo\Space\Recipe\Step\Project;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\GitRepository;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\Project;
use Teknoo\East\Paas\Object\SshIdentity;
use Teknoo\East\Paas\Object\XRegistryAuth;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountRegistry;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PrepareProject
{
    public function __invoke(
        ManagerInterface $manager,
        Project $projectInstance,
        AccountRegistry $accountRegistry,
    ): self {
        $projectInstance->setSourceRepository(
            new GitRepository(
                '',
                'master',
                new SshIdentity('git', '')
            )
        );

        $projectInstance->setImagesRegistry(
            new ImageRegistry(
                $accountRegistry->getRegistryUrl(),
                new XRegistryAuth(
                    username: $accountRegistry->getRegistryAccountName(),
                    password: $accountRegistry->getRegistryPassword(),
                    auth: $accountRegistry->getRegistryConfigName(),
                    serverAddress: $accountRegistry->getRegistryUrl(),
                )
            )
        );

        return $this;
    }
}
