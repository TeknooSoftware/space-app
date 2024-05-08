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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry;

use DateTimeInterface;
use SensitiveParameter;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Kubernetes\Model\Deployment;
use Teknoo\Kubernetes\Model\Ingress;
use Teknoo\Kubernetes\Model\Model;
use Teknoo\Kubernetes\Model\Secret;
use Teknoo\Kubernetes\Model\Service;
use Teknoo\Space\Infrastructures\Kubernetes\Traits\InsertModelTrait;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Throwable;

use function base64_encode;
use function hash;
use function password_hash;
use function random_int;
use function ucfirst;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateRegistryDeployment
{
    /**
     * @use InsertModelTrait<Deployment|Ingress|Secret|Service>
     */
    use InsertModelTrait;

    private const REPLICATION_SUFFIX = '-replication-dplmt';
    private const LABEL_GROUP = 'private-registry';
    private const CONTAINER_REGISTRY = 'registry';
    private const CONTAINER_AUTH_VOLUME = 'auth-credentials';
    private const CONTAINER_IMAGE_VOLUME = 'images-storage';

    public function __construct(
        private string $registryImageName,
        private string $registryCpuRequests,
        private string $registryMemoryRequests,
        private string $registryCpuLimits,
        private string $registryMemoryLimits,
        private string $tlsSecretName,
        private string $registryUrl,
        private string $clusterIssuer,
        private DatesService $datesService,
        private bool $preferRealDate,
        private string $ingressClass,
    ) {
    }

    private function createRegistryReplication(
        string $namespace,
        string $name,
        string $authSecret,
        string $persistentVolumeClaimName
    ): Deployment {
        return new Deployment([
            'metadata' => [
                'name' => $name . self::REPLICATION_SUFFIX,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name . self::REPLICATION_SUFFIX,
                    'group' => self::LABEL_GROUP,
                ],
            ],
            'spec' => [
                'replicas' => 1 ,
                'selector' => [
                    'matchLabels' => [
                        'name' => $name,
                    ],
                ],
                'template' => [
                    'metadata' => [
                        'labels' => [
                            'name' => $name,
                            'group' => self::LABEL_GROUP,
                        ],
                    ],
                    'spec' => [
                        'containers' => [
                            [
                                'name' => self::CONTAINER_REGISTRY,
                                'image' => $this->registryImageName,
                                'ports' => [
                                    ['containerPort' => 5000],
                                ],
                                'volumeMounts' => [
                                    [
                                        'name' => self::CONTAINER_AUTH_VOLUME,
                                        'mountPath' => '/auth',
                                        'readOnly' => true,
                                    ],
                                    [
                                        'name' => self::CONTAINER_IMAGE_VOLUME,
                                        'mountPath' => '/var/lib/registry',
                                        'readOnly' => false,
                                    ],
                                ],
                                'env' => [
                                    [
                                        'name' => 'REGISTRY_AUTH',
                                        'value' => 'htpasswd',
                                    ],
                                    [
                                        'name' => 'REGISTRY_AUTH_HTPASSWD_PATH',
                                        'value' => '/auth/htpasswd',
                                    ],
                                    [
                                        'name' => 'REGISTRY_AUTH_HTPASSWD_REALM',
                                        'value' => ucfirst($namespace) . ' Private Registry',
                                    ],
                                ],
                                'resources' => [
                                    'requests' => [
                                        'cpu' => $this->registryCpuRequests,
                                        'memory' => $this->registryMemoryRequests
                                    ],
                                    'limits' => [
                                        'cpu' => $this->registryCpuLimits,
                                        'memory' => $this->registryMemoryLimits
                                    ],
                                ]
                            ],
                        ],
                        'volumes' => [
                            [
                                'name' => self::CONTAINER_AUTH_VOLUME,
                                'secret' => [
                                    'secretName' => $authSecret
                                ],
                            ],
                            [
                                'name' => self::CONTAINER_IMAGE_VOLUME,
                                'persistentVolumeClaim' => [
                                    'claimName' => $persistentVolumeClaimName
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function createRegistryService(
        string $namespace,
        string $name,
        string $podName
    ): Service {
        return new Service([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                    'group' => self::LABEL_GROUP,
                ],
            ],
            'spec' => [
                'selector' => [
                    'name' => $podName,
                ],
                'type' => 'ClusterIP',
                'ports' => [
                    [
                        'name' => 'docker-registry',
                        'protocol' => 'TCP',
                        'port' => 5000,
                        'targetPort' => 5000,
                    ]
                ],
            ],
        ]);
    }

    private function createRegistryIngress(
        string $namespace,
        string $name,
        string $url,
        string $serviceName,
        string $tlsSecretName
    ): Ingress {
        return new Ingress([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                    'group' => self::LABEL_GROUP,
                ],
                'annotations' => [
                    'kubernetes.io/ingress.class' => $this->ingressClass,
                    'cert-manager.io/cluster-issuer' => $this->clusterIssuer,
                    'nginx.ingress.kubernetes.io/proxy-body-size' => "0",
                ],
            ],
            'spec' => [
                'tls' => [
                    [
                        'hosts' => [$url],
                        'secretName' => $tlsSecretName,
                    ]
                ],
                'rules' => [
                    [
                        'host' => $url,
                        'http' => [
                            'paths' => [
                                [
                                    'path' => '/',
                                    'pathType' => 'Prefix',
                                    'backend' => [
                                        'service' => [
                                            'name' => $serviceName,
                                            'port' => [
                                                'number' => 5000,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
         ]);
    }

    private function createRegistryAuthSecret(
        string $namespace,
        string $name,
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
                'htpasswd' => base64_encode(
                    $username . ':' . password_hash($password, PASSWORD_BCRYPT)
                )
            ],
            'type' => 'Opaque',
        ]);
    }

    private function createPassword(string $accountNamespace): string
    {
        return hash("sha256", random_int(1000000, 9999999) . $accountNamespace);
    }

    private function configureRegistry(
        ManagerInterface $manager,
        string $kubeNamespace,
        string $accountNamespace,
        string $persistentVolumeClaimName,
        AccountHistory $accountHistory,
        KubernetesClient $client,
        ClusterCatalog $clusterCatalog,
    ): void {
        $username = $accountNamespace;
        $password = $this->createPassword($accountNamespace);

        $registryUrl = $accountNamespace . $this->registryUrl;
        $podName = $accountNamespace . '-registry-pod';
        $serviceName = $accountNamespace . '-registry-service';
        $ingressName = $accountNamespace . '-registry-ingress';
        $dockerConfigSecretName = $accountNamespace . '-docker-config';
        $authSecretName = $accountNamespace . '-registry-auth-secret';

        $authSecret = $this->createRegistryAuthSecret(
            $kubeNamespace,
            $authSecretName,
            $username,
            $password
        );

        $this->insertModel($client->secrets(), $authSecret, true);

        $replication = $this->createRegistryReplication(
            $kubeNamespace,
            $podName,
            $authSecretName,
            $persistentVolumeClaimName
        );

        $podsModel = $client->pods();
        /** @var array<int, Model> $existantPods */
        $existantPods = $podsModel
            ->setLabelSelector(
                [
                    'group' => self::LABEL_GROUP,
                ],
            )
            ->find()
            ->all();

        $this->insertModel($client->deployments(), $replication, true);

        if (!empty($existantPods)) {
            foreach ($existantPods as $pod) {
                $podsModel->delete($pod);
            }
        }

        $service = $this->createRegistryService(
            $kubeNamespace,
            $serviceName,
            $podName
        );

        $this->insertModel($client->services(), $service, true);

        $ingress = $this->createRegistryIngress(
            $kubeNamespace,
            $ingressName,
            $registryUrl,
            $serviceName,
            $accountNamespace . '-' . $this->tlsSecretName
        );

        $this->insertModel($client->ingresses(), $ingress, true);

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $registryUrl, $username) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.registry_account',
                    $dateTime,
                    false,
                    [
                        'registry_url' => $registryUrl,
                        'registry_account_name' => $username,
                    ]
                );
            },
            $this->preferRealDate,
        );

        $manager->updateWorkPlan([
            'registryUrl' => $registryUrl,
            'registryAccountName' => $username,
            'registryPassword' => $password,
            'registryConfigName' => $dockerConfigSecretName,
        ]);
    }

    public function __invoke(
        ManagerInterface $manager,
        string $kubeNamespace,
        string $accountNamespace,
        AccountHistory $accountHistory,
        ClusterCatalog $clusterCatalog,
        string $persistentVolumeClaimName,
    ): self {
        $clusterRegistry = $clusterCatalog->getClusterForRegistry();
        $client = $clusterRegistry->getKubernetesRegistryClient();
        $client->setNamespace($kubeNamespace);

        try {
            $this->configureRegistry(
                manager: $manager,
                kubeNamespace: $kubeNamespace,
                accountNamespace: $accountNamespace,
                persistentVolumeClaimName: $persistentVolumeClaimName,
                accountHistory: $accountHistory,
                client: $client,
                clusterCatalog: $clusterCatalog,
            );
        } catch (Throwable $error) {
            $manager->error($error);
        }

        return $this;
    }
}
