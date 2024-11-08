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
use Teknoo\East\Common\Doctrine\Object\Media as MediaODM;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Paas\Contracts\Recipe\Step\Job\DispatchResultInterface;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Account;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Job;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Project;
use Teknoo\East\Paas\Object\Account as AccountOrigin;
use Teknoo\East\Paas\Object\AccountQuota;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\GitRepository;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\Job as JobOrigin;
use Teknoo\East\Paas\Object\Project as ProjectOrigin;
use Teknoo\East\Paas\Object\SshIdentity;
use Teknoo\East\Paas\Object\XRegistryAuth;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Service\PersistedVariableEncryption;
use Throwable;

use function array_shift;
use function count;
use function current;
use function mb_strtolower;
use function random_int;
use function str_replace;
use function strtolower;
use function substr;
use function trim;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait PersistenceStepsTrait
{
    /**
     * @Given an account for :accountName with the account namespace :accountNamespace
     */
    public function anAccountForWithTheAccountNamespace(string $accountName, string $accountNamespace): void
    {
        $account = new Account();
        $account->setId($this->generateId());
        $account->setName($accountName);
        $account->setNamespace($accountNamespace);
        $account->setPrefixNamespace('space-behat-');

        $this->persistAndRegister($account);

        $accountData = new AccountData(
            account: $account,
            legalName: $accountName . ' SAS',
            streetAddress: '123 street',
            zipCode: '14000',
            cityName: 'Caen',
            countryName: 'France',
            vatNumber: 'FR0102030405',
            subscriptionPlan: 'test-1',
        );
        $accountData->setId($this->generateId());

        $this->persistAndRegister($accountData);

        $sac = mb_strtolower(str_replace(' ', '-', $accountName));
        $accountEnvironments = new AccountEnvironment(
            account: $account,
            clusterName: 'Demo Kube Cluster',
            envName: 'dev',
            namespace: 'space-client-' . $accountNamespace . '-dev',
            serviceAccountName:  $sac . '-dev-account',
            roleName: $sac . '-dev-role',
            roleBindingName: $sac . '-dev-role-binding',
            caCertificate: "-----BEGIN CERTIFICATE-----FooBar",
            clientCertificate: "",
            clientKey: "",
            token: "aFakeToken",
            metadata: [],
        );
        $accountEnvironments->setId($this->generateId());

        $this->persistAndRegister($accountEnvironments);
        $accountEnvironments = new AccountEnvironment(
            account: $account,
            clusterName: 'Demo Kube Cluster',
            envName: 'prod',
            namespace: 'space-client-' . $accountNamespace . '-prod',
            serviceAccountName:  $sac . '-prod-account',
            roleName: $sac . '-prod-role',
            roleBindingName: $sac . '-prod-role-binding',
            caCertificate: "-----BEGIN CERTIFICATE-----FooBar",
            clientCertificate: "",
            clientKey: "",
            token: "aFakeToken",
            metadata: [],
        );
        $accountEnvironments->setId($this->generateId());

        $this->persistAndRegister($accountEnvironments);

        $accountRegistry = new AccountRegistry(
            account: $account,
            registryNamespace: 'space-registry-' . $sac,
            registryUrl: $sac . '.registry.demo.teknoo.space',
            registryAccountName: $sac . '-registry',
            registryConfigName: $sac . '-docker-config',
            registryPassword: $sac . '-foobar',
            persistentVolumeClaimName: $sac . '-pvc',
        );
        $accountRegistry->setId($this->generateId());

        $this->persistAndRegister($accountRegistry);
    }

    /**
     * @Given quotas defined for this account
     */
    public function quotasDefinedForThisAccount()
    {
        $this->recall(Account::class)?->setQuotas(
            $this->quotasAllowed = [
                new AccountQuota('compute', 'cpu', '10'),
                new AccountQuota('memory', 'memory', '1 Gi'),
            ]
        );
    }

    /**
     * @Given an :role, called :lastName :firstName with the :email with the password :password
     */
    public function anUserCalledWithTheWithThePassword(
        string $lastName,
        string $firstName,
        string $email,
        string $password,
        string $role
    ): void {
        $sp = new StoredPassword(
            algo: PasswordAuthenticatedUser::class,
            unhashedPassword: true,
            hash: $password,
        );

        $user = new User();
        $user->setId($this->generateId());
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setActive(true);
        $user->setRoles([
            match ($role) {
                'user' => 'ROLE_USER',
                'admin' => 'ROLE_ADMIN',
            }
        ]);
        $user->addAuthData(
            $sp->setHashedPassword(
                $this->passwordHasher->hashPassword(
                    new PasswordAuthenticatedUser($user, $sp),
                    $password
                )
            )
        );

        $this->persistAndRegister($user);
        $this->recall(Account::class)?->setUsers([$user]);

        $media = new MediaODM();
        $media->setId($this->generateId());
        $this->persistAndRegister($media);

        $userData = new UserData(
            user: $user,
            picture: $media
        );
        $userData->setId($this->generateId());

        $this->persistAndRegister($userData);
    }

    /**
     * @Given the 2FA authentication enable for last user
     */
    public function theFaAuthenticationEnableForLastUser(): void
    {
        $totpAlgorithm = 'sha1';
        $totpDigits = 6;
        $totpPeriods = 30;

        $otpt = new TOTPAuth(
            provider: TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR,
            topSecret: $this->authenticator->generateSecret(),
            algorithm: $totpAlgorithm,
            period: $totpPeriods,
            digits: $totpDigits,
            enabled: true,
        );

        $this->register($otpt);
        $this->recall(User::class)?->addAuthData(
            $otpt,
        );
    }

    /**
     * @Then the account keeps these persisted variables
     */
    public function theAccountKeepsThesePersistedVariables(TableNode $expectedVariables): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);
        $vars = $this->getRepository(AccountPersistedVariable::class)->findBy(['account' => $account]);
        Assert::assertCount(count($expectedVariables->getLines()) - 1, $vars);

        $algo = $this->getEncryptAlgoForVar();
        $service = $this->sfContainer->get(PersistedVariableEncryption::class);
        $service->setAgentMode(true);

        foreach ($expectedVariables as $expVar) {
            $var = array_shift($vars);
            /** @var AccountPersistedVariable $var */
            Assert::assertInstanceOf(AccountPersistedVariable::class, $var);

            if ('x' === $expVar['id']) {
                Assert::assertNotEmpty($var->getId());
            } else {
                Assert::assertSame(
                    $expVar['id'],
                    substr($var->getId(), 0, 3)
                );
            }

            Assert::assertSame(
                $account,
                $var->getAccount(),
            );

            Assert::assertEquals(
                $expVar['name'],
                $var->getName(),
            );

            Assert::assertEquals(
                $expVar['secret'],
                (int) $var->isSecret(),
            );

            if (!empty($var->isSecret())) {
                Assert::assertEquals(
                    $algo,
                    $var->getEncryptionAlgorithm()
                );
            } else {
                Assert::assertEmpty(
                    $var->getEncryptionAlgorithm()
                );
            }

            if ($var->isSecret() && null !== $algo) {
                $promise = new Promise(
                    fn (AccountPersistedVariable $apv) => $apv,
                    fn (Throwable $error) => throw $error,
                );

                $service->decrypt($var, $promise);

                Assert::assertEquals(
                    $expVar['value'],
                    $res = $promise->fetchResult()?->getValue(),
                );

                Assert::assertNotEquals(
                    $res,
                    $var->getValue(),
                );
            } else {
                Assert::assertEquals(
                    $expVar['value'],
                    $var->getValue(),
                );
            }

            Assert::assertEquals(
                $expVar['environment'],
                $var->getEnvName(),
            );
        }
    }

    /**
     * @Then the account must have these persisted variables
     */
    public function theAccountMustHaveTheseePersistedVariables(TableNode $expectedVariables): void
    {
        $this->checkIfResponseIsAFinal();

        $this->theAccountKeepsThesePersistedVariables($expectedVariables);
    }

    /**
     * @Given the account has these persisted variables:
     */
    public function theAccountHasThesePersistedVariables(TableNode $variables): void
    {
        $account = $this->recall(Account::class);
        $service = $this->sfContainer->get(PersistedVariableEncryption::class);

        foreach ($variables as $var) {
            $algo = null;
            if (!empty($var['secret'])) {
                $algo = $this->getEncryptAlgoForVar();
            }

            $apv = new AccountPersistedVariable(
                account: $account,
                id: $var['id'],
                name: $var['name'],
                value: $var['value'],
                envName: $var['environment'],
                secret: !empty($var['secret']),
                encryptionAlgorithm: null,
                needEncryption: !empty($algo),
            );

            $promise = new Promise(
                function (AccountPersistedVariable $eapv) use ($apv, $algo): void {
                    if (!empty($algo)) {
                        Assert::assertNotEquals(
                            $apv->getValue(),
                            $eapv->getValue(),
                        );

                        Assert::assertEquals(
                            $algo,
                            $eapv->getEncryptionAlgorithm(),
                        );
                    }

                    $this->persistObject($eapv);
                },
                fn (Throwable $error) => throw $error,
            );

            if (empty($algo)) {
                $promise->success($apv);
            } else {
                $service->encrypt(clone $apv, $promise);
            }
        }
    }

    /**
     * @Given a standard website project :projectName
     */
    public function aStandardWebsiteProject(string $projectName): void
    {
        /** @var AccountEnvironment $credential */
        $credential = $this->recall(AccountEnvironment::class);
        /** @var AccountRegistry $registry */
        $registry = $this->recall(AccountRegistry::class);

        $account = $this->recall(Account::class);
        $project = new Project($account);
        $project->setId($this->generateId());
        $this->originalProjectName = $projectName;
        $project->setName($projectName);
        $project->setPrefix($this->projectPrefix = '');

        $project->setImagesRegistry(
            repository: new ImageRegistry(
                apiUrl: $registry->getRegistryUrl(),
                identity: new XRegistryAuth(
                    username: $registry->getRegistryAccountName(),
                    password: $registry->getRegistryPassword(),
                    auth: $registry->getRegistryConfigName(),
                    serverAddress: $registry->getRegistryUrl(),
                )
            )
        );

        $project->setSourceRepository(
            new GitRepository(
                'https://oauth:token@gitlab.demo',
                'main',
                new SshIdentity('git', '')
            )
        );

        $cluster = new Cluster();
        $cluster->setName($this->defaultClusterName);
        $cluster->setType($this->defaultClusterType);
        $cluster->setAddress($this->defaultClusterAddress);
        $cluster->useHierarchicalNamespaces($this->useHnc);
        $account->namespaceIsItDefined(fn (string $ns, string $pf) => $cluster->setNamespace($pf . $ns . '-prod'));
        $cluster->setEnvironment(new Environment('prod'));
        $cluster->setLocked(true);
        $cluster->setIdentity(
            new ClusterCredentials(
                caCertificate: $credential->getCaCertificate(),
                clientCertificate: $credential->getClientCertificate(),
                clientKey: $credential->getClientKey(),
                token: $credential->getToken()
            )
        );

        $clusterDev = new Cluster();
        $clusterDev->setName($this->defaultClusterName);
        $clusterDev->setType($this->defaultClusterType);
        $clusterDev->setAddress('dev.' . $this->defaultClusterAddress);
        $clusterDev->useHierarchicalNamespaces($this->useHnc);
        $account->namespaceIsItDefined(fn (string $ns, string $pf) => $clusterDev->setNamespace($pf . $ns . '-dev'));
        $clusterDev->setEnvironment(new Environment('dev'));
        $clusterDev->setLocked(true);
        $clusterDev->setIdentity(
            new ClusterCredentials(
                caCertificate: $credential->getCaCertificate(),
                clientCertificate: $credential->getClientCertificate(),
                clientKey: $credential->getClientKey(),
                token: $credential->getToken()
            )
        );

        $project->setClusters([
            $cluster,
            $clusterDev,
        ]);

        $this->persistAndRegister($project);

        $projectMetadata = new ProjectMetadata(
            project: $project,
            projectUrl: 'https://my.project.demo'
        );

        $this->persistAndRegister($projectMetadata);

        if ($this->useHnc) {
            $this->hncSuffix = '-' . str_replace(' ', '', strtolower($projectName));
        } else {
            $this->hncSuffix = '';
        }
    }

    /**
     * @Given :count standard websites projects :projectName and a prefix :prefix
     */
    public function standardWebsitesProjectsAndAPrefix(int $count, string $projectName, string $prefix): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->aStandardWebsiteProjectAndAPrefix(str_replace('X', (string) $i, $projectName), $prefix);
        }
    }

    /**
     * @Given :count basics users for this account
     */
    public function basicsUsersForThisAccount(int $count): void
    {
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $user = new User();
            $user->setId($this->generateId());
            $user->setFirstName("Firstname $i");
            $user->setLastName("Lastname $i");
            $user->setEmail("email$i@teknoo.space");
            $user->setActive(true);
            $user->setRoles(['ROLE_USER']);

            $this->persistAndRegister($user);
            $users[] = $user;

            $userData = new UserData(
                user: $user,
            );
            $userData->setId($this->generateId());

            $this->persistAndRegister($userData);
        }

        $this->recall(Account::class)?->setUsers($users);
    }

    /**
     * @Given :count accounts with some users
     */
    public function accountsWithSomeUsers(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $account = new Account();
            $account->setName("Account $i");
            $account->setNamespace("namespace-$i");

            for ($j = 1; $j <= random_int(1, $count); $j++) {
                $users = [];
                $user = new User();
                $user->setId($this->generateId());
                $user->setFirstName("Firstname $i");
                $user->setLastName("Lastname $i");
                $user->setEmail("email$i@teknoo.space");
                $user->setActive(true);
                $user->setRoles(['ROLE_USER']);

                $this->persistAndRegister($user);
                $users[] = $user;

                $userData = new UserData(user: $user,);
                $userData->setId($this->generateId());

                $this->persistAndRegister($userData);
            }

            $account->setUsers($users);
            $this->persistAndRegister($account);
        }
    }

    /**
     * @Given a standard website project :projectName and a prefix :prefix
     */
    public function aStandardWebsiteProjectAndAPrefix(string $projectName, string $prefix): void
    {
        /** @var AccountEnvironment $credential */
        $credential = $this->recall(AccountEnvironment::class);
        /** @var AccountRegistry $registry */
        $registry = $this->recall(AccountRegistry::class);

        $account = $this->recall(Account::class);
        $project = new Project($account);
        $project->setId($this->generateId());
        $this->originalProjectName = $projectName;
        $project->setName($projectName);
        $project->setPrefix($this->projectPrefix = $prefix);

        $project->setImagesRegistry(
            repository: $imageRegistry = new ImageRegistry(
                apiUrl: $registry->getRegistryUrl(),
                identity: new XRegistryAuth(
                    username: $registry->getRegistryAccountName(),
                    password: $registry->getRegistryPassword(),
                    auth: $registry->getRegistryConfigName(),
                    serverAddress: $registry->getRegistryUrl(),
                )
            )
        );

        $this->register($imageRegistry);

        $project->setSourceRepository(
            $repository = new GitRepository(
                'https://oauth:token@gitlab.demo',
                'main',
                new SshIdentity('git', '')
            )
        );

        $this->register($repository);

        $cluster = new Cluster();
        $cluster->setName($this->defaultClusterName);
        $cluster->setType($this->defaultClusterType);
        $cluster->setAddress($this->defaultClusterAddress);
        $cluster->useHierarchicalNamespaces($this->useHnc);
        $account->namespaceIsItDefined(fn (string $ns, string $pf) => $cluster->setNamespace($pf . $ns . '-prod'));
        $cluster->setEnvironment($env = new Environment('prod'));
        $cluster->setLocked(true);
        $this->register($env);
        $cluster->setIdentity(
            new ClusterCredentials(
                caCertificate: $credential->getCaCertificate(),
                clientCertificate: $credential->getClientCertificate(),
                clientKey: $credential->getClientKey(),
                token: $credential->getToken()
            )
        );

        $clusterDev = new Cluster();
        $clusterDev->setName($this->defaultClusterName);
        $clusterDev->setType($this->defaultClusterType);
        $clusterDev->setAddress('dev.' . $this->defaultClusterAddress);
        $account->namespaceIsItDefined(fn (string $ns, string $pf) => $clusterDev->setNamespace($pf . $ns . '-dev'));
        $clusterDev->useHierarchicalNamespaces($this->useHnc);
        $clusterDev->setEnvironment(new Environment('dev'));
        $clusterDev->setIdentity(
            new ClusterCredentials(
                caCertificate: $credential->getCaCertificate(),
                clientCertificate: $credential->getClientCertificate(),
                clientKey: $credential->getClientKey(),
                token: $credential->getToken()
            )
        );

        $project->setClusters([
            $cluster,
            $clusterDev,
        ]);

        $this->persistAndRegister($clusterDev);
        $this->persistAndRegister($cluster);
        $this->persistAndRegister($project);

        $projectMetadata = new ProjectMetadata(
            project: $project,
            projectUrl: 'https://my.project.demo'
        );

        $this->persistAndRegister($projectMetadata);

        if ($this->useHnc) {
            $this->hncSuffix = '-' . str_replace(' ', '', strtolower($projectName));
        } else {
            $this->hncSuffix = '';
        }
    }

    /**
     * @Given :count project's variables
     */
    public function andSomeProjectVariables(int $count): void
    {
        $project = $this->recall(Project::class);
        $service = $this->sfContainer->get(PersistedVariableEncryption::class);

        for ($i = 1; $i <= $count; $i++) {
            $isSecret = ($i % 3) === 0;
            $algo = null;
            if ($isSecret) {
                $algo = $this->getEncryptAlgoForVar();
            }

            $pVar = new ProjectPersistedVariable(
                project: $project,
                id: null,
                name: 'var ' . $i,
                value: 'value ' . $i,
                envName: 'prod',
                secret: $isSecret,
                encryptionAlgorithm: null,
                needEncryption: !empty($algo),
            );

            $promise = new Promise(
                function (ProjectPersistedVariable $epVar) use ($pVar, $algo): void {
                    if (!empty($algo)) {
                        Assert::assertNotEquals(
                            $pVar->getValue(),
                            $epVar->getValue(),
                        );

                        Assert::assertEquals(
                            $algo,
                            $epVar->getEncryptionAlgorithm(),
                        );
                    }

                    $this->persistAndRegister($epVar);
                },
                fn (Throwable $error) => throw $error,
            );

            if (empty($algo)) {
                $promise->success($pVar);
            } else {
                $service->encrypt(clone $pVar, $promise);
            }
        }
    }

    /**
     * @Then the project must be persisted
     */
    public function theProjectMustBePersisted(): void
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);
        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );

        $projects = $this->listObjects(ProjectOrigin::class);
        Assert::assertNotEmpty($projects);

        Assert::assertNotEmpty(
            current($projects)->getId(),
        );
    }

    /**
     * @Then the project must be updated
     */
    public function theProjectMustBeUpdated(): void
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);
        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );

        $projects = $this->listObjects(Project::class);
        Assert::assertNotEmpty($projects);

        $project = current($projects);
        Assert::assertNotEmpty(
            $project->getId(),
        );

        Assert::assertNotEquals(
            $this->originalProjectName,
            (string) $project,
        );
    }

    /**
     * @Then the project is not deleted
     */
    public function theProjectIsNotDeleted(): void
    {
        $projects = $this->listObjects(Project::class);
        Assert::assertNotEmpty($projects);
    }

    /**
     * @Then there are no project persisted variables
     */
    public function thereAreNoProjectPersistedVariables(): void
    {
        Assert::assertEmpty($this->listObjects(ProjectPersistedVariable::class));
    }

    /**
     * @Then data have been saved
     */
    public function dataHaveBeenSaved(): void
    {
        $crawler = $this->createCrawler();
        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);

        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );
    }

    /**
     * @Then the project keeps these persisted variables
     */
    public function theProjectKeepsTheseePersistedVariables(TableNode $expectedVariables): void
    {
        $vars = $this->listObjects(ProjectPersistedVariable::class);
        Assert::assertCount(count($expectedVariables->getLines()) - 1, $vars);
        $project = $this->recall(Project::class);

        $algo = $this->getEncryptAlgoForVar();
        $service = $this->sfContainer->get(PersistedVariableEncryption::class);
        $service->setAgentMode(true);

        foreach ($expectedVariables as $expVar) {
            $var = array_shift($vars);
            /** @var ProjectPersistedVariable $var */
            Assert::assertInstanceOf(ProjectPersistedVariable::class, $var);

            if ('x' === $expVar['id']) {
                Assert::assertNotEmpty($var->getId());
            } else {
                Assert::assertSame(
                    $expVar['id'],
                    substr($var->getId(), 0, 3)
                );
            }

            Assert::assertSame(
                $project,
                $var->getProject(),
            );

            Assert::assertEquals(
                $expVar['name'],
                $var->getName(),
            );

            Assert::assertEquals(
                $expVar['secret'],
                (int) $var->isSecret(),
            );

            if (!empty($var->isSecret())) {
                Assert::assertEquals(
                    $algo,
                    $var->getEncryptionAlgorithm()
                );
            } else {
                Assert::assertEmpty(
                    $var->getEncryptionAlgorithm()
                );
            }

            if ($var->isSecret() && null !== $algo) {
                $promise = new Promise(
                    fn (ProjectPersistedVariable $ppv) => $ppv,
                    fn (Throwable $error) => throw $error,
                );

                $service->decrypt($var, $promise);

                Assert::assertEquals(
                    $expVar['value'],
                    $res = $promise->fetchResult()?->getValue(),
                );

                Assert::assertNotEquals(
                    $res,
                    $var->getValue(),
                );
            } else {
                Assert::assertEquals(
                    $expVar['value'],
                    $var->getValue(),
                );
            }

            Assert::assertEquals(
                $expVar['environment'],
                $var->getEnvName(),
            );
        }

        $service->setAgentMode(false);
    }

    /**
     * @Then the project must have these persisted variables
     */
    public function theProjectMustHaveTheseePersistedVariables(TableNode $expectedVariables): void
    {
        $this->checkIfResponseIsAFinal();

        $this->theProjectKeepsTheseePersistedVariables($expectedVariables);
    }

    /**
     * @Given the project has these persisted variables:
     */
    public function theProjectHasThesePersistedVariables(TableNode $variables): void
    {
        $project = $this->recall(Project::class);
        $service = $this->sfContainer->get(PersistedVariableEncryption::class);

        foreach ($variables as $var) {
            $algo = null;
            if (!empty($var['secret'])) {
                $algo = $this->getEncryptAlgoForVar();
            }

            $apv = new ProjectPersistedVariable(
                project: $project,
                id: $var['id'],
                name: $var['name'],
                value: $var['value'],
                envName: $var['environment'],
                secret: !empty($var['secret']),
                encryptionAlgorithm: null,
                needEncryption: !empty($algo),
            );

            $promise = new Promise(
                function (ProjectPersistedVariable $eapv) use ($apv, $algo): void {
                    if (!empty($algo)) {
                        Assert::assertNotEquals(
                            $apv->getValue(),
                            $eapv->getValue(),
                        );

                        Assert::assertEquals(
                            $algo,
                            $eapv->getEncryptionAlgorithm(),
                        );
                    }

                    $this->persistObject($eapv);
                },
                fn (Throwable $error) => throw $error,
            );

            if (empty($algo)) {
                $promise->success($apv);
            } else {
                $service->encrypt(clone $apv, $promise);
            }
        }
    }

    /**
     * @Given :number jobs for the project
     */
    public function jobsForTheProject(int $number): void
    {
        $project = $this->recall(Project::class);
        $env = $this->recall(Environment::class);
        $cluster = $this->recall(Cluster::class);
        $registry = $this->recall(ImageRegistry::class);
        $repository = $this->recall(GitRepository::class);

        for ($i = 0; $i < $number; $i++) {
            $job = new Job();
            $job->setProject($project);
            $job->setEnvironment($env);
            $job->setClusters([$cluster]);
            $job->setSourceRepository($repository);
            $job->setImagesRegistry($registry);

            $this->persistAndRegister($job);
        }
    }

    /**
     * @When the job is deleted
     */
    public function theJobIsDeleted(): void
    {
        Assert::assertEmpty(
            $this->listObjects(Job::class),
        );
    }

    /**
     * @Then there is a project in the memory for this account
     */
    public function thereIsAProjectInTheMemoryForThisAccount(): void
    {
        Assert::assertCount(
            1,
            $this->listObjects(ProjectOrigin::class),
        );
    }

    /**
     * @Then there is an user in the memory
     */
    public function thereIsAnUserInTheMemory(): void
    {
        Assert::assertCount(
            2,
            $this->listObjects(User::class),
        );
    }

    /**
     * @Then there is an account in the memory
     */
    public function thereIsAnAccountInTheMemory(): void
    {
        Assert::assertNotEmpty(
            $this->listObjects(User::class),
        );
    }

    /**
     * @Then no object has been deleted
     */
    public function noObjectHasBeenDeleted()
    {
        Assert::assertEmpty($this->removedObjects);
    }

    /**
     * @Then the old account environment account :namespace must be deleted
     */
    public function theOldAccountEnvironmentAccountMustBeDeleted(string $namespace): void
    {
        Assert::assertNotEmpty($this->removedObjects[AccountEnvironment::class]);
        Assert::assertCount(1, $this->removedObjects[AccountEnvironment::class]);

        /** @var AccountEnvironment $accountEnv */
        foreach ($this->removedObjects[AccountEnvironment::class] as $accountEnv) {
            Assert::assertEquals(
                $namespace,
                $accountEnv->getNamespace(),
            );
        }
    }

    /**
     * @Then the old account registry object has been deleted and remplaced
     */
    public function theOldAccountRegistryObjectHasBeenDeletedAndRemplaced(): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        Assert::assertNotEmpty($this->removedObjects[AccountRegistry::class]);
        Assert::assertCount(1, $this->removedObjects[AccountRegistry::class]);

        foreach ($this->removedObjects[AccountRegistry::class] as $oldAR) {
            break;
        }

        /** @var AccountRegistry $ar */
        foreach ($this->listObjects(AccountRegistry::class) as $ar) {
            if ($ar->getAccount() === $account) {
                Assert::assertEquals(
                    $oldAR->getRegistryNamespace(),
                    $ar->getRegistryNamespace(),
                );

                return;
            }
        }

        Assert::fail('Missing AccountRegistry');
    }

    /**
     * @Then the old account environment :namespace object has been deleted and remplaced
     */
    public function theOldAccountEnvironmentObjectHasBeenDeletedAndRemplaced(string $namespace): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        Assert::assertNotEmpty($this->removedObjects[AccountEnvironment::class]);
        Assert::assertCount(1, $this->removedObjects[AccountEnvironment::class]);

        foreach ($this->removedObjects[AccountEnvironment::class] as $oldAE) {
            break;
        }

        Assert::assertEquals(
            $namespace,
            $oldAE->getNamespace(),
        );

        /** @var AccountEnvironment $ae */
        foreach ($this->listObjects(AccountEnvironment::class) as $ae) {
            if ($ae->getAccount() === $account && $oldAE->getEnvName() === $ae->getEnvName()) {
                Assert::assertEquals(
                    $oldAE->getNamespace(),
                    $ae->getNamespace(),
                );

                return;
            }
        }

        Assert::fail('Missing AccountEnvironment');
    }

    /**
     * @Then the project is deleted
     */
    public function theProjectIsDeleted(): void
    {
        Assert::assertEmpty(
            $this->listObjects(Project::class),
        );
    }

    /**
     * @Then the user is deleted
     */
    public function theUserIsDeleted(): void
    {
        Assert::assertCount(
            1,
            $this->listObjects(User::class),
        );
    }

    /**
     * @Then the account is deleted
     */
    public function theAccountIsDeleted(): void
    {
        Assert::assertEmpty(
            $this->listObjects(Account::class),
        );
    }

    /**
     * @When the job is not deleted
     */
    public function theJobIsNotDeleted(): void
    {
        Assert::assertNotEmpty(
            $this->listObjects(Job::class),
        );
    }

    /**
     * @Then job must be successful finished
     */
    public function jobMustBeSuccessfulFinished(): void
    {
        $jobs = $this->listObjects(JobOrigin::class);
        Assert::assertNotEmpty($jobs);

        /** @var JobOrigin $job */
        $job = current($jobs);
        Assert::assertInstanceOf(JobOrigin::class, $job);

        Assert::assertTrue($job->getHistory()->isFinal(), 'History is not final');
        Assert::assertEquals(
            DispatchResultInterface::class,
            $job->getHistory()->getMessage(),
        );
    }

    /**
     * @Then An account :accountName is created
     */
    public function anAccountIsCreated(string $accountName): void
    {
        $accounts = $this->listObjects(AccountOrigin::class);
        Assert::assertNotEmpty($accounts);

        Assert::assertNotEmpty(
            $accountName,
            (string) current($accounts),
        );
    }

    /**
     * @Then an user :email is created
     */
    public function anUserIsCreated(string $email): void
    {
        $users = $this->listObjects(User::class);
        Assert::assertNotEmpty($users);

        Assert::assertNotEmpty(
            $email,
            current($users)->getEmail(),
        );
    }
}
