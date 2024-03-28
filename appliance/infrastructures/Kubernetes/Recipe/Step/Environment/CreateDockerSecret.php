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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment;

use DateTimeInterface;
use SensitiveParameter;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Model\Secret;
use Teknoo\Space\Infrastructures\Kubernetes\Traits\InsertModelTrait;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountRegistry;

use function base64_encode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateDockerSecret
{
    /**
     * @use InsertModelTrait<Secret>
     */
    use InsertModelTrait;

    private const LABEL_GROUP = 'private-registry';

    public function __construct(
        private DatesService $datesService,
        private bool $preferRealDate,
        private string $spaceRegistryUrl,
        private string $spaceRegistryUsername,
        private string $spaceRegistryPwd,
    ) {
    }

    private function createDockerConfigSecret(
        string $namespace,
        string $name,
        string $url,
        string $username,
        #[SensitiveParameter]
        string $password,
    ): Secret {
        return new Secret([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                    'group' => self::LABEL_GROUP,
                ],
            ],
            'data' => [
                '.dockerconfigjson' => base64_encode(
                    json_encode(
                        [
                            'auths' => [
                                $url => [
                                    'username' => $username,
                                    'password' => $password,
                                    'auth' => base64_encode($username . ':' . $password)
                                ],
                                $this->spaceRegistryUrl => [
                                    'username' => $this->spaceRegistryUsername,
                                    'password' => $this->spaceRegistryPwd,
                                    'auth' => base64_encode(
                                        string: $this->spaceRegistryUsername . ':' . $this->spaceRegistryPwd,
                                    ),
                                ],
                            ],
                        ],
                        JSON_THROW_ON_ERROR
                    )
                )
            ],
            'type' => 'kubernetes.io/dockerconfigjson',
        ]);
    }

    public function __invoke(
        Account $accountInstance,
        AccountHistory $accountHistory,
        AccountRegistry $accountRegistry,
        string $kubeNamespace,
        string $accountNamespace,
        ClusterConfig $clusterConfig,
    ): self {
        $client = $clusterConfig->getKubernetesClient();

        $dockerConfigSecret = $this->createDockerConfigSecret(
            $kubeNamespace,
            $accountRegistry->getRegistryConfigName(),
            $accountRegistry->getRegistryUrl(),
            $accountRegistry->getRegistryAccountName(),
            $accountRegistry->getRegistryPassword(),
        );

        $this->insertModel(
            $client->secrets(),
            $dockerConfigSecret,
            true,
        );

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.dockersecret',
                    $dateTime,
                    false,
                );
            },
            $this->preferRealDate,
        );

        return $this;
    }
}
