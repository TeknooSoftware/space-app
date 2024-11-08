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

namespace Teknoo\Space\Tests\Behat\Traits;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Account;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Job;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Project;
use Teknoo\East\Paas\Object\Account as AccountOrigin;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\Job as JobOrigin;
use Teknoo\East\Paas\Object\Project as ProjectOrigin;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Service\PersistedVariableEncryption;
use Throwable;

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
    /**
     * @When the API is called to list of jobs
     */
    public function theApiIsCalledToListOfJobs(): void
    {
        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_api_v1_job_list',
                parameters: [
                    'projectId' => $project->getId(),
                ],
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    /**
     * @When the API is called to list of projects
     * @When the API is called to list of projects as :role
     */
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

    /**
     * @When the API is called to list of users as admin
     */
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

    /**
     * @When the API is called to list of accounts as admin
     */
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

    /**
     * @When the API is called to get the last job
     */
    public function theApiIsCalledToGetTheLastJob(): void
    {
        $project = $this->recall(Project::class);
        $job = $this->recall(Job::class);

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

    /**
     * @When the API is called to get the last project as admin
     */
    public function theApiIsCalledToGetTheLastProjectAsAdmin(): void
    {
        $this->theApiIsCalledToGetTheLastProject('space_api_v1_admin_project_edit');
    }

    /**
     * @When the API is called to get the last project
     */
    public function theApiIsCalledToGetTheLastProject(string $routeName = 'space_api_v1_project_edit'): void
    {
        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: $routeName,
                parameters: [
                    'id' => $project->getId(),
                ],
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    /**
     * @When the API is called to get the last user
     */
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

    /**
     * @When the API is called to get the last account
     */
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

    /**
     * @When the API is called to get the last project's variables
     */
    public function theApiIsCalledToGetTheLastProjectsVariables(): void
    {
        $this->theApiIsCalledToGetTheLastProject('space_api_v1_project_edit_variables');
    }

    /**
     * @When the API is called to get the last generated job
     */
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

    /**
     * @When the API is called to delete the last project
     * @When the API is called to delete the last project with :method method
     * @When the API is called to delete the last project as :role
     * @When the API is called to delete the last project with :method method as :role
     */
    public function theApiIsCalledToDeleteTheLastProject(string $method = 'POST', ?string $role = null): void
    {
        $route = match ($role) {
            'admin' => 'space_api_v1_admin_project_delete',
            default => 'space_api_v1_project_delete',
        };

        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: $route,
                parameters: [
                    'id' => $project->getId(),
                ],
            ),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    /**
     * @When the API is called to delete the last user
     * @When the API is called to delete the last user with :method method
     */
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

    /**
     * @When the API is called to delete the last account
     * @When the API is called to delete the last account with :method method
     */
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

    /**
     * @When the API is called to delete the last job
     * @When the API is called to delete the last job with :method method
     */
    public function theApiIsCalledToDeleteTheLastJob(string $method = 'POST'): void
    {
        $project = $this->recall(Project::class);
        $job = $this->recall(Job::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: 'space_api_v1_job_delete',
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

    /**
     * @When the API is called to create a new job with a json body:
     */
    public function theApiIsCalledToCreateANewJobWithAJsonBody(TableNode $bodyFields): void
    {
        $this->theApiIsCalledToCreateANewJob($bodyFields, 'json');
    }

    private function autoGetId(string $field, string $value): string
    {
        if (str_contains($field, 'environmentResumes')) {
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
                    str_starts_with($field['value'], '<auto') => $this->autoGetId($field['field'], $field['value']),
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

    /**
     * @When the API is called to edit a project's variables with a json body:
     */
    public function theApiIsCalledToEditAProjectsVariablesWithAJsonBody(TableNode $bodyFields): void
    {
        $this->theApiIsCalledToEditAProject($bodyFields, 'json', 'space_api_v1_project_edit_variables');
    }

    /**
     * @When the API is called to edit a project's variables:
     */
    public function theApiIsCalledToEditAProjectsVariables(TableNode $bodyFields): void
    {
        $this->theApiIsCalledToEditAProject($bodyFields, 'form', 'space_api_v1_project_edit_variables');
    }

    /**
     * @When the API is called to edit a project with a json body:
     */
    public function theApiIsCalledToEditAProjectWithAJsonBody(TableNode $bodyFields): void
    {
        $this->theApiIsCalledToEditAProject($bodyFields, 'json');
    }

    /**
     * @When the API is called to edit a project as admin:
     * @When the API is called to edit a project as admin with a :format body:
     */
    public function theApiIsCalledToEditAProjectAsAdmin(
        TableNode $bodyFields,
        string $format = 'default',
    ): void {
        $this->theApiIsCalledToEditAProject(
            bodyFields: $bodyFields,
            format: $format,
            routeName: 'space_api_v1_admin_project_edit',
        );
    }

    /**
     * @When the API is called to edit a project:
     */
    public function theApiIsCalledToEditAProject(
        TableNode $bodyFields,
        string $format = 'default',
        string $routeName = 'space_api_v1_project_edit',
    ): void {
        $project = $this->recall(Project::class);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: $routeName,
                parameters: [
                    'id' => $project->getId(),
                ]
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    /**
     * @When the API is called to create a project:
     * @When the API is called to create a project with a :format body:
     * @When the API is called to create a project as :role:
     * @When the API is called to create a project as :role with a :format body:
     */
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

    /**
     * @When the API is called to create an user as admin:
     * @When the API is called to create an user as admin with a :format body:
     */
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

    /**
     * @When the API is called to create an account as admin:
     * @When the API is called to create an account as admin with a :format body:
     */
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

    /**
     * @When the API is called to edit the last user:
     * @When the API is called to edit the last user with a :format body:
     */
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

    /**
     * @When the API is called to edit the last account:
     * @When the API is called to edit the last account with a :format body:
     */
    public function theApiIsCalledToEditAnAccount(
        TableNode $bodyFields,
        string $format = 'default',
    ): void {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_edit',
                parameters: [
                    'id' => $account->getId(),
                ]
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    /**
     * @When the API is called to reinstall the account registry
     */
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

    /**
     * @When the API is called to refresh quota of account's environment
     */
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

    /**
     * @When the API is called to reinstall the account's environment :envName on :clusterName
     */
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

    /**
     * @When the API is called to get user's settings
     */
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

    /**
     * @When the API is called to get account's settings
     */
    public function theApiIsCalledToGetAccountsSettings(): void
    {
        $this->executeRequest(
            method: 'get',
            url: $this->getPathFromRoute('space_api_v1_account_settings'),
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }

    /**
     * @When the API is called to update user's settings:
     * @When the API is called to update user's settings with a :format body:
     */
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

    /**
     * @When the API is called to update account's settings:
     * @When the API is called to update account's settings with a :format body:
     */
    public function theApiIsCalledToUpdateAccountsSettings(TableNode $bodyFields, string $format = 'default'): void
    {
        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_account_settings',
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    /**
     * @When the API is called to update account's variables:
     * @When the API is called to update account's variables with a :format body:
     * @When the API is called to update variables of last account with as :role:
     * @When the API is called to update variables of last account with a :format body as :role:
     */
    public function theApiIsCalledToUpdateAccountsVariables(
        TableNode $bodyFields,
        string $format = 'default',
        ?string $role = null,
    ): void {
        if (null === $role) {
            $url = $this->getPathFromRoute(
                route: 'space_api_v1_account_edit_variables',
            );
        } else {
            $account = $this->recall(Account::class);
            Assert::assertNotEmpty($account);

            $url = $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_edit_variables',
                parameters: [
                    'id' => $account->getId(),
                ],
            );
        }

        $this->submitValuesThroughAPI(
            url: $url,
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    /**
     * @When the API is called to get account's variables
     * @When the API is called to get variables of last account as :role
     */
    public function theApiIsCalledToGetAccountsVariables(?string $role = null): void
    {
        if (null === $role) {
            $url = $this->getPathFromRoute(
                route: 'space_api_v1_account_edit_variables',
            );
        } else {
            $account = $this->recall(Account::class);
            Assert::assertNotEmpty($account);

            $url = $this->getPathFromRoute(
                route: 'space_api_v1_admin_account_edit_variables',
                parameters: [
                    'id' => $account->getId(),
                ],
            );
        }

        $this->executeRequest(
            method: 'post',
            url: $url,
            headers: [
                'HTTP_AUTHORIZATION' => "Bearer {$this->jwtToken}",
            ],
            noCookies: true,
        );
    }



    /**
     * @Then the serialized accounts variables
     * @Then the serialized accounts variables with :count variables
     */
    public function theSerializedAccountsVariables(?int $count = null): void
    {
        $this->checkIfResponseIsAFinal();

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

    /**
     * @When the API is called to create a new job:
     */
    public function theApiIsCalledToCreateANewJob(TableNode $bodyFields, string $format = 'default'): void
    {
        $project = $this->recall(Project::class);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_job_new',
                parameters: [
                    'projectId' => $project->getId(),
                ]
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    /**
     * @When the API is called to restart a the job:
     */
    public function theApiIsCalledToRestartATheJob(TableNode $bodyFields, string $format = 'default'): void
    {
        $project = $this->recall(Project::class);

        $this->submitValuesThroughAPI(
            url: $this->getPathFromRoute(
                route: 'space_api_v1_job_new',
                parameters: [
                    'projectId' => $project->getId(),
                ]
            ),
            bodyFields: $bodyFields,
            format: $format,
        );

        $this->clearJobMemory = true;
    }

    /**
     * @When the API is called to pending job status api
     */
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

    /**
     * @Then get a JSON reponse
     */
    public function getAJsonReponse(): void
    {
        Assert::assertEquals(
            'application/json; charset=utf-8',
            $this->response->headers->get('Content-Type'),
        );
    }

    /**
     * @Then the serialized success result
     */
    public function theSerializedSuccessResult()
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

    /**
     * @When an :arg1 error
     */
    public function anError(int $code): void
    {
        Assert::assertEquals(
            $code,
            $this->response->getStatusCode(),
        );

        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertEquals(
            $code,
            $unserialized['data']['code'],
        );
    }

    /**
     * @Then a pending job id
     */
    public function aPendingJobId(): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertNotEmpty($unserialized['meta']['url']);
        Assert::assertNotEmpty($unserialized['data']['job_queue_id']);

        $this->apiPendingJobUrl = $unserialized['meta']['url'];

        Assert::assertEquals(
            $this->urlGenerator->generate(
                'space_api_v1_job_new_pending',
                [
                    'projectId' => $this->recall(Project::class)?->getId(),
                    'newJobId' => $unserialized['data']['job_queue_id'],
                ]
            ),
            $unserialized['meta']['url'],
        );
    }

    /**
     * @Then a pending job status without a job id
     */
    public function aPendingJobStatusWithoutAJobId(): void
    {
        $body = (string) $this->response->getContent();
        $unserialized = json_decode(json: $body, associative: true);

        Assert::assertEquals(
            'teknoo.space.error.job.pending.mercure_disabled',
            $unserialized['data']['error']['message'],
        );
    }

    /**
     * @When is a serialized collection of :count items on :total pages
     */
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

    /**
     * @Then the a list of serialized users
     */
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

    /**
     * @Then the a list of serialized accounts
     */
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

    /**
     * @When the a list of serialized jobs
     */
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

    /**
     * @Then the a list of serialized owned projects
     */
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

    /**
     * @Then the a list of serialized projects
     */
    public function theAListOfSerializedProjects(): void
    {
        $projects = [];
        foreach ($this->getListOfPersistedObjects(Project::class) as $project) {
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

    /**
     * @Then the serialized :count project's variables
     * @Then the serialized :count project's variables with :name equals to :value
     */
    public function theSerializedProjectsVariables(int $count, ?string $name = null, ?string $value = null): void
    {
        $this->checkIfResponseIsAFinal();

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
                            fn (ProjectPersistedVariable $ppv) => $ppv,
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

    /**
     * @Then the serialized created project :name
     */
    public function theSerializedCreatedProject(string $name): void
    {
        $this->theSerializedProject(created: true, name: $name);
    }

    /**
     * @Then the serialized user :lastName :firstName
     * @Then the serialized user :lastName :firstName for :role
     */
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

    /**
     * @Then the serialized account :accountName
     * @Then the serialized account :accountName for :role
     */
    public function theSerializedAccount(string $accountName, ?string $role = null): void
    {
        $account = $this->recall(Account::class);
        if (null === $account) {
            foreach ($this->listObjects(AccountOrigin::class) as $account) {
            }
        }

        Assert::assertNotNull($account);
        $accountData = $this->getRepository(AccountData::class)->findOneBy(['account' => $account]);

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
                ),
            ],
            format: 'json',
            context: [
                'groups' => match ($role) {
                    'admin' => ['admin', 'api', 'crud'],
                    default => ['api', 'crud'],
                }
            ],
        );

        Assert::assertNotEmpty(
            $unserialized['data']['account'] ?? null,
        );

        Assert::assertNotEmpty(
            $unserialized['data']['accountData'] ?? null,
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );

        Assert::assertEquals(
            $accountName,
            (string) $account,
        );
    }

    /**
     * @Then the serialized project :name
     * @Then the serialized updated project :name
     */
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

    /**
     * @When the serialized job
     */
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

    /**
     * @Then the serialized deleted project
     */
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

    /**
     * @Then the serialized deleted user
     */
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

    /**
     * @Then the serialized deleted account
     */
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

    /**
     * @When the serialized deleted job
     */
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
}
