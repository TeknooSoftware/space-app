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

class ManifestGenerator
{
    public function namespaceCreation(string $name): string
    {
        return <<<"EOF"
{
    "namespaces": [
        {
            "kind": "Namespace",
            "apiVersion": "v1",
            "metadata": {
                "name": "space-client-$name",
                "labels": {
                    "name": "space-client-$name",
                    "id": "#ID#"
                }
            }
        }
    ],
    "namespaces\/space-client-$name\/serviceaccounts": [
        {
            "kind": "ServiceAccount",
            "apiVersion": "v1",
            "metadata": {
                "name": "$name-account",
                "namespace": "space-client-$name",
                "labels": {
                    "name": "$name-account"
                }
            }
        }
    ],
    "namespaces\/space-client-$name\/roles": [
        {
            "kind": "Role",
            "apiVersion": "rbac.authorization.k8s.io\/v1",
            "metadata": {
                "name": "$name-role",
                "namespace": "space-client-$name",
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
                    "namespace": "space-client-$name",
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
    "namespaces\/space-client-$name\/rolebindings": [
        {
            "kind": "RoleBinding",
            "apiVersion": "rbac.authorization.k8s.io\/v1",
            "metadata": {
                "name": "$name-role-binding",
                "namespace": "space-client-$name",
                "labels": {
                    "name": "$name-role-binding"
                }
            },
            "subjects": [
                {
                    "kind": "ServiceAccount",
                    "name": "$name-account",
                    "namespace": "space-client-$name",
                    "apiGroup": ""
                }
            ],
            "roleRef": {
                "kind": "Role",
                "name": "$name-role",
                "namespace": "space-client-$name",
                "apiGroup": "rbac.authorization.k8s.io"
            }
        }
    ],
    "namespaces\/space-client-$name\/secrets": [
        {
            "kind": "Secret",
            "apiVersion": "v1",
            "metadata": {
                "name": "$name-secret",
                "namespace": "space-client-$name",
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
        ;
    }

    public function fullDeployment(
        string $projectPrefix,
        string $jobId,
        string $hncSuffix,
        bool $useHnc,
    ): string {
        if (!empty($projectPrefix)) {
            $projectPrefix .= '-';
        }
        $nameHnc = trim($hncSuffix, '-');

        $hncManifest = '';
        if ($useHnc) {
            $hncManifest = <<<"EOF"
"namespaces/default/subnamespacesanchors": [
        {
            "kind": "SubnamespaceAnchor",
            "apiVersion": "hnc.x-k8s.io/v1",
            "metadata": {
                "name": "{$nameHnc}",
                "namespace": "space-behat-my-comany",
                "labels": {
                    "name": "space-behat-my-comany{$hncSuffix}"
                }
            }
        }
    ],
    
EOF;
        }

        $secret = base64_encode($projectPrefix . 'world');

        return <<<"EOF"
{
    $hncManifest"namespaces/space-behat-my-comany{$hncSuffix}/secrets": [
        {
            "kind": "Secret",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}map-vault-secret",
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
    "namespaces/space-behat-my-comany{$hncSuffix}/configmaps": [
        {
            "kind": "ConfigMap",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}map1-map",
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
    "namespaces/space-behat-my-comany{$hncSuffix}/persistentvolumeclaims": [
        {
            "kind": "PersistentVolumeClaim",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}data",
                "namespace": "space-behat-my-comany{$hncSuffix}",
                "labels": {
                    "name": "{$projectPrefix}data"
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
        },
        {
            "kind": "PersistentVolumeClaim",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}data-replicated",
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
    "namespaces/space-behat-my-comany{$hncSuffix}/deployments": [
        {
            "kind": "Deployment",
            "apiVersion": "apps/v1",
            "metadata": {
                "name": "{$projectPrefix}shell-dplmt",
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
                        "namespace": "space-behat-my-comany{$hncSuffix}",
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
                                "ports": []
                            }
                        ],
                        "imagePullSecrets": [
                            {
                                "name": "my-companydocker-config"
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
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
                        "namespace": "space-behat-my-comany{$hncSuffix}",
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
                                }
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
                                }
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
                                ]
                            }
                        ],
                        "imagePullSecrets": [
                            {
                                "name": "my-companydocker-config"
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
    "namespaces/space-behat-my-comany{$hncSuffix}/statefulsets": [
        {
            "kind": "StatefulSet",
            "apiVersion": "apps/v1",
            "metadata": {
                "name": "{$projectPrefix}php-pods-sfset",
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
                        "namespace": "space-behat-my-comany{$hncSuffix}",
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
                                }
                            }
                        ],
                        "imagePullSecrets": [
                            {
                                "name": "my-companydocker-config"
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
                                ]
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
    "namespaces/space-behat-my-comany{$hncSuffix}/services": [
        {
            "kind": "Service",
            "apiVersion": "v1",
            "metadata": {
                "name": "{$projectPrefix}php-service",
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
    "namespaces/space-behat-my-comany{$hncSuffix}/ingresses": [
        {
            "kind": "Ingress",
            "apiVersion": "networking.k8s.io/v1",
            "metadata": {
                "name": "{$projectPrefix}demo-ingress",
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
                    }
                ],
                "tls": [
                    {
                        "hosts": [
                            "demo-paas.teknoo.software"
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
                "namespace": "space-behat-my-comany{$hncSuffix}",
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
    }
}
