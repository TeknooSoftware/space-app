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

namespace Teknoo\Space\Tests\Behat;

use Teknoo\East\Paas\Object\AccountQuota;
use Teknoo\Space\Object\Persisted\AccountRegistry;

use function json_decode;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class ManifestGenerator
{
    /**
     * @param AccountQuota[] $quotas
     */
    public function registryCreation(string $name): string
    {
        $json = <<<"EOF"
{
    "namespaces": [
        {
            "kind": "Namespace",
            "apiVersion": "v1",
            "metadata": {
                "name": "space-registry-$name",
                "labels": {
                    "name": "space-registry-$name",
                    "id": "#ID#"
                }
            }
        }
    ],
    "namespaces\/space-registry-$name\/persistentvolumeclaims": [
        {
            "kind": "PersistentVolumeClaim",
            "apiVersion": "v1",
            "metadata": {
                "name": "$name-pvc",
                "namespace": "space-registry-$name",
                "labels": {
                    "name": "$name-pvc"
                }
            },
            "spec": {
                "accessModes": [
                    "ReadWriteOnce"
                ],
                "storageClassName": "nfs.csi.k8s.io",
                "resources": {
                    "requests": {
                        "storage": "3Gi"
                    }
                }
            }
        }
    ],
    "namespaces\/space-registry-$name\/secrets": [
        {
            "kind": "Secret",
            "apiVersion": "v1",
            "metadata": {
                "name": "$name-registry-auth-secret",
                "namespace": "space-registry-$name",
                "labels": {
                    "name": "$name-registry-auth-secret",
                    "group": "private-registry"
                }
            },
            "data": {
                "htpasswd": "==="
            },
            "type": "Opaque"
        }
    ],
    "namespaces\/space-registry-$name\/deployments": [
        {
            "kind": "Deployment",
            "apiVersion": "apps\/v1",
            "metadata": {
                "name": "$name-registry-pod-replication-dplmt",
                "namespace": "space-registry-$name",
                "labels": {
                    "name": "$name-registry-pod-replication-dplmt",
                    "group": "private-registry"
                }
            },
            "spec": {
                "replicas": 1,
                "selector": {
                    "matchLabels": {
                        "name": "$name-registry-pod"
                    }
                },
                "template": {
                    "metadata": {
                        "labels": {
                            "name": "$name-registry-pod",
                            "group": "private-registry"
                        }
                    },
                    "spec": {
                        "containers": [
                            {
                                "name": "registry",
                                "image": "registry:latest",
                                "ports": [
                                    {
                                        "containerPort": 5000
                                    }
                                ],
                                "volumeMounts": [
                                    {
                                        "name": "auth-credentials",
                                        "mountPath": "\/auth",
                                        "readOnly": true
                                    },
                                    {
                                        "name": "images-storage",
                                        "mountPath": "\/var\/lib\/registry",
                                        "readOnly": false
                                    }
                                ],
                                "env": [
                                    {
                                        "name": "REGISTRY_AUTH",
                                        "value": "htpasswd"
                                    },
                                    {
                                        "name": "REGISTRY_AUTH_HTPASSWD_PATH",
                                        "value": "\/auth\/htpasswd"
                                    },
                                    {
                                        "name": "REGISTRY_AUTH_HTPASSWD_REALM",
                                        "value": "Space-registry-$name Private Registry"
                                    }
                                ],
                                "resources": {
                                    "requests": {
                                        "cpu": "100m",
                                        "memory": "32Mi"
                                    },
                                    "limits": {
                                        "cpu": "300m",
                                        "memory": "512Mi"
                                    }
                                }
                            }
                        ],
                        "volumes": [
                            {
                                "name": "auth-credentials",
                                "secret": {
                                    "secretName": "$name-registry-auth-secret"
                                }
                            },
                            {
                                "name": "images-storage",
                                "persistentVolumeClaim": {
                                    "claimName": "$name-pvc"
                                }
                            }
                        ]
                    }
                }
            }
        }
    ],
    "namespaces\/space-registry-$name\/services": [
        {
            "kind": "Service",
            "apiVersion": "v1",
            "metadata": {
                "name": "$name-registry-service",
                "namespace": "space-registry-$name",
                "labels": {
                    "name": "$name-registry-service",
                    "group": "private-registry"
                }
            },
            "spec": {
                "selector": {
                    "name": "$name-registry-pod"
                },
                "type": "ClusterIP",
                "ports": [
                    {
                        "name": "docker-registry",
                        "protocol": "TCP",
                        "port": 5000,
                        "targetPort": 5000
                    }
                ]
            }
        }
    ],
    "namespaces\/space-registry-$name\/ingresses": [
        {
            "kind": "Ingress",
            "apiVersion": "networking.k8s.io\/v1",
            "metadata": {
                "name": "$name-registry-ingress",
                "namespace": "space-registry-$name",
                "labels": {
                    "name": "$name-registry-ingress",
                    "group": "private-registry"
                },
                "annotations": {
                    "kubernetes.io\/ingress.class": "public",
                    "cert-manager.io\/cluster-issuer": "lets-encrypt",
                    "nginx.ingress.kubernetes.io\/proxy-body-size": "0"
                }
            },
            "spec": {
                "tls": [
                    {
                        "hosts": [
                            "$name.registry.kubernetes.localhost"
                        ],
                        "secretName": "$name-registry-certs"
                    }
                ],
                "rules": [
                    {
                        "host": "$name.registry.kubernetes.localhost",
                        "http": {
                            "paths": [
                                {
                                    "path": "\/",
                                    "pathType": "Prefix",
                                    "backend": {
                                        "service": {
                                            "name": "$name-registry-service",
                                            "port": {
                                                "number": 5000
                                            }
                                        }
                                    }
                                }
                            ]
                        }
                    }
                ]
            }
        }
    ]
}
EOF;

        return json_encode(
            value: json_decode(
                json: $json,
                associative: true
            ),
            flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
        );
    }

    /**
     * @param AccountQuota[] $accountQuotas
     */
    public function namespaceCreation(
        string $name,
        string $namespace,
        array $accountQuotas,
        AccountRegistry $registry,
    ): string {
        $quotas = '';
        if (!empty($accountQuotas)) {
            $quotasHards = [];
            foreach ($accountQuotas as $accountQuota) {
                $quotasHards["requests.{$accountQuota->type}"] = $accountQuota->requires;
                $quotasHards["limits.{$accountQuota->type}"] = $accountQuota->capacity;
            }

            $quotas = ', "namespaces\/space-client-' . $namespace . '\/resourcequotas":' . json_encode([[
                'kind' => 'ResourceQuota',
                'apiVersion' => 'v1',
                'metadata' => [
                    'name' => $name . '-quota',
                    'namespace' => 'space-client-' . $namespace,
                    'labels' => [
                        'name' => $name . '-quota',
                    ],
                ],
                'spec' => [
                    'hard' => $quotasHards,
                ],
            ]]);
        }

        $json = <<<"EOF"
{
    "namespaces": [
        {
            "kind": "Namespace",
            "apiVersion": "v1",
            "metadata": {
                "name": "space-client-$namespace",
                "labels": {
                    "name": "space-client-$namespace",
                    "id": "#ID#"
                }
            }
        }
    ],
    "namespaces\/space-client-$namespace\/serviceaccounts": [
        {
            "kind": "ServiceAccount",
            "apiVersion": "v1",
            "metadata": {
                "name": "$name-account",
                "namespace": "space-client-$namespace",
                "labels": {
                    "name": "$name-account"
                }
            }
        }
    ]$quotas,
    "namespaces\/space-client-$namespace\/roles": [
        {
            "kind": "Role",
            "apiVersion": "rbac.authorization.k8s.io\/v1",
            "metadata": {
                "name": "$name-role",
                "namespace": "space-client-$namespace",
                "labels": {
                    "name": "$name-role"
                }
            },
            "rules": [
                {
                    "apiGroups": [
                        ""
                    ],
                    "resources": [
                        "pods",
                        "pods\/log",
                        "pods\/exec",
                        "services",
                        "secrets",
                        "replicationcontrollers",
                        "persistentvolumeclaims",
                        "configmaps"
                    ],
                    "verbs": [
                        "get",
                        "watch",
                        "list",
                        "create",
                        "update",
                        "patch",
                        "delete",
                        "deletecollection"
                    ]
                },
                {
                    "apiGroups": [
                        "apps"
                    ],
                    "resources": [
                        "deployments",
                        "replicasets",
                        "statefulsets"
                    ],
                    "verbs": [
                        "get",
                        "watch",
                        "list",
                        "create",
                        "update",
                        "patch",
                        "delete",
                        "deletecollection"
                    ]
                },
                {
                    "apiGroups": [
                        "networking.k8s.io"
                    ],
                    "resources": [
                        "ingresses"
                    ],
                    "verbs": [
                        "get",
                        "watch",
                        "list",
                        "create",
                        "update",
                        "patch",
                        "delete",
                        "deletecollection"
                    ]
                }
            ]
        }
    ],
    "clusterroles": [
        {
            "kind": "ClusterRole",
            "apiVersion": "rbac.authorization.k8s.io\/v1",
            "metadata": {
                "name": "$name-cluster-role",
                "labels": {
                    "name": "$name-cluster-role"
                }
            },
            "rules": [
                {
                    "apiGroups": [
                        ""
                    ],
                    "resources": [
                        "namespaces"
                    ],
                    "verbs": [
                        "get",
                        "watch"
                    ]
                }
            ]
        }
    ],
    "clusterrolebindings": [
        {
            "kind": "ClusterRoleBinding",
            "apiVersion": "rbac.authorization.k8s.io\/v1",
            "metadata": {
                "name": "$name-cluster-role-binding",
                "labels": {
                    "name": "$name-cluster-role-binding"
                }
            },
            "subjects": [
                {
                    "kind": "ServiceAccount",
                    "name": "$name-account",
                    "namespace": "space-client-$namespace",
                    "apiGroup": ""
                }
            ],
            "roleRef": {
                "kind": "ClusterRole",
                "name": "$name-cluster-role",
                "apiGroup": "rbac.authorization.k8s.io"
            }
        }
    ],
    "namespaces\/space-client-$namespace\/rolebindings": [
        {
            "kind": "RoleBinding",
            "apiVersion": "rbac.authorization.k8s.io\/v1",
            "metadata": {
                "name": "$name-role-binding",
                "namespace": "space-client-$namespace",
                "labels": {
                    "name": "$name-role-binding"
                }
            },
            "subjects": [
                {
                    "kind": "ServiceAccount",
                    "name": "$name-account",
                    "namespace": "space-client-$namespace",
                    "apiGroup": ""
                }
            ],
            "roleRef": {
                "kind": "Role",
                "name": "$name-role",
                "namespace": "space-client-$namespace",
                "apiGroup": "rbac.authorization.k8s.io"
            }
        }
    ],
    "namespaces\/space-client-$namespace\/secrets": [
        {
            "kind": "Secret",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$registry->getRegistryConfigName()}",
                "namespace": "space-client-$namespace",
                "labels": {
                    "name": "{$registry->getRegistryConfigName()}",
                    "group": "private-registry"
                }
            },
            "data": {
                ".dockerconfigjson": "==="
            },
            "type": "kubernetes.io\/dockerconfigjson"
        },
        {
            "kind": "Secret",
            "apiVersion": "v1",
            "metadata": {
                "name": "$name-secret",
                "namespace": "space-client-$namespace",
                "labels": {
                    "name": "$name-secret"
                },
                "annotations": {
                    "kubernetes.io\/service-account.name": "$name-account"
                }
            },
            "type": "kubernetes.io\/service-account-token"
        }
    ]
}
EOF;

        return json_encode(
            value: json_decode(
                json: $json,
                associative: true
            ),
            flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
        );
    }

    /**
     * @param array<string, <string> $namespaces
     * @param AccountQuota[] $accountQuotas
     */
    public function quotaRefresh(
        string $accountNs,
        array $namespaces,
        array $accountQuotas,
    ): string {
        $quotas = [];

        $quotasHards = [];
        foreach ($accountQuotas as $accountQuota) {
            $quotasHards["requests.{$accountQuota->type}"] = $accountQuota->requires;
            $quotasHards["limits.{$accountQuota->type}"] = $accountQuota->capacity;
        }

        foreach ($namespaces as $namespace) {
            $quotas['namespaces/' . $namespace . '/resourcequotas'] = [
                [
                    'kind' => 'ResourceQuota',
                    'apiVersion' => 'v1',
                    'metadata' => [
                        'name' => $accountNs . '-quota',
                        'namespace' => $namespace,
                        'labels' => [
                            'name' => $accountNs . '-quota',
                        ],
                    ],
                    'spec' => [
                        'hard' => $quotasHards,
                    ],
                ]
            ];
        }

        return json_encode(
            value: $quotas,
            flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
        );
    }

    public function fullDeployment(
        string $projectPrefix,
        string $jobId,
        string $hncSuffix,
        bool $useHnc,
        string $quotaMode,
        string $defaultsMods,
    ): string {
        if (!empty($projectPrefix)) {
            $projectPrefix .= '-';
        }
        $nameHnc = trim($hncSuffix, '-');

        $hncManifest = '';
        if ($useHnc) {
            $hncManifest = <<<"EOF"
"namespaces/space-behat-my-company-prod/subnamespacesanchors": [
        {
            "kind": "SubnamespaceAnchor",
            "apiVersion": "hnc.x-k8s.io/v1",
            "metadata": {
                "name": "{$nameHnc}",
                "namespace": "space-behat-my-company-prod",
                "labels": {
                    "name": "space-behat-my-company-prod{$hncSuffix}"
                }
            }
        }
    ],
    
EOF;
        }

        $storageClass = match ($defaultsMods) {
            'cluster' => 'cluster-default-behat-provider',
            default => 'nfs.csi.k8s.io',
        };

        $imagePullSecrets = match ($defaultsMods) {
            'generic', 'cluster' => 'oci-registry-behat',
            default => 'my-company-docker-config',
        };

        $secret = base64_encode($projectPrefix . 'world');

        $prefixResource = ', "resources": ';
        $automaticResources = $prefixResource . json_encode(
            [
                'requests' => [
                    'cpu' => '200m',
                    'memory' => '20.480Mi',
                ],
                'limits' => [
                    'cpu' => '1.600',
                    'memory' => '163.840Mi',
                ],
            ],
        );

        $phpRunResources = match ($quotaMode) {
            'automatic' => $automaticResources,
            'partial' => $prefixResource . json_encode(
                [
                    'requests' => [
                        'cpu' => '68m',
                        'memory' => '9.600Mi',
                    ],
                    'limits' => [
                        'cpu' => '561m',
                        'memory' => '80Mi',
                    ],
                ],
            ),
            'full' => $prefixResource . json_encode(
                [
                    'requests' => [
                        'cpu' => '200m',
                        'memory' => '64Mi',
                    ],
                    'limits' => [
                        'cpu' => '500m',
                        'memory' => '96Mi',
                    ],
                ],
            ),
            default => ''
        };

        $shellResources = match ($quotaMode) {
            'automatic' => $automaticResources,
            'partial' => $prefixResource . json_encode(
                [
                    'requests' => [
                        'cpu' => '100m',
                        'memory' => '9.600Mi',
                    ],
                    'limits' => [
                        'cpu' => '100m',
                        'memory' => '80Mi',
                    ],
                ],
            ),
            'full' => $prefixResource . json_encode(
                [
                    'requests' => [
                        'cpu' => '100m',
                        'memory' => '32Mi',
                    ],
                    'limits' => [
                        'cpu' => '100m',
                        'memory' => '32Mi',
                    ],
                ],
            ),
            default => ''
        };

        $nginxResources = match ($quotaMode) {
            'automatic' => $automaticResources,
            'partial' => $prefixResource . json_encode(
                [
                    'requests' => [
                        'cpu' => '68m',
                        'memory' => '9.600Mi',
                    ],
                    'limits' => [
                        'cpu' => '561m',
                        'memory' => '80Mi',
                    ],
                ],
            ),
            'full' => $prefixResource . json_encode(
                [
                    'requests' => [
                        'cpu' => '200m',
                        'memory' => '64Mi',
                    ],
                    'limits' => [
                        'cpu' => '200m',
                        'memory' => '64Mi',
                    ],
                ],
            ),
            default => ''
        };

        $wafResources = match ($quotaMode) {
            'automatic' => $automaticResources,
            'partial', 'full' => $prefixResource . json_encode(
                [
                    'requests' => [
                        'cpu' => '100m',
                        'memory' => '64Mi',
                    ],
                    'limits' => [
                        'cpu' => '100m',
                        'memory' => '64Mi',
                    ],
                ],
            ),
            default => ''
        };

        $blackfireResources = match ($quotaMode) {
            'automatic' => $automaticResources,
            'partial', 'full' => $prefixResource . json_encode(
                [
                    'requests' => [
                        'cpu' => '100m',
                        'memory' => '128Mi',
                    ],
                    'limits' => [
                        'cpu' => '100m',
                        'memory' => '128Mi',
                    ],
                ],
            ),
            default => ''
        };

        $json = <<<"EOF"
{
    $hncManifest"namespaces/space-behat-my-company-prod{$hncSuffix}/secrets": [
        {
            "kind": "Secret",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}map-vault-secret",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}map-vault"
                }
            },
            "type": "Opaque",
            "data": {
                "key1": "dmFsdWUx",
                "key2": "QkFS"
            }
        },
        {
            "kind": "Secret",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}map-vault2-secret",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}map-vault2"
                }
            },
            "type": "Opaque",
            "data": {
                "hello": "$secret"
            }
        },
        {
            "kind": "Secret",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}volume-vault-secret",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}volume-vault"
                }
            },
            "type": "foo",
            "data": {
                "foo": "YmFy",
                "bar": "Zm9v"
            }
        }
    ],
    "namespaces/space-behat-my-company-prod{$hncSuffix}/configmaps": [
        {
            "kind": "ConfigMap",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}map1-map",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}map1"
                }
            },
            "data": {
                "key1": "value1",
                "key2": "BAR"
            }
        },
        {
            "kind": "ConfigMap",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}map2-map",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}map2"
                }
            },
            "data": {
                "foo": "bar",
                "bar": "{$projectPrefix}foo"
            }
        }
    ],
    "namespaces/space-behat-my-company-prod{$hncSuffix}/persistentvolumeclaims": [
        {
            "kind": "PersistentVolumeClaim",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}data",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}data"
                }
            },
            "spec": {
                "accessModes": [
                    "ReadWriteOnce"
                ],
                "storageClassName": "$storageClass",
                "resources": {
                    "requests": {
                        "storage": "3Gi"
                    }
                }
            }
        },
        {
            "kind": "PersistentVolumeClaim",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}data-replicated",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}data-replicated"
                }
            },
            "spec": {
                "accessModes": [
                    "ReadWriteOnce"
                ],
                "storageClassName": "replicated-provider",
                "resources": {
                    "requests": {
                        "storage": "3Gi"
                    }
                }
            }
        }
    ],
    "namespaces/space-behat-my-company-prod{$hncSuffix}/deployments": [
        {
            "kind": "Deployment",
            "apiVersion": "apps/v1",
            "metadata": {
                "name": "{$projectPrefix}shell-dplmt",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}shell"
                },
                "annotations": {
                    "teknoo.east.paas.version": "v1"
                }
            },
            "spec": {
                "replicas": 1,
                "strategy": {
                    "type": "RollingUpdate",
                    "rollingUpdate": {
                        "maxSurge": 1,
                        "maxUnavailable": 0
                    }
                },
                "selector": {
                    "matchLabels": {
                        "name": "{$projectPrefix}shell"
                    }
                },
                "template": {
                    "metadata": {
                        "name": "{$projectPrefix}shell-pod",
                        "namespace": "space-behat-my-company-prod{$hncSuffix}",
                        "labels": {
                            "name": "{$projectPrefix}shell",
                            "vname": "{$projectPrefix}shell-v1"
                        }
                    },
                    "spec": {
                        "hostAliases": [
                            {
                                "hostnames": [
                                    "sleep"
                                ],
                                "ip": "127.0.0.1"
                            }
                        ],
                        "containers": [
                            {
                                "name": "sleep",
                                "image": "registry.hub.docker.com/bash:alpine",
                                "imagePullPolicy": "Always",
                                "ports": []$shellResources
                            }
                        ],
                        "imagePullSecrets": [
                            {
                                "name": "$imagePullSecrets"
                            }
                        ]
                    }
                }
            }
        },
        {
            "kind": "Deployment",
            "apiVersion": "apps/v1",
            "metadata": {
                "name": "{$projectPrefix}demo-dplmt",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}demo"
                },
                "annotations": {
                    "teknoo.east.paas.version": "v1"
                }
            },
            "spec": {
                "replicas": 1,
                "strategy": {
                    "type": "Recreate"
                },
                "selector": {
                    "matchLabels": {
                        "name": "{$projectPrefix}demo"
                    }
                },
                "template": {
                    "metadata": {
                        "name": "{$projectPrefix}demo-pod",
                        "namespace": "space-behat-my-company-prod{$hncSuffix}",
                        "labels": {
                            "name": "{$projectPrefix}demo",
                            "vname": "{$projectPrefix}demo-v1"
                        }
                    },
                    "spec": {
                        "hostAliases": [
                            {
                                "hostnames": [
                                    "nginx",
                                    "waf",
                                    "blackfire"
                                ],
                                "ip": "127.0.0.1"
                            }
                        ],
                        "containers": [
                            {
                                "name": "nginx",
                                "image": "my-company.registry.demo.teknoo.space/nginx:alpine-prod",
                                "imagePullPolicy": "Always",
                                "ports": [
                                    {
                                        "containerPort": 8080
                                    },
                                    {
                                        "containerPort": 8181
                                    }
                                ],
                                "livenessProbe": {
                                    "initialDelaySeconds": 10,
                                    "periodSeconds": 30,
                                    "httpGet": {
                                        "path": "/status",
                                        "port": 8080,
                                        "scheme": "HTTPS"
                                    },
                                    "successThreshold": 3,
                                    "failureThreshold": 2
                                }$nginxResources
                            },
                            {
                                "name": "waf",
                                "image": "registry.hub.docker.com/library/waf:alpine",
                                "imagePullPolicy": "Always",
                                "ports": [
                                    {
                                        "containerPort": 8181
                                    }
                                ],
                                "livenessProbe": {
                                    "initialDelaySeconds": 10,
                                    "periodSeconds": 30,
                                    "tcpSocket": {
                                        "port": 8181
                                    },
                                    "successThreshold": 1,
                                    "failureThreshold": 1
                                }$wafResources
                            },
                            {
                                "name": "blackfire",
                                "image": "blackfire/blackfire:2-prod",
                                "imagePullPolicy": "Always",
                                "ports": [
                                    {
                                        "containerPort": 8307
                                    }
                                ],
                                "env": [
                                    {
                                        "name": "BLACKFIRE_SERVER_ID",
                                        "value": "foo"
                                    },
                                    {
                                        "name": "BLACKFIRE_SERVER_TOKEN",
                                        "value": "bar"
                                    }
                                ]$blackfireResources
                            }
                        ],
                        "imagePullSecrets": [
                            {
                                "name": "$imagePullSecrets"
                            }
                        ],
                        "securityContext": {
                            "fsGroup": 1000
                        }
                    }
                }
            }
        }
    ],
    "namespaces/space-behat-my-company-prod{$hncSuffix}/statefulsets": [
        {
            "kind": "StatefulSet",
            "apiVersion": "apps/v1",
            "metadata": {
                "name": "{$projectPrefix}php-pods-sfset",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}php-pods"
                },
                "annotations": {
                    "teknoo.east.paas.version": "v1"
                }
            },
            "spec": {
                "replicas": 2,
                "serviceName": "{$projectPrefix}php-pods",
                "strategy": {
                    "type": "RollingUpdate",
                    "rollingUpdate": {
                        "maxSurge": 2,
                        "maxUnavailable": 1
                    }
                },
                "selector": {
                    "matchLabels": {
                        "name": "{$projectPrefix}php-pods"
                    }
                },
                "template": {
                    "metadata": {
                        "name": "{$projectPrefix}php-pods-pod",
                        "namespace": "space-behat-my-company-prod{$hncSuffix}",
                        "labels": {
                            "name": "{$projectPrefix}php-pods",
                            "vname": "{$projectPrefix}php-pods-v1"
                        }
                    },
                    "spec": {
                        "hostAliases": [
                            {
                                "hostnames": [
                                    "php-run"
                                ],
                                "ip": "127.0.0.1"
                            }
                        ],
                        "containers": [
                            {
                                "name": "php-run",
                                "image": "my-company.registry.demo.teknoo.space/php-run:7.4-prod",
                                "imagePullPolicy": "Always",
                                "ports": [
                                    {
                                        "containerPort": 8080
                                    }
                                ],
                                "envFrom": [
                                    {
                                        "secretRef": {
                                            "name": "{$projectPrefix}map-vault2-secret"
                                        }
                                    },
                                    {
                                        "configMapRef": {
                                            "name": "{$projectPrefix}map2-map"
                                        }
                                    }
                                ],
                                "env": [
                                    {
                                        "name": "SERVER_SCRIPT",
                                        "value": "/opt/app/src/server.php"
                                    },
                                    {
                                        "name": "KEY1",
                                        "valueFrom": {
                                            "secretKeyRef": {
                                                "name": "{$projectPrefix}map-vault-secret",
                                                "key": "key1"
                                            }
                                        }
                                    },
                                    {
                                        "name": "KEY2",
                                        "valueFrom": {
                                            "secretKeyRef": {
                                                "name": "{$projectPrefix}map-vault-secret",
                                                "key": "key2"
                                            }
                                        }
                                    },
                                    {
                                        "name": "KEY0",
                                        "valueFrom": {
                                            "configMapKeyRef": {
                                                "name": "{$projectPrefix}map1-map",
                                                "key": "key0"
                                            }
                                        }
                                    }
                                ],
                                "volumeMounts": [
                                    {
                                        "name": "extra-{$jobId}-volume",
                                        "mountPath": "/opt/extra",
                                        "readOnly": true
                                    },
                                    {
                                        "name": "data-volume",
                                        "mountPath": "/opt/data",
                                        "readOnly": false
                                    },
                                    {
                                        "name": "data-replicated-volume",
                                        "mountPath": "/opt/data-replicated",
                                        "readOnly": false
                                    },
                                    {
                                        "name": "map-volume",
                                        "mountPath": "/map",
                                        "readOnly": false
                                    },
                                    {
                                        "name": "vault-volume",
                                        "mountPath": "/vault",
                                        "readOnly": false
                                    }
                                ],
                                "livenessProbe": {
                                    "initialDelaySeconds": 10,
                                    "periodSeconds": 30,
                                    "exec": {
                                        "command": [
                                            "ps",
                                            "aux",
                                            "php"
                                        ]
                                    },
                                    "successThreshold": 1,
                                    "failureThreshold": 1
                                }$phpRunResources
                            }
                        ],
                        "imagePullSecrets": [
                            {
                                "name": "$imagePullSecrets"
                            }
                        ],
                        "affinity": {
                            "nodeAffinity": {
                                "requiredDuringSchedulingIgnoredDuringExecution": {
                                    "nodeSelectorTerms": [
                                        {
                                            "matchExpressions": [
                                                {
                                                    "key": "paas.east.teknoo.net/x86_64",
                                                    "operator": "Exists"
                                                },
                                                {
                                                    "key": "paas.east.teknoo.net/avx",
                                                    "operator": "Exists"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            }
                        },
                        "initContainers": [
                            {
                                "name": "extra-{$jobId}",
                                "image": "my-company.registry.demo.teknoo.space/extra-{$jobId}",
                                "imagePullPolicy": "Always",
                                "volumeMounts": [
                                    {
                                        "name": "extra-{$jobId}-volume",
                                        "mountPath": "/opt/extra",
                                        "readOnly": false
                                    }
                                ],
                                "env": [
                                    {
                                        "name": "MOUNT_PATH",
                                        "value": "/opt/extra"
                                    }
                                ]$phpRunResources
                            }
                        ],
                        "volumes": [
                            {
                                "name": "extra-{$jobId}-volume",
                                "emptyDir": []
                            },
                            {
                                "name": "data-volume",
                                "persistentVolumeClaim": {
                                    "claimName": "{$projectPrefix}data"
                                }
                            },
                            {
                                "name": "data-replicated-volume",
                                "persistentVolumeClaim": {
                                    "claimName": "{$projectPrefix}data-replicated"
                                }
                            },
                            {
                                "name": "map-volume",
                                "configMap": {
                                    "name": "{$projectPrefix}map2-map"
                                }
                            },
                            {
                                "name": "vault-volume",
                                "secret": {
                                    "secretName": "{$projectPrefix}volume-vault-secret"
                                }
                            }
                        ]
                    }
                }
            }
        }
    ],
    "namespaces/space-behat-my-company-prod{$hncSuffix}/services": [
        {
            "kind": "Service",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}php-service",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}php-service"
                }
            },
            "spec": {
                "selector": {
                    "name": "{$projectPrefix}php-pods"
                },
                "type": "LoadBalancer",
                "ports": [
                    {
                        "name": "php-service-9876",
                        "protocol": "TCP",
                        "port": 9876,
                        "targetPort": 8080
                    }
                ]
            }
        },
        {
            "kind": "Service",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}demo",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}demo"
                }
            },
            "spec": {
                "selector": {
                    "name": "{$projectPrefix}demo"
                },
                "type": "ClusterIP",
                "ports": [
                    {
                        "name": "demo-8080",
                        "protocol": "TCP",
                        "port": 8080,
                        "targetPort": 8080
                    },
                    {
                        "name": "demo-8181",
                        "protocol": "TCP",
                        "port": 8181,
                        "targetPort": 8181
                    }
                ]
            }
        }
    ],
    "namespaces/space-behat-my-company-prod{$hncSuffix}/ingresses": [
        {
            "kind": "Ingress",
            "apiVersion": "networking.k8s.io/v1",
            "metadata": {
                "name": "{$projectPrefix}demo-ingress",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}demo"
                },
                "annotations": {
                    "foo2": "bar",
                    "cert-manager.io/cluster-issuer": "lets-encrypt",
                    "kubernetes.io/ingress.class": "public"
                }
            },
            "spec": {
                "rules": [
                    {
                        "host": "demo-paas.teknoo.software",
                        "http": {
                            "paths": [
                                {
                                    "path": "/",
                                    "pathType": "Prefix",
                                    "backend": {
                                        "service": {
                                            "name": "{$projectPrefix}demo",
                                            "port": {
                                                "number": 8080
                                            }
                                        }
                                    }
                                },
                                {
                                    "path": "/php",
                                    "pathType": "Prefix",
                                    "backend": {
                                        "service": {
                                            "name": "{$projectPrefix}php-service",
                                            "port": {
                                                "number": 9876
                                            }
                                        }
                                    }
                                }
                            ]
                        }
                    },{
                        "host": "alias1.demo-paas.teknoo.software",
                        "http": {
                            "paths": [
                                {
                                    "path": "/",
                                    "pathType": "Prefix",
                                    "backend": {
                                        "service": {
                                            "name": "{$projectPrefix}demo",
                                            "port": {
                                                "number": 8080
                                            }
                                        }
                                    }
                                },
                                {
                                    "path": "/php",
                                    "pathType": "Prefix",
                                    "backend": {
                                        "service": {
                                            "name": "{$projectPrefix}php-service",
                                            "port": {
                                                "number": 9876
                                            }
                                        }
                                    }
                                }
                            ]
                        }
                    },{
                        "host": "alias2.demo-paas.teknoo.software",
                        "http": {
                            "paths": [
                                {
                                    "path": "/",
                                    "pathType": "Prefix",
                                    "backend": {
                                        "service": {
                                            "name": "{$projectPrefix}demo",
                                            "port": {
                                                "number": 8080
                                            }
                                        }
                                    }
                                },
                                {
                                    "path": "/php",
                                    "pathType": "Prefix",
                                    "backend": {
                                        "service": {
                                            "name": "{$projectPrefix}php-service",
                                            "port": {
                                                "number": 9876
                                            }
                                        }
                                    }
                                }
                            ]
                        }
                    }
                ],
                "tls": [
                    {
                        "hosts": [
                            "demo-paas.teknoo.software",
                            "alias1.demo-paas.teknoo.software",
                            "alias2.demo-paas.teknoo.software"
                        ],
                        "secretName": "{$projectPrefix}demo-vault-secret"
                    }
                ]
            }
        },
        {
            "kind": "Ingress",
            "apiVersion": "networking.k8s.io/v1",
            "metadata": {
                "name": "{$projectPrefix}demo-secure-ingress",
                "namespace": "space-behat-my-company-prod{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}demo-secure"
                },
                "annotations": {
                    "kubernetes.io/ingress.class": "public",
                    "nginx.ingress.kubernetes.io/backend-protocol": "HTTPS"
                }
            },
            "spec": {
                "rules": [
                    {
                        "host": "demo-secure.teknoo.software",
                        "http": {
                            "paths": [
                                {
                                    "path": "/",
                                    "pathType": "Prefix",
                                    "backend": {
                                        "service": {
                                            "name": "{$projectPrefix}demo",
                                            "port": {
                                                "number": 8181
                                            }
                                        }
                                    }
                                }
                            ]
                        }
                    }
                ],
                "tls": [
                    {
                        "hosts": [
                            "demo-secure.teknoo.software"
                        ],
                        "secretName": "{$projectPrefix}demo-vault-secret"
                    }
                ]
            }
        }
    ]
}
EOF;

        return json_encode(
            value: json_decode(
                json: $json,
                associative: true
            ),
            flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
        );
    }
}
