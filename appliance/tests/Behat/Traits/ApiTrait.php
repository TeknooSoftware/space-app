<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat\Traits;

use Behat\Gherkin\Node\TableNode;
use Behat\Step\Then;
use Behat\Step\When;
use DomainException;
use DateTimeInterface;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Account;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Job;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Project;
use Teknoo\East\Paas\Object\Account as AccountOrigin;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\Job as JobOrigin;
use Teknoo\East\Paas\Object\Project as ProjectOrigin;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\ApiKeysAuth;
use Teknoo\Space\Object\Persisted\ApiKeyToken;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Service\PersistedVariableEncryption;
use Throwable;

use function array_filter;
use function array_map;
use function array_slice;
use function array_values;
use function end;
use function explode;
use function json_decode;
use function json_encode;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function trim;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait ApiTrait
{
    #[When('the API is called to list of jobs')]
    #[When('the API is called to list of jobs as :role')]
    public function theApiIsCalledToListOfJobs(?string $role = null): void
    {
        /** @var Project $project */
        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: 'GET',
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: 'space_api_v1_admin_job_list',
                    parameters: [
                        'accountId' => $project->getAccount()->getId(),
                        'projectId' => $project->getId(),
                    ],
                ),
                default => $this->getPathFromRoute(
                    route: 'space_api_v1_job_list',
                    parameters: [
                        'projectId' => $project->getId(),
                    ],
                ),
            },
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to list of projects as :role')]
    #[When('the API is called to list of projects')]
    public function theApiIsCalledToListOfProjects(?string $role = null): void
    {
        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: match ($role) {
                    'admin' => 'space_api_v1_admin_project_list',
                    default => 'space_api_v1_project_list',
                }
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to list of projects of last account as :role')]
    public function theApiIsCalledToListOfProjectsOfAccount(string $role): void
    {
        $this->executeRequest(
            method: 'GET',
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: 'space_api_v1_admin_account_project_list',
                    parameters: [
                        'accountId' => $this->recall(Account::class)->getId(),
                    ]
                ),
                default => throw new \InvalidArgumentException('Missing role'),
            },
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to list of accounts clusters of last account as :role')]
    #[When('the API is called to list of accounts clusters')]
    public function theApiIsCalledToListOfAccountsClusters(?string $role = null): void
    {
        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: match ($role) {
                    'admin' => 'space_api_v1_admin_account_clusters_list',
                    default => 'space_api_v1_account_clusters_list',
                },
                parameters: match ($role) {
                    'admin' => [
                        'accountId' => $this->recall(Account::class)->getId(),
                    ],
                    default => [],
                }
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to list of users as admin')]
    public function theApiIsCalledToListOfUsers(): void
    {
        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_user_list',
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to list of accounts as admin')]
    public function theApiIsCalledToListOfAccountsAsAdmin(): void
    {
        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_list',
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to get the last job')]
    #[When('the API is called to get the last job as :role')]
    public function theApiIsCalledToGetTheLastJob(?string $role = null): void
    {
        $project = $this->recall(Project::class);
        $job = $this->recall(Job::class);

        $this->executeRequest(
            method: 'GET',
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: 'space_api_v1_admin_job_get',
                    parameters: [
                        'accountId' => $project->getAccount()->getId(),
                        'projectId' => $project->getId(),
                        'id' => $job->getId(),
                    ],
                ),
                default => $this->getPathFromRoute(
                    route: 'space_api_v1_job_get',
                    parameters: [
                        'projectId' => $project->getId(),
                        'id' => $job->getId(),
                    ],
                ),
            },
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to get the last project')]
    #[When('the API is called to get the last project as :role')]
    public function theApiIsCalledToGetTheLastProject(bool $isForVariables = false, ?string $role = null): void
    {
        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: 'GET',
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: match ($isForVariables) {
                        true => 'space_api_v1_admin_project_edit_variables',
                        default => 'space_api_v1_admin_project_edit',
                    },
                    parameters: [
                        'id' => $project->getId(),
                        'accountId' => $project->getAccount()->getId(),
                    ],
                ),
                default => $this->getPathFromRoute(
                    route: match ($isForVariables) {
                        true => 'space_api_v1_project_edit_variables',
                        default => 'space_api_v1_project_edit',
                    },
                    parameters: [
                        'id' => $project->getId(),
                    ],
                ),
            },
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to get the last account cluster as admin')]
    public function theApiIsCalledToGetTheLastAccountClusterAsAdmin(): void
    {
        $this->theApiIsCalledToGetTheLastAccountCluster('space_api_v1_admin_account_clusters_edit');
    }

    #[When('the API is called to get the last account cluster')]
    public function theApiIsCalledToGetTheLastAccountCluster(
        string $routeName = 'space_api_v1_account_clusters_edit'
    ): void {
        $accountCluster = $this->recall(AccountCluster::class);

        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: $routeName,
                parameters: match ($routeName) {
                    'space_api_v1_admin_account_clusters_edit' => [
                        'id' => $accountCluster->getId(),
                        'accountId' => $accountCluster->getAccount()->getId(),
                    ],
                    default => [
                        'id' => $accountCluster->getId(),
                    ],
                }
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to get the last user')]
    public function theApiIsCalledToGetTheLastUser(): void
    {
        $user = $this->recall(User::class);

        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_user_edit',
                parameters: [
                    'id' => $user->getId(),
                ],
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to get the last account')]
    public function theApiIsCalledToGetTheLastAccount(): void
    {
        $account = $this->recall(Account::class);

        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_edit',
                parameters: [
                    'id' => $account->getId(),
                ],
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When("the API is called to get the last project's variables")]
    #[When("the API is called to get the last project's variables as :role")]
    public function theApiIsCalledToGetTheLastProjectsVariables(?string $role = null): void
    {
        $this->theApiIsCalledToGetTheLastProject(isForVariables: true, role: $role);
    }

    #[When('the API is called to get the last generated job')]
    public function theApiIsCalledToGetTheLastGeneratedJob(): void
    {
        $project = $this->recall(Project::class);
        $jobs = $this->listObjects(JobOrigin::class);
        $job = end($jobs);

        if (empty($job)) {
            throw new RuntimeException('Job was not created');
        }

        $this->register($job);

        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_api_v1_job_get',
                parameters: [
                    'projectId' => $project->getId(),
                    'id' => $job->getId(),
                ],
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to delete the last project with :method method as :role')]
    #[When('the API is called to delete the last project as :role')]
    #[When('the API is called to delete the last project with :method method')]
    #[When('the API is called to delete the last project')]
    public function theApiIsCalledToDeleteTheLastProject(string $method = 'POST', ?string $role = null): void
    {
        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: $method,
            url:
            match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: 'space_api_v1_admin_project_delete',
                    parameters: [
                        'accountId' => $project->getAccount()->getId(),
                        'id' => $project->getId(),
                    ],
                ),
                default => $this->getPathFromRoute(
                    route: 'space_api_v1_project_delete',
                    parameters: [
                        'id' => $project->getId(),
                    ],
                ),
            },
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to delete the last account cluster with :method method as :role')]
    #[When('the API is called to delete the last account cluster as :role')]
    #[When('the API is called to delete the last account cluster')]
    #[When('the API is called to delete the last account cluster with :method method')]
    public function theApiIsCalledToDeleteTheLastAccountCluster(string $method = 'POST', ?string $role = null): void
    {
        $route = match ($role) {
            'admin' => 'space_api_v1_admin_account_clusters_delete',
            default => 'space_api_v1_account_clusters_delete',
        };

        $accountCluster = $this->recall(AccountCluster::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: $route,
                parameters: match ($role) {
                    'admin' => [
                        'id' => $accountCluster->getId(),
                        'accountId' => $accountCluster->getAccount()->getId(),
                    ],
                    default => [
                        'id' => $accountCluster->getId(),
                    ],
                },
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to delete the last user with :method method')]
    #[When('the API is called to delete the last user')]
    public function theApiIsCalledToDeleteTheLastUser(string $method = 'POST'): void
    {
        $user = $this->recall(User::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_user_delete',
                parameters: [
                    'id' => $user->getId(),
                ],
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to delete the last account with :method method')]
    #[When('the API is called to delete the last account')]
    public function theApiIsCalledToDeleteTheLastAccount(string $method = 'POST'): void
    {
        $account = $this->recall(Account::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_delete',
                parameters: [
                    'id' => $account->getId(),
                ],
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to delete the last job with :method method')]
    #[When('the API is called to delete the last job')]
    #[When('the API is called to delete the last job as :role with :method method')]
    #[When('the API is called to delete the last job as :role')]
    public function theApiIsCalledToDeleteTheLastJob(string $method = 'POST', ?string $role = null): void
    {
        $project = $this->recall(Project::class);
        $job = $this->recall(Job::class);

        $this->executeRequest(
            method: $method,
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: 'space_api_v1_admin_job_delete',
                    parameters: [
                        'accountId' => $project->getAccount()->getId(),
                        'projectId' => $project->getId(),
                        'id' => $job->getId(),
                    ],
                ),
                default => $this->getPathFromRoute(
                    route: 'space_api_v1_job_delete',
                    parameters: [
                        'projectId' => $project->getId(),
                        'id' => $job->getId(),
                    ],
                ),
            },
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When('the API is called to create a new job with a json body:')]
    #[When('the API is called to create a new job as :role with a json body:')]
    public function theApiIsCalledToCreateANewJobWithAJsonBody(TableNode $bodyFields, ?string $role = null): void
    {
        $this->theApiIsCalledToCreateANewJob(
            bodyFields: $bodyFields,
            format: 'json',
            role: $role,
        );
    }

    private function autoGetId(string $field, string $value): string
    {
        if (str_contains($field, 'environments')) {
            $parts = explode(':', trim($value, '<>'));
            $account = $this->recall(Account::class);

            foreach ($this->listObjects(AccountEnvironment::class) as $object) {
                if (
                    $object->getAccount() === $account
                    && strtolower($object->getEnvName()) === strtolower($parts[1] ?? '')
                ) {
                    return $object->getId();
                }
            }
        }

        throw new RuntimeException('Auto unsupported');
    }

    private function encodeAPIBody(?TableNode $bodyFields, string $format = 'default'): string|array
    {
        $final = [];
        if (null !== $bodyFields) {
            foreach ($bodyFields as $field) {
                $value = match (true) {
                    str_starts_with((string) $field['value'], '<auto') => $this->autoGetId(
                        field: $field['field'],
                        value: $field['value'],
                    ),
                    default => $field['value'],
                };

                $this->setRequestParameters($final, $field['field'], $value);
            }
        }

        return match ($format) {
            'json' => json_encode($final),
            default => $final,
        };
    }

    private function submitValuesThroughAPI(string $url, ?TableNode $bodyFields, string $format = 'default'): void
    {
        $final = $this->encodeAPIBody($bodyFields, $format);

        $this->executeRequest(
            method: 'post',
            url: $url,
            params: match ($format) {
                'json' => [],
                default => $final,
            },
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
                'CONTENT_TYPE' => match ($format) {
                    'json' => 'application/json',
                    default => 'application/x-www-form-urlencoded',
                },
            ],
            noCookies: true,
            content: match ($format) {
                'json' => $final,
                default => null,
            },
        );
    }

    #[When("the API is called to edit a project's variables with a :format body:")]
    #[When("the API is called to edit a project's variables:")]
    public function theApiIsCalledToEditAProjectsVariables(TableNode $bodyFields, string $format = 'default'): void
    {
        /** @var TYPE_NAME $bodyFields */
        $this->theApiIsCalledToEditAProject($bodyFields, $format, isForVariables: true);
    }

    #[When('the API is called to edit a project:')]
    #[When("the API is called to edit a project with a :format body:")]
    #[When('the API is called to edit a project as :role with a :format body:')]
    #[When('the API is called to edit a project as :role:')]
    public function theApiIsCalledToEditAProject(
        TableNode $bodyFields,
        string $format = 'default',
        ?string $role = null,
        bool $isForVariables = false,
    ): void {
        $project = $this->recall(Project::class);

        $this->submitValuesThroughAPI(
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: match ($isForVariables) {
                        true => 'space_api_v1_admin_project_edit_variables',
                        default => 'space_api_v1_admin_project_edit',
                    },
                    parameters: [
                        'id' => $project->getId(),
                        'accountId' => $project->getAccount()->getId(),
                    ]
                ),
                default => $this->getPathFromRoute(
                    route: match ($isForVariables) {
                        true => 'space_api_v1_project_edit_variables',
                        default => 'space_api_v1_project_edit',
                    },
                    parameters: [
                        'id' => $project->getId(),
                    ]
                ),
            },
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to refresh credentials of the last project')]
    #[When('the API is called to refresh credentials of the last project as :role')]
    public function theApiIsCalledToRefreshCredentialsOfTheLastProject(
        string $role = '',
    ): void {
        $project = $this->recall(Project::class);

        $this->submitValuesThroughAPI(
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: 'space_api_v1_admin_project_refresh_credentials',
                    parameters: [
                        'id' => $project->getId(),
                        'accountId' => $project->getAccount()->getId(),
                    ]
                ),
                default => $this->getPathFromRoute(
                    route: 'space_api_v1_project_refresh_credentials',
                    parameters: [
                        'id' => $project->getId(),
                    ]
                )
            },
            bodyFields: null,
            format: 'json',
        );
    }

    #[When('the API is called to edit an account cluster as admin with a :format body:')]
    #[When('the API is called to edit an account cluster as admin:')]
    public function theApiIsCalledToEditAnAccountClusterAsAdmin(
        TableNode $bodyFields,
        string $format = 'default',
    ): void {
        $this->theApiIsCalledToEditAnAccountCluster(
            bodyFields: $bodyFields,
            format: $format,
            routeName: 'space_api_v1_admin_account_clusters_edit',
            role: 'admin',
        );
    }

    #[When('the API is called to edit an account cluster:')]
    #[When('the API is called to edit an account cluster with a :format body:')]
    public function theApiIsCalledToEditAnAccountCluster(
        TableNode $bodyFields,
        string $format = 'default',
        string $routeName = 'space_api_v1_account_clusters_edit',
        ?string $role = null,
    ): void {
        $accountCluster = $this->recall(AccountCluster::class);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: $routeName,
                parameters: match ($role) {
                    'admin' =>  [
                        'id' => $accountCluster->getId(),
                        'accountId' => $accountCluster->getAccount()->getId(),
                    ],
                    default =>  [
                        'id' => $accountCluster->getId(),
                    ],
                }
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to create a project as :role with a :format body:')]
    #[When('the API is called to create a project as :role:')]
    #[When('the API is called to create a project with a :format body:')]
    #[When('the API is called to create a project:')]
    public function theApiIsCalledToCreateAProject(
        TableNode $bodyFields,
        string $format = 'default',
        ?string $role = null,
    ): void {
        if ('admin' === $role) {
            $url = $this->getPathFromRoute(
                route: 'space_api_v1_admin_project_new',
                parameters: [
                    'accountId' => $this->recall(Account::class)->getId(),
                ]
            );
        } else {
            $url = $this->getPathFromRoute(
                route: 'space_api_v1_project_new',
            );
        }

        $this->submitValuesThroughAPI(
            url: $url,
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to create an account cluster as :role with a :format body:')]
    #[When('the API is called to create an account cluster as :role:')]
    #[When('the API is called to create an account cluster:')]
    #[When('the API is called to create an account cluster with a :format body:')]
    public function theApiIsCalledToCreateAnAccountCluster(
        TableNode $bodyFields,
        string $format = 'default',
        ?string $role = null,
    ): void {
        if ('admin' === $role) {
            $url = $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_clusters_new',
                parameters: [
                    'accountId' => $this->recall(Account::class)->getId(),
                ]
            );
        } else {
            $url = $this->getPathFromRoute(
                route: 'space_api_v1_account_clusters_new',
            );
        }

        $this->submitValuesThroughAPI(
            url: $url,
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to create an user as admin with a :format body:')]
    #[When('the API is called to create an user as admin:')]
    public function theApiIsCalledToCreateAnUserAsAdmin(
        TableNode $bodyFields,
        string $format = 'default',
    ): void {
        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_user_new',
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to create an account as admin with a :format body:')]
    #[When('the API is called to create an account as admin:')]
    public function theApiIsCalledToCreateAnAccountAsAdmin(
        TableNode $bodyFields,
        string $format = 'default',
    ): void {
        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_new',
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to edit the last user with a :format body:')]
    #[When('the API is called to edit the last user:')]
    public function theApiIsCalledToEditAnUser(
        TableNode $bodyFields,
        string $format = 'default',
    ): void {
        $user = $this->recall(User::class);
        Assert::assertNotNull($user);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_user_edit',
                parameters: [
                    'id' => $user->getId(),
                ]
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to edit the last account with a :format body:')]
    #[When('the API is called to edit the last account:')]
    #[When('the API is called to edit the last account :view with a :format body:')]
    #[When('the API is called to edit the last account :view:')]
    public function theApiIsCalledToEditAnAccount(
        TableNode $bodyFields,
        string $format = 'default',
        string $view = 'default',
    ): void {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: match ($view) {
                    'environments' => 'space_api_v1_admin_account_edit_environments',
                    default => 'space_api_v1_admin_account_edit',
                },
                parameters: [
                    'id' => $account->getId(),
                ]
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to reinstall the account registry')]
    public function theApiIsCalledToReinstallTheAccountRegistry(): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_reinstall_registry',
                parameters: [
                    'id' => $account->getId(),
                ]
            ),
            bodyFields: null,
            format: 'json',
        );
    }

    #[When("the API is called to refresh quota of account's environment")]
    public function theApiIsCalledToRefreshQuotaOfAccountsEnvironment(): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_refresh_quota',
                parameters: [
                    'id' => $account->getId(),
                ]
            ),
            bodyFields: null,
            format: 'json',
        );
    }

    #[When("the API is called to reinstall the account's environment :envName on :clusterName")]
    public function theApiIsCalledToReinstallTheAccountsEnvironmentOn(string $envName, string $clusterName): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_environment_reinstall',
                parameters: [
                    'id' => $account->getId(),
                    'envName' => $envName,
                    'clusterName' => $clusterName,
                ]
            ),
            bodyFields: null,
            format: 'json',
        );
    }

    #[When("the API is called to get user's settings")]
    public function theApiIsCalledToGetUsersSettings(): void
    {
        $this->executeRequest(
            method: 'get',
            url: $this->getPathFromRoute('space_api_v1_my_settings'),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When("the API is called to get account's :view")]
    #[When("the API is called to get account's :view as :role")]
    public function theApiIsCalledToGetAccountsSettings(
        string $view,
        ?string $role = null,
    ): void {
        $this->executeRequest(
            method: 'get',
            url: match ($view) {
                'settings' => $this->getPathFromRoute('space_api_v1_account_settings'),
                'status' => $this->getPathFromRoute('space_api_v1_account_status'),
                'environments' => $this->getPathFromRoute('space_api_v1_account_environments'),
                'variables' => match ($role) {
                    'admin' => $this->getPathFromRoute(
                        route: 'space_api_v1_admin_account_edit_variables',
                        parameters: [
                            'id' => $this->recall(Account::class)->getId(),
                        ],
                    ),
                    default => $this->getPathFromRoute('space_api_v1_account_edit_variables'),
                },
                default => throw new DomainException("$view is not available"),
            },
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[When("the API is called to update user's settings:")]
    #[When("the API is called to update user's settings with a :format body:")]
    public function theApiIsCalledToUpdateUsersSettings(TableNode $bodyFields, string $format = 'default'): void
    {
        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_my_settings',
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When("the API is called to update account's :view:")]
    #[When("the API is called to update account's :view with a :format body:")]
    #[When("the API is called to update account's :view as :role:")]
    #[When("the API is called to update account's :view with a :format body as :role:")]
    #[When('the API is called to update variables of last account with a :format body as :role:')]
    #[When('the API is called to update variables of last account with as :role:')]
    public function theApiIsCalledToUpdateAccountsSettings(
        TableNode $bodyFields,
        string $format = 'default',
        string $view = 'settings',
        ?string $role = null,
    ): void {
        $this->submitValuesThroughAPI(
            url: match ($view) {
                'environments' => $this->getPathFromRoute(
                    route: 'space_api_v1_account_environments',
                ),
                'variables' => match ($role) {
                    'admin' => $this->getPathFromRoute(
                        route: 'space_api_v1_admin_account_edit_variables',
                        parameters: [
                            'id' => $this->recall(Account::class)->getId(),
                        ],
                    ),
                    default => $this->getPathFromRoute(
                        route: 'space_api_v1_account_edit_variables',
                    ),
                },
                default => $this->getPathFromRoute(
                    route: 'space_api_v1_account_settings',
                ),
            },
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[Then('the serialized accounts variables with :count variables')]
    #[Then('the serialized accounts variables')]
    public function theSerializedAccountsVariables(?int $count = null): void
    {
        $this->isAFinalResponse();

        $account = $this->recall(Account::class);
        $accountData = $this->getRepository(AccountData::class)->findOneBy(['account' => $account]);
        $accountVars = $this->getRepository(AccountPersistedVariable::class)->findBy(['account' => $account]);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            [
                'meta' => [
                    '@class' => SpaceAccount::class,
                    'id' => $account->getId(),
                ],
                'data' => new SpaceAccount(
                    account: $account,
                    accountData: $accountData,
                    variables: $accountVars
                ),
            ],
            format: 'json',
            context: [
                'groups' => ['crud_variables'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data']['variables'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );

        if (!empty($count)) {
            Assert::assertCount(
                $count,
                $unserialized['data']['variables'] ?? [],
            );
        }
    }

    #[When('the API is called to create a new job:')]
    #[When('the API is called to create a new job as :role:')]
    public function theApiIsCalledToCreateANewJob(
        TableNode $bodyFields,
        string $format = 'default',
        ?string $role = null
    ): void {
        $project = $this->recall(Project::class);

        $this->submitValuesThroughAPI(
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: 'space_api_v1_admin_job_new',
                    parameters: [
                        'accountId' => $project->getAccount()->getId(),
                        'projectId' => $project->getId(),
                    ]
                ),
                default => $this->getPathFromRoute(
                    route: 'space_api_v1_job_new',
                    parameters: [
                        'projectId' => $project->getId(),
                    ]
                ),
            },
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    #[When('the API is called to restart a the job:')]
    #[When('the API is called to restart a the job as :role:')]
    public function theApiIsCalledToRestartATheJob(
        TableNode $bodyFields,
        string $format = 'default',
        ?string $role = null,
    ): void {
        $project = $this->recall(Project::class);

        $this->submitValuesThroughAPI(
            url: match ($role) {
                'admin' => $this->getPathFromRoute(
                    route: 'space_api_v1_admin_job_new',
                    parameters: [
                        'accountId' => $project->getAccount()->getId(),
                        'projectId' => $project->getId(),
                    ]
                ),
                default => $this->getPathFromRoute(
                    route: 'space_api_v1_job_new',
                    parameters: [
                        'projectId' => $project->getId(),
                    ]
                ),
            },
            bodyFields: $bodyFields,
            format: $format,
        );

        $this->clearJobMemory = true;
    }

    #[When('the API is called to pending job status api')]
    public function theApiIsCalledToPendingJobStatusApi(): void
    {
        Assert::assertNotEmpty($this->apiPendingJobUrl);

        $this->executeRequest(
            method: 'GET',
            url: $this->apiPendingJobUrl,
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[Then('get a JSON reponse')]
    public function getAJsonReponse(): void
    {
        Assert::assertStringStartsWith(
            'application/json',
            $this->response->headers->get('Content-Type'),
        );
    }

    #[Then('the serialized success result')]
    public function theSerializedSuccessResult(): void
    {
        Assert::assertEquals(200, $this->response?->getStatusCode());

        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertEquals(
            [
                'meta' => [
                    'id' => $account->getId(),
                    '@class' => SpaceAccount::class,
                ],
                'success' => true
            ],
            $unserialized,
        );
    }

    #[When('an :arg1 error')]
    #[When('an :arg1 error about :message')]
    public function anError(int $code, ?string $message = null): void
    {
        Assert::assertEquals(
            $code,
            $this->response->getStatusCode(),
        );

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        if (null !== $message) {
            if (isset($unserialized['code'])) {
                Assert::assertEquals(
                    $code,
                    $unserialized['code'],
                );
            }

            if (isset($unserialized['message'])) {
                Assert::assertEquals(
                    $message,
                    $unserialized['message'],
                );
            }


            if (isset($unserialized['error'])) {
                Assert::assertEquals(
                    $message,
                    $unserialized['error'],
                );
            }
        } else {
            Assert::assertEquals(
                $code,
                $unserialized['data']['code'],
            );
        }
    }

    #[Then('a pending job id')]
    #[Then('a pending job id with :role route')]
    public function aPendingJobId(?string $role = null): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertNotEmpty($unserialized['meta']['url']);
        Assert::assertNotEmpty($unserialized['data']['job_queue_id']);

        $this->apiPendingJobUrl = $unserialized['meta']['url'];

        Assert::assertEquals(
            match ($role) {
                'admin' => $this->urlGenerator->generate(
                    'space_api_v1_admin_job_new_pending',
                    [
                        'accountId' => $this->recall(Account::class)?->getId(),
                        'projectId' => $this->recall(Project::class)?->getId(),
                        'newJobId' => $unserialized['data']['job_queue_id'],
                    ]
                ),
                default => $this->urlGenerator->generate(
                    'space_api_v1_job_new_pending',
                    [
                        'projectId' => $this->recall(Project::class)?->getId(),
                        'newJobId' => $unserialized['data']['job_queue_id'],
                    ]
                ),
            },
            $unserialized['meta']['url'],
        );
    }

    #[Then('a pending job status without a job id')]
    public function aPendingJobStatusWithoutAJobId(): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertEquals(
            'teknoo.space.error.job.pending.mercure_disabled',
            $unserialized['data']['error']['message'],
        );
    }

    #[When('is a serialized collection of :count items on :total pages')]
    public function isASerializedCollectionOfItemsOnPages(int $count, int $page): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertEquals(
            1,
            $unserialized['meta']['page'] ?? 0
        );

        Assert::assertEquals(
            $count,
            $unserialized['meta']['count'] ?? 0
        );

        Assert::assertEquals(
            $page,
            $unserialized['meta']['totalPages'] ?? 0
        );

        $this->itemsPerPages = $count / $page;
    }

    #[Then('the a list of serialized users')]
    public function theAListOfSerializedUsers(): void
    {
        $users = [];
        foreach ($this->getListOfPersistedObjects(User::class) as $user) {
            $users[] = new SpaceUser($user);
        }

        $selectedUsers = array_values(
            array_slice(
                array: $users,
                offset: 0,
                length: $this->itemsPerPages,
                preserve_keys: false,
            )
        );

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            $selectedUsers,
            format: 'json',
            context: [
                'groups' => ['api'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized['data'],
        );
    }

    #[Then('the a list of serialized accounts')]
    public function theAListOfSerializedAccounts(): void
    {
        $accounts = [];
        foreach ($this->getListOfPersistedObjects(Account::class) as $account) {
            $accountData = $this->getRepository(AccountData::class)->findOneBy(['account' => $account]);
            $accounts[] = new SpaceAccount($account, $accountData);
        }

        $selectedAccounts = array_values(
            array_slice(
                array: $accounts,
                offset: 0,
                length: $this->itemsPerPages,
                preserve_keys: false,
            )
        );

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            $selectedAccounts,
            format: 'json',
            context: [
                'groups' => ['api'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized['data'],
        );
    }

    #[When('the a list of serialized jobs')]
    public function theListSerializedJobs(): void
    {
        $jobs = $this->getListOfPersistedObjects(Job::class);
        $selectedJobs = array_values(
            array_slice(
                array: $jobs,
                offset: 0,
                length: $this->itemsPerPages,
                preserve_keys: false,
            )
        );

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            $selectedJobs,
            format: 'json',
            context: [
                'groups' => ['api'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized['data'],
        );
    }

    #[Then('the a list of serialized owned projects')]
    public function theAListOfSerializedOwnedProjects(): void
    {
        $account = $this->recall(Account::class);
        $allProjects = $this->getListOfPersistedObjects(Project::class);
        $projects = [];
        foreach ($allProjects as $project) {
            if ($project->getAccount() === $account) {
                $projects[] = new SpaceProject($project);
            }
        }

        $selectedProjects = array_values(
            array_slice(
                array: $projects,
                offset: 0,
                length: $this->itemsPerPages,
                preserve_keys: false,
            )
        );

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            $selectedProjects,
            format: 'json',
            context: [
                'groups' => ['api'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized['data'],
        );
    }

    #[Then('the a list of serialized owned accounts clusters')]
    #[Then('the a list of serialized accounts clusters')]
    public function theAListOfSerializedOwnedAccountsClusters(): void
    {
        $account = $this->recall(Account::class);
        $allAccountClusters = $this->getListOfPersistedObjects(AccountCluster::class);
        $accountClusters = [];
        foreach ($allAccountClusters as $accountCluster) {
            if ($accountCluster->getAccount() === $account) {
                $accountClusters[] = $accountCluster;
            }
        }

        $selectedAccountsClusters = array_values(
            array_slice(
                array: $accountClusters,
                offset: 0,
                length: $this->itemsPerPages,
                preserve_keys: false,
            )
        );

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            $selectedAccountsClusters,
            format: 'json',
            context: [
                'groups' => ['api'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized['data'],
        );
    }

    #[Then('the a list of serialized projects')]
    #[Then('the a list of serialized projects of last :type')]
    public function theAListOfSerializedProjects(?string $type = null): void
    {
        $account = null;
        if ('account' === $type) {
            $account = $this->recall(Account::class);
        }

        $projects = [];
        foreach ($this->getListOfPersistedObjects(Project::class) as $project) {
            if ($account && $project->getAccount() !== $account) {
                continue;
            }

            $projects[] = new SpaceProject($project);
        }

        $selectedProjects = array_values(
            array_slice(
                array: $projects,
                offset: 0,
                length: $this->itemsPerPages,
                preserve_keys: false,
            )
        );

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            $selectedProjects,
            format: 'json',
            context: [
                'groups' => ['api'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized['data'],
        );
    }

    #[Then("the serialized :count project's variables")]
    #[Then("the serialized :count project's variables with :name equals to :value")]
    public function theSerializedProjectsVariables(int $count, ?string $name = null, ?string $value = null): void
    {
        $this->isAFinalResponse();

        $project = $this->recall(Project::class);
        $project ??= $this->recall(ProjectOrigin::class);
        $projectsVars = $this->getRepository(ProjectPersistedVariable::class)->findBy(['project' => $project]);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            [
                'meta' => [
                    '@class' => SpaceProject::class,
                    'id' => $project->getId(),
                ],
                'data' => new SpaceProject(projectOrAccount: $project, variables: $projectsVars)
            ],
            format: 'json',
            context: [
                'groups' => ['crud_variables'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );

        $algo = $this->getEncryptAlgoForVar();
        $service = $this->sfContainer->get(PersistedVariableEncryption::class);
        $service->setAgentMode(true);

        if (null !== $name) {
            $found = false;
            foreach ($projectsVars as $var) {
                if ($name === $var->getName()) {
                    $found = true;

                    if ($var->isSecret() && null !== $algo) {
                        $promise = new Promise(
                            fn (ProjectPersistedVariable $ppv): ProjectPersistedVariable => $ppv,
                            fn (Throwable $error) => throw $error,
                        );

                        $service->decrypt($var, $promise);

                        Assert::assertEquals(
                            $value,
                            $res = $promise->fetchResult()?->getValue(),
                        );

                        Assert::assertNotEquals(
                            $res,
                            $var->getValue(),
                        );
                    } else {
                        Assert::assertEquals(
                            $value,
                            $var->getValue(),
                        );
                    }
                }
            }

            Assert::assertTrue($found);
        }

        $service->setAgentMode(false);
    }

    #[Then('the serialized created project :name')]
    public function theSerializedCreatedProject(string $name): void
    {
        $this->theSerializedProject(created: true, name: $name);
    }

    #[Then('the serialized created account cluster :name')]
    public function theSerializedCreatedAccountCluster(string $name): void
    {
        $this->theSerializedAccountCluster(created: true, name: $name);
    }

    #[Then('the serialized user :lastName :firstName')]
    #[Then('the serialized user :lastName :firstName for :role')]
    public function theSerializedUser(string $lastName, string $firstName, ?string $role = null): void
    {
        foreach ($this->listObjects(User::class) as $user) {
        }

        $userData = $this->getRepository(UserData::class)->findOneBy(['user' => $user]);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            [
                'meta' => [
                    '@class' => SpaceUser::class,
                    'id' => $user->getId(),
                ],
                'data' => new SpaceUser(
                    user: $user,
                    userData: $userData,
                ),
            ],
            format: 'json',
            context: [
                'groups' => [
                    match ($role) {
                        'admin' => 'crud',
                        default => 'api',
                    }
                ],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );

        Assert::assertEquals(
            $firstName,
            (string) $user->getFirstName(),
        );

        Assert::assertEquals(
            $lastName,
            (string) $user->getLastName(),
        );
    }

    #[Then('the serialized account :accountName')]
    #[Then('the serialized account :accountName for :role')]
    #[Then("the serialized account's :view of :accountName")]
    #[Then("the serialized account's :view of :accountName for :role")]
    public function theSerializedAccount(string $accountName, ?string $role = null, string $view = ''): void
    {
        $account = $this->recall(Account::class);
        if (null === $account) {
            foreach ($this->listObjects(AccountOrigin::class) as $account) {
            }
        }

        Assert::assertNotNull($account);
        $accountData = $this->getRepository(AccountData::class)->findOneBy(['account' => $account]);

        $environments = [];
        if ('environments' === $view) {
            $environments = array_map(
                fn (AccountEnvironment $accountEnvironment): AccountEnvironmentResume => $accountEnvironment->resume(),
                array_filter(
                    array_values(
                        $this->listObjects(AccountEnvironment::class),
                    ),
                    fn (AccountEnvironment $accountEnvironment): bool => $accountEnvironment->getAccount() === $account,
                ),
            );
        }

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            [
                'meta' => [
                    '@class' => SpaceAccount::class,
                    'id' => $account->getId(),
                ],
                'data' => new SpaceAccount(
                    account: $account,
                    accountData: $accountData,
                    environments: $environments,
                ),
            ],
            format: 'json',
            context: [
                'groups' => match ($role . $view) {
                    'admin' => ['admin', 'api', 'crud'],
                    'environments' => ['api', 'crud_environments'],
                    'adminenvironments' => ['api', 'crud_environments'],
                    default => ['api', 'crud'],
                }
            ],
        );

        if ('environments' !== $view) {
            Assert::assertNotEmpty(
                $unserialized['data']['account'] ?? null,
            );

            Assert::assertNotEmpty(
                $unserialized['data']['accountData'] ?? null,
            );
        } else {
            Assert::assertEmpty(
                $unserialized['data']['account'] ?? null,
            );

            Assert::assertEmpty(
                $unserialized['data']['accountData'] ?? null,
            );
        }

        if ('environments' === $view) {
            Assert::assertNotEmpty(
                $unserialized['data']['environments'] ?? null,
            );
        } else {
            Assert::assertEmpty(
                $unserialized['data']['environments'] ?? null,
            );
        }

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );

        Assert::assertEquals(
            $accountName,
            (string) $account,
        );
    }

    #[Then('the serialized project :name')]
    #[Then('the serialized updated project :name')]
    public function theSerializedProject(bool $created = false, string $name = ''): void
    {
        if ($created) {
            $project = null;
            foreach ($this->listObjects(ProjectOrigin::class) as $project) {
                break;
            }

            Assert::assertNotEmpty($project);
        } else {
            $project = $this->recall(Project::class);
            $project ??= $this->recall(ProjectOrigin::class);
        }

        $projectMetadata = $this->getRepository(ProjectMetadata::class)->findOneBy(['project' => $project]);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            [
                'meta' => [
                    '@class' => SpaceProject::class,
                    'id' => $project->getId(),
                ],
                'data' => new SpaceProject(
                    projectOrAccount: $project,
                    projectMetadata: $projectMetadata,
                ),
            ],
            format: 'json',
            context: [
                'groups' => ['crud'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );

        Assert::assertEquals(
            $name,
            (string) $project,
        );
    }

    private function compareCluster(Project $expectedProject, Cluster $expected, Cluster $actual): void
    {
        $ro = new \ReflectionObject($actual);
        $rp = $ro->getProperty('project');

        Assert::assertSame($expectedProject, $rp->getValue($actual));

        $fakeNormalizer = new class () implements EastNormalizerInterface {
            public array $data = [];

            public function injectData(#[SensitiveParameter] array $data): EastNormalizerInterface
            {
                $this->data = $data;

                return $this;
            }
        };

        $expected->exportToMeData($fakeNormalizer);
        $expectedData = $fakeNormalizer->data;
        unset($expectedData['id']);
        unset($expectedData['project']);
        $expectedData['identity'] = $expectedData['identity']->exportToMeData($fakeNormalizer);

        $actual->exportToMeData($fakeNormalizer);
        $actualData = $fakeNormalizer->data;
        unset($actualData['id']);
        unset($actualData['project']);
        $actualData['identity'] = $actualData['identity']->exportToMeData($fakeNormalizer);

        Assert::assertEquals($expectedData, $actualData);
    }

    #[Then("the last project's cluster remains unchanged")]
    public function theLastProjectsClusterRemainsUnchanged(): void
    {
        $project = $this->recall(Project::class);
        $clusters = $this->listObjects(Cluster::class);

        Assert::assertCount(1, $clusters);
        /** @var Cluster $cluster */
        foreach ($clusters as $cluster) {
            $this->compareCluster(
                $project,
                $this->createCustomCluster($this->recall(Account::class)),
                $cluster,
            );
        }
    }

    #[Then("the last project's cluster returns to its original state from the clusters catalog")]
    public function theLastProjectsClusterReturnsToItsOriginalStateFromTheClustersCatalog(): void
    {
        $account = $this->recall(Account::class);
        $credentials = $this->recall(AccountEnvironment::class);
        /** @var Project $project */
        $project = $this->recall(Project::class);
        $clusters = [];
        $project->visit(
            'clusters',
            function ($cs) use (&$clusters): void {
                $clusters = $cs;
            },
        );

        Assert::assertCount(1, $clusters);

        /** @var ClusterCatalog $clustersCatalog */
        $clustersCatalog = $this->sfContainer->get('teknoo.space.clusters_catalog');

        /** @var Cluster $cluster */
        foreach ($clusters as $cluster) {
            $this->compareCluster(
                $project,
                $this->createCatalogCluster(
                    $account,
                    $clustersCatalog->getCluster($this->defaultClusterName),
                    $credentials,
                    '',
                    'prod',
                ),
                $cluster,
            );
        }
    }

    #[Then("the last project's cluster returns to its original state from the account cluster")]
    public function theLastProjectsClusterReturnsToItsOriginalStateFromTheAccountCluster(): void
    {
        $account = $this->recall(Account::class);

        /** @var Project $project */
        $project = $this->recall(Project::class);
        $clusters = [];
        $project->visit(
            'clusters',
            function ($cs) use (&$clusters): void {
                $clusters = $cs;
            },
        );

        Assert::assertCount(1, $clusters);

        /** @var Cluster $cluster */
        foreach ($clusters as $cluster) {
            $this->compareCluster(
                $project,
                $this->createCatalogAccountCluster($account, 'prod'),
                $cluster,
            );
        }
    }

    #[Then('the serialized account cluster :name')]
    #[Then('the serialized updated account cluster :name')]
    public function theSerializedAccountCluster(bool $created = false, string $name = ''): void
    {
        if ($created) {
            $accountCluster = null;
            foreach ($this->listObjects(AccountCluster::class) as $accountCluster) {
                break;
            }

            Assert::assertNotEmpty($accountCluster);
        } else {
            $accountCluster = $this->recall(AccountCluster::class);
        }

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            [
                'meta' => [
                    '@class' => AccountCluster::class,
                    'id' => $accountCluster->getId(),
                ],
                'data' => $accountCluster
            ],
            format: 'json',
            context: [
                'groups' => ['crud'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );

        Assert::assertEquals(
            '',
            $unserialized['data']['token'],
        );

        Assert::assertEquals(
            $name,
            (string) $accountCluster,
        );
    }

    #[When('the serialized job')]
    public function theSerializedJob(): void
    {
        $job = $this->recall(Job::class);
        $job ??= $this->recall(JobOrigin::class);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            [
                'meta' => [
                    '@class' => JobOrigin::class,
                    'id' => $job->getId(),
                ],
                'data' => $job
            ],
            format: 'json',
            context: [
                'groups' => ['api'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );
    }

    #[Then('the serialized deleted project')]
    public function theSerializedDeletedProject(): void
    {
        $project = $this->recall(Project::class);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = [
            'meta' => [
                '@class' => SpaceProject::class,
                'id' => $project->getId(),
                'deleted' => 'success',
            ],
            'data' => $this->normalizer->normalize(
                new SpaceProject($project),
                format: 'json',
                context: [
                    'groups' => ['digest'],
                ],
            ),
        ];

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );
    }

    #[Then('the serialized deleted account cluster')]
    public function theSerializedDeletedAccountCluster(): void
    {
        $accountCluster = $this->recall(AccountCluster::class);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = [
            'meta' => [
                '@class' => AccountCluster::class,
                'id' => $accountCluster->getId(),
                'deleted' => 'success',
            ],
            'data' => $this->normalizer->normalize(
                $accountCluster,
                format: 'json',
                context: [
                    'groups' => ['digest'],
                ],
            ),
        ];

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );
    }

    #[Then('the serialized deleted user')]
    public function theSerializedDeletedUser(): void
    {
        $user = $this->recall(User::class);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = [
            'meta' => [
                '@class' => SpaceUser::class,
                'id' => $user->getId(),
                'deleted' => 'success',
            ],
            'data' => $this->normalizer->normalize(
                new SpaceUser($user),
                format: 'json',
                context: [
                    'groups' => ['digest'],
                ],
            ),
        ];

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );
    }

    #[Then('the serialized deleted account')]
    public function theSerializedDeletedAccount(): void
    {
        $account = $this->recall(Account::class);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = [
            'meta' => [
                '@class' => SpaceAccount::class,
                'id' => $account->getId(),
                'deleted' => 'success',
            ],
            'data' => $this->normalizer->normalize(
                new SpaceAccount($account),
                format: 'json',
                context: [
                    'groups' => ['digest'],
                ],
            ),
        ];

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );
    }

    #[When('the serialized deleted job')]
    public function theSerializedDeletedJob(): void
    {
        $job = $this->recall(Job::class);

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        $normalized = $this->normalizer->normalize(
            [
                'meta' => [
                    '@class' => JobOrigin::class,
                    'id' => $job->getId(),
                    'deleted' => 'success',
                ],
                'data' => [
                    '@class' => JobOrigin::class,
                    'id' => $job->getId(),
                    'project' => $job->getProject(),
                    'environment' => $this->recall(Environment::class),
                ],
            ],
            format: 'json',
            context: [
                'groups' => ['digest'],
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );
    }

    #[Then('the subscription plan is :name')]
    public function theSubscriptionPlanNameIs(string $name): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertArrayHasKey('plan_name', $unserialized['data']);
        Assert::assertEquals($name, $unserialized['data']['plan_name']);
    }

    #[Then('with :allowed allowed environments and :counted created')]
    public function withAllowedEnvironmentsAndCreated(int $allowed, int $counted): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertArrayHasKey('environments', $unserialized['data']);
        Assert::assertArrayHasKey('allowed', $unserialized['data']['environments']);
        Assert::assertEquals($allowed, $unserialized['data']['environments']['allowed']);
        Assert::assertArrayHasKey('counted', $unserialized['data']['environments']);
        Assert::assertEquals($counted, $unserialized['data']['environments']['counted']);
    }

    #[Then('without exceeding environments')]
    public function withoutExceedingEnvironments(): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertArrayHasKey('environments', $unserialized['data']);
        Assert::assertArrayHasKey('exceeding', $unserialized['data']['environments']);
        Assert::assertFalse($unserialized['data']['environments']['exceeding']);
    }

    #[Then('with exceeding environments')]
    public function withExceedingEnvironments(): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertArrayHasKey('environments', $unserialized['data']);
        Assert::assertArrayHasKey('exceeding', $unserialized['data']['environments']);
        Assert::assertTrue($unserialized['data']['environments']['exceeding']);
    }

    #[Then('with :allowed allowed projects and :counted created')]
    public function withAllowedProjectsAndCreated(int $allowed, int $counted): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertArrayHasKey('projects', $unserialized['data']);
        Assert::assertArrayHasKey('allowed', $unserialized['data']['projects']);
        Assert::assertEquals($allowed, $unserialized['data']['projects']['allowed']);
        Assert::assertArrayHasKey('counted', $unserialized['data']['projects']);
        Assert::assertEquals($counted, $unserialized['data']['projects']['counted']);
    }

    #[Then('without exceeding projects')]
    public function withoutExceedingProjects(): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertArrayHasKey('projects', $unserialized['data']);
        Assert::assertArrayHasKey('exceeding', $unserialized['data']['projects']);
        Assert::assertFalse($unserialized['data']['projects']['exceeding']);
    }

    #[Then('with exceeding projects')]
    public function withExceedingProjects(): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertArrayHasKey('projects', $unserialized['data']);
        Assert::assertArrayHasKey('exceeding', $unserialized['data']['projects']);
        Assert::assertTrue($unserialized['data']['projects']['exceeding']);
    }

    #[When('the API is called to get a new JWT token')]
    #[When('the API is called to get a new JWT token with a :format body')]
    public function theApiIsCalledToGetANewJwtToken(string $format = 'default'): void
    {
        $this->executeRequest(
            method: 'post',
            url: $this->getPathFromRoute('space_api_v1_jwt_generate_token'),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    #[Then('a new JWT token is returned')]
    public function aNewJwtTokenIsReturned(): void
    {
        $this->isAFinalResponse();

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertArrayHasKey('data', $unserialized);
        $data = $unserialized['data'];
        Assert::assertArrayHasKey('token', $data);
        Assert::assertNotEmpty($data['token']);

        if (null === $this->jwtToken) {
            $this->jwtToken = $data['token'];
        } else {
            $this->nextJwtToken = $data['token'];
        }
    }

    #[When('the time goes back :count days')]
    public function theTimeGoesBackDays(int $count): void
    {
        $this->datesService?->passMeTheDate(
            function (DateTimeInterface $date) use ($count): void {
                $date = $date->modify('-' . $count . ' days');
                $this->datesService?->setCurrentDate($date);
                $this->currentDate = $date;
            }
        );
    }

    #[When('the time passes by :count days')]
    public function theTimePassesByDays(int $count): void
    {
        $this->datesService?->passMeTheDate(
            function (DateTimeInterface $date) use ($count): void {
                $date = $date->modify('+' . $count . ' days');
                $this->datesService?->setCurrentDate($date);
                $this->currentDate = $date;
            }
        );
    }

    #[When('the API client switch to new JWT token')]
    public function theApiClientSwitchToNewJwtToken(): void
    {
        Assert::assertNotNull($this->nextJwtToken);
        $this->jwtToken = $this->nextJwtToken;
        $this->nextJwtToken = null;
    }

    #[When('create api key :name')]
    public function createApiKeys(string $name): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_my_settings_list_api_keys',
        );

        $dateInFuture = $this->datesService->now();
        $dateInFuture = $dateInFuture->modify('+30 days');

        $values = $this->createForm('api_keys_auth')->getPhpValues();
        $values['api_keys_auth']['name'] = $name;
        $values['api_keys_auth']['expiresAt'] = $dateInFuture->format('Y-m-d');

        $this->executeRequest(
            method: 'POST',
            url: $this->getPathFromRoute('space_my_settings_list_api_keys'),
            params: $values
        );

        $node = $this->createCrawler()->filter('.api-token-value');
        $tokenString = trim((string) $node->getNode(0)?->attributes->getNamedItem('value')->textContent);

        Assert::assertNotEmpty($tokenString);

        /** @var User $user */
        $user = $this->recall(User::class);
        $apiKeyAuth = $user->getOneAuthData(ApiKeysAuth::class);

        Assert::assertInstanceOf(ApiKeysAuth::class, $apiKeyAuth);

        $token = $apiKeyAuth->getToken($name);
        Assert::assertInstanceOf(ApiKeyToken::class, $token);

        Assert::assertEquals($tokenString, $token->getToken());

        $this->register($token);
    }

    #[When('the user sign on API in with :email and the previous token')]
    public function theUserSignOnApiInWithAndThePassword(string $email): void
    {
        /** @var ApiKeyToken $token */
        $token = $this->recall(ApiKeyToken::class);

        $this->executeRequest(
            method: 'post',
            url: $this->getPathFromRoute(
                route: 'space_api_v1_login_check',
            ),
            params: [],
            noCookies: true,
            headers: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(
                [
                    'username' => ($token?->getName() ?? '') . ':' . $email,
                    'token' => $token?->getToken() ?? '',
                ]
            ),
        );
    }
}
