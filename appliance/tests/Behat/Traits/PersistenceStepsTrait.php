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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use DateTimeInterface;
use LogicException;
use PHPUnit\Framework\Assert;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Common\Doctrine\Object\Media as MediaODM;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Contracts\Recipe\Step\Job\DispatchResultInterface;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Account;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Job;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Project;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Object\Account as AccountOrigin;
use Teknoo\East\Paas\Object\AccountQuota;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\GitRepository;
use Teknoo\East\Paas\Object\History;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\Job as JobOrigin;
use Teknoo\East\Paas\Object\Project as ProjectOrigin;
use Teknoo\East\Paas\Object\SshIdentity;
use Teknoo\East\Paas\Object\XRegistryAuth;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\Symfony\Object\ApiKeysAuthUser;
use Teknoo\Space\Object\Config\ConfigClusterInterface as ClusterConfig;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Object\Persisted\ApiKeyToken;
use Teknoo\Space\Object\Persisted\ApiKeysAuth;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
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
    #[Given('an account for :accountName with the account namespace :accountNamespace')]
    public function anAccountForWithTheAccountNamespace(string $accountName, string $accountNamespace): void
    {
        $account = new Account();
        $account->setId($this->generateId());
        $account->setName($accountName);
        $account->setNamespace($accountNamespace);
        $account->setPrefixNamespace('space-client-');

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
        $accountEnvironment = new AccountEnvironment(
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
        $accountEnvironment->setId($this->generateId());

        $this->persistAndRegister($accountEnvironment);
        $accountEnvironment = new AccountEnvironment(
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
        $accountEnvironment->setId($this->generateId());

        $this->persistAndRegister($accountEnvironment);

        $accountRegistry = new AccountRegistry(
            account: $account,
            registryNamespace: 'space-registry-' . $sac,
            registryUrl: $sac . '.registry.demo.teknoo.space',
            registryAccountName: $sac . '-registry',
            registryConfigName: $sac . '-docker-config',
            registryPassword: $sac . '-foobar',
            persistentVolumeClaimName: $sac . '-pvc',
            clusterName: 'Demo Kube Cluster',
        );
        $accountRegistry->setId($this->generateId());

        $this->persistAndRegister($accountRegistry);
    }

    #[Given('quotas defined for this account')]
    public function quotasDefinedForThisAccount(): void
    {
        $this->recall(Account::class)?->setQuotas(
            $this->quotasAllowed = [
                new AccountQuota('compute', 'cpu', '10'),
                new AccountQuota('memory', 'memory', '1 Gi'),
            ]
        );
    }

    #[Given('an :role, called :lastName :firstName with the :email with the password :password')]
    #[Given('a :role, called :lastName :firstName with the :email with the password :password')]
    public function theRoleCalledWithTheEmailAndThePassword(
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

    #[Given('a token :name with the value :token')]
    public function aTokenWithTheValue(string $name, string $token): void
    {
        $this->datesService?->passMeTheDate(
            function (DateTimeInterface $now) use ($name, $token): void {
                /** @var User $user */
                $user = $this->recall(User::class);

                $apiToken = new ApiKeyToken(
                    name: $name,
                    token: $token,
                    tokenHash: '',
                    isExpired: false,
                    createdAt: $now,
                    expiresAt: (clone $now)->modify('+30 day'),
                );

                $apiToken->setTokenHash(
                    $this->passwordHasher->hashPassword(
                        new ApiKeysAuthUser(
                            $user,
                            $apiToken,
                        ),
                        $token
                    )
                );

                /** @var ApiKeysAuth $apiKeys */
                $apiKeys = $user->getOneAuthData(ApiKeysAuth::class) ?? new ApiKeysAuth();
                $apiKeys->addToken($apiToken);
                $user->addAuthData($apiKeys);

                $this->register($apiToken);
            }
        );
    }

    #[Given('the 2FA authentication is enabled for the last user')]
    public function theTwoFaAuthenticationIsEnabledForTheLastUser(): void
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

    #[Then('the account keeps these persisted variables')]
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
                    fn (AccountPersistedVariable $apv): AccountPersistedVariable => $apv,
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

    #[Then('the account must have these persisted variables')]
    public function theAccountMustHaveTheseePersistedVariables(TableNode $expectedVariables): void
    {
        $this->isAFinalResponse();

        $this->theAccountKeepsThesePersistedVariables($expectedVariables);
    }

    #[Given('the account has these persisted variables:')]
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

    private ?string $overrideClusterType = null;

    private ?string $overrideClusterName = null;

    private ?string $overrideClusterAddress = null;

    /**
     * Override the cluster type/name/address the next `a standard project` step builds. Left `null` for every
     * Kubernetes scenario, so the produced clusters stay byte-for-byte identical to the default behaviour.
     */
    public function setClusterOverride(string $type, string $name, string $address): void
    {
        $this->overrideClusterType = $type;
        $this->overrideClusterName = $name;
        $this->overrideClusterAddress = $address;
    }

    #[Given('a standard project :projectName')]
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

        //Docker Compose clusters take their deploy-time identity from the catalog configuration (SSH key +
        //known_hosts), so resolve it here; Kubernetes scenarios never touch the catalog and keep the
        //account-environment credentials.
        $clusterConfig = null;
        if ('docker-compose' === $this->overrideClusterType) {
            /** @var ClusterCatalog $clustersCatalog */
            $clustersCatalog = $this->sfContainer->get('teknoo.space.clusters_catalog');
            $clusterConfig = $clustersCatalog->getCluster(
                $this->overrideClusterName ?? $this->defaultClusterName,
            );
        }

        $cluster = new Cluster();
        $cluster->setName($this->overrideClusterName ?? $this->defaultClusterName);
        $cluster->setType($this->overrideClusterType ?? $this->defaultClusterType);
        $cluster->setAddress($this->overrideClusterAddress ?? $this->defaultClusterAddress);
        $cluster->useHierarchicalNamespaces($this->useHnc);
        $account->namespaceIsItDefined(
            fn (string $ns, string $pf): Cluster => $cluster->setNamespace($pf . $ns . '-prod')
        );
        $cluster->setEnvironment(new Environment('prod'));
        $cluster->setLocked(true);
        $cluster->setIdentity($this->buildClusterIdentity($clusterConfig, $credential));

        $clusterDev = new Cluster();
        $clusterDev->setName($this->overrideClusterName ?? $this->defaultClusterName);
        $clusterDev->setType($this->overrideClusterType ?? $this->defaultClusterType);
        $clusterDev->setAddress($this->overrideClusterAddress ?? ('dev.' . $this->defaultClusterAddress));
        $clusterDev->useHierarchicalNamespaces($this->useHnc);
        $account->namespaceIsItDefined(
            fn (string $ns, string $pf): Cluster => $clusterDev->setNamespace($pf . $ns . '-dev')
        );
        $clusterDev->setEnvironment(new Environment('dev'));
        $clusterDev->setLocked(true);
        $clusterDev->setIdentity($this->buildClusterIdentity($clusterConfig, $credential));

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

    #[Given(':count standard projects :projectName and a prefix :prefix')]
    public function standardProjectsAndAPrefix(int $count, string $projectName, string $prefix): void
    {
        for ($i = 1; $i <= $count; ++$i) {
            $this->aStandardProjectAndAPrefix(str_replace('X', (string) $i, $projectName), $prefix);
        }
    }

    #[Given(':count accounts clusters :name and a slug :slug')]
    public function accountsClustersAndASlug(int $count, string $name, string $slug): void
    {
        for ($i = 1; $i <= $count; ++$i) {
            $this->anAccountClustersAndASlug(str_replace('X', (string) $i, $name), $slug . '-' . $i);
        }
    }

    #[Given(':count basics users for this account')]
    public function basicsUsersForThisAccount(int $count): void
    {
        $users = [];
        for ($i = 1; $i <= $count; ++$i) {
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

    #[Given(':count accounts with some users')]
    public function accountsWithSomeUsers(int $count): void
    {
        for ($i = 1; $i <= $count; ++$i) {
            $account = new Account();
            $account->setName("Account $i");
            $account->setNamespace("namespace-$i");

            $users = [];
            for ($j = 1; $j <= random_int(1, $count); ++$j) {
                $user = new User();
                $user->setId($this->generateId());
                $user->setFirstName("Firstname $i");
                $user->setLastName("Lastname $i");
                $user->setEmail("email$i@teknoo.space");
                $user->setActive(true);
                $user->setRoles(['ROLE_USER']);

                $this->persistAndRegister($user);
                $users[] = $user;

                $userData = new UserData(user: $user);
                $userData->setId($this->generateId());

                $this->persistAndRegister($userData);
            }

            $account->setUsers($users);
            $this->persistAndRegister($account);
        }
    }

    private function createCustomCluster(Account $account): Cluster
    {
        $cluster = new Cluster();
        $cluster->setName('Custom Cluster');
        $cluster->setType($this->defaultClusterType);
        $cluster->setAddress('https://custom.cluster');
        $cluster->useHierarchicalNamespaces(false);
        $account->namespaceIsItDefined(
            fn (string $ns, string $pf): Cluster => $cluster->setNamespace($pf . $ns . '-prod')
        );
        $cluster->setEnvironment($env = new Environment('prod'));

        if (empty($this->recall(Environment::class))) {
            $this->register($env);
        }

        $cluster->setLocked(false);

        $cluster->setIdentity(
            new ClusterCredentials(
                caCertificate: 'foo',
                clientCertificate: '',
                clientKey: '',
                token: 'bar',
            )
        );

        return $cluster;
    }

    private function buildClusterIdentity(
        ?ClusterConfig $clusterConfig,
        AccountEnvironment $credential,
    ): ClusterCredentials {
        //Docker Compose clusters connect over SSH: their deploy-time identity is the one their configuration
        //exposes (private key in clientKey, known_hosts in caCertificate, optional SSH login in username),
        //exactly like production - and exactly what the real DockerCompose\RunnerFactory consumes to
        //materialize "--private-key" and resolve "--user". Any other cluster type keeps the Kubernetes-shaped
        //credentials derived from the account environment, so those scenarios stay byte-for-byte identical.
        if ($clusterConfig instanceof DockerComposeCluster) {
            return $clusterConfig->getCredentials();
        }

        return new ClusterCredentials(
            caCertificate: $credential->getCaCertificate(),
            clientCertificate: $credential->getClientCertificate(),
            clientKey: $credential->getClientKey(),
            token: $credential->getToken()
        );
    }

    private function createCatalogCluster(
        Account $account,
        ClusterConfig $clusterConfig,
        AccountEnvironment $credential,
        string $prefix,
        string $envName,
    ): Cluster {
        $cluster = new Cluster();
        $cluster->setName($clusterConfig->name);
        $cluster->setType($clusterConfig->type);
        $cluster->setAddress(str_replace('https://', 'https://' . $prefix, $clusterConfig->masterAddress));
        $cluster->useHierarchicalNamespaces($this->useHnc);
        $account->namespaceIsItDefined(
            fn (string $ns, string $pf): Cluster => $cluster->setNamespace($pf . $ns . '-' . $envName)
        );
        $cluster->setEnvironment($env = new Environment($envName));
        $cluster->setLocked(true);

        if (empty($this->recall(Environment::class))) {
            $this->register($env);
        }

        $cluster->setIdentity($this->buildClusterIdentity($clusterConfig, $credential));

        return $cluster;
    }

    private function createCatalogAccountCluster(
        Account $account,
        string $envName,
    ): Cluster {
        /** @var AccountCluster $accountCluster */
        $accountCluster = $this->recall(AccountCluster::class);

        $cluster = new Cluster();
        $type = '';
        $caCertificate = '';
        $token = '';
        $username = '';
        $accountCluster->visit([
            'name' => $cluster->setName(...),
            'type' => function (string $v) use (&$type, $cluster): void {
                $type = $v;
                $cluster->setType($v);
            },
            'masterAddress' => $cluster->setAddress(...),
            'useHnc' => $cluster->useHierarchicalNamespaces(...),
            'caCertificate' => function (string $v) use (&$caCertificate): void {
                $caCertificate = $v;
            },
            'token' => function (string $v) use (&$token): void {
                $token = $v;
            },
            'username' => function (?string $v) use (&$username): void {
                $username = (string) $v;
            },
        ]);

        //As in production, only a Docker Compose cluster carries its SSH login into the project's cluster
        if ('docker-compose' !== $type) {
            $username = '';
        }

        $account->namespaceIsItDefined(
            fn (string $ns, string $pf): Cluster => $cluster->setNamespace($pf . $ns . '-' . $envName)
        );
        $cluster->setEnvironment($env = new Environment($envName));
        $cluster->setLocked(true);

        $cluster->setIdentity(
            new ClusterCredentials(
                caCertificate: $caCertificate,
                clientCertificate: '',
                clientKey: '',
                token: $token,
                username: $username,
            ),
        );

        return $cluster;
    }

    private function createAndPersistProject(
        string $projectName,
        string $prefix,
        bool $customCluster,
        ?string $clusterName = null,
        ?string $envName = null,
    ): void {

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

        if (!empty($clusterName) && !empty($envName)) {
            /** @var AccountCluster $accountCluster */
            $accountCluster = $this->recall(AccountCluster::class);
            $clusterConfig = $accountCluster->convertToConfigCluster(
                $this->sfContainer->get(ClientFactoryInterface::class),
                new RepositoryRegistry(),
            );

            $cluster = $this->createCatalogCluster($account, $clusterConfig, $credential, '', $envName);

            $project->setClusters([
                $cluster,
            ]);

            $this->persistAndRegister($cluster);
        } elseif (false === $customCluster) {
            /** @var ClusterCatalog $clustersCatalog */
            $clustersCatalog = $this->sfContainer->get('teknoo.space.clusters_catalog');

            $cluster = $this->createCatalogCluster(
                $account,
                $clustersCatalog->getCluster($this->overrideClusterName ?? $this->defaultClusterName),
                $credential,
                '',
                'prod',
            );

            $clusterDev = $this->createCatalogCluster(
                $account,
                $clustersCatalog->getCluster($this->overrideClusterName ?? $this->defaultClusterName),
                $credential,
                'dev.',
                'dev',
            );

            $project->setClusters([
                $cluster,
                $clusterDev,
            ]);

            $this->persistAndRegister($clusterDev);
            $this->persistAndRegister($cluster);
        } else {
            $cluster = $this->createCustomCluster($account);

            $project->setClusters([
                $cluster,
            ]);

            $this->persistAndRegister($cluster);
        }

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

    #[Given('a standard project :projectName and a prefix :prefix')]
    #[Given('a standard project :projectName and a prefix :prefix on :clusterName for :envName')]
    public function aStandardProjectAndAPrefix(
        string $projectName,
        string $prefix,
        ?string $clusterName = null,
        ?string $envName = null,
    ): void {
        $this->createAndPersistProject($projectName, $prefix, false, $clusterName, $envName);
    }

    #[Given('a custom project :projectName and a prefix :prefix on custom cluster')]
    public function aCustomProjectAndAPrefixOnCustomCluster(string $projectName, string $prefix): void
    {
        $this->createAndPersistProject($projectName, $prefix, true);
    }

    #[Given(":count project's variables")]
    public function andSomeProjectVariables(int $count): void
    {
        $project = $this->recall(Project::class);
        $service = $this->sfContainer->get(PersistedVariableEncryption::class);

        for ($i = 1; $i <= $count; ++$i) {
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

    #[Given('an account clusters :name and a slug :slug')]
    public function anAccountClustersAndASlug(string $name, string $slug): void
    {
        $account = $this->recall(Account::class);

        $cluster = new AccountCluster(
            account: $account,
            name: $name,
            slug: $slug,
            type: 'kubernetes',
            masterAddress: "https://kubernetes.{$slug}.behat",
            storageProvisioner: 'nfs.csi.k8s.io',
            dashboardAddress: "https://dashboard.{$slug}.behat",
            caCertificate: \base64_encode('behatCaCertificate'),
            token: \base64_encode('behatToken'),
            supportRegistry: true,
            registryUrl: "https://registry.{$slug}.behat",
            useHnc: false,
        );

        $this->persistAndRegister($cluster);
    }

    #[Given('an account clusters :name and a slug :slug on docker compose')]
    public function anAccountClustersAndASlugOnDockerCompose(string $name, string $slug): void
    {
        $account = $this->recall(Account::class);

        $cluster = new AccountCluster(
            account: $account,
            name: $name,
            slug: $slug,
            type: 'docker-compose',
            masterAddress: "ssh://deployer@docker-host.{$slug}.behat:22",
            storageProvisioner: '',
            dashboardAddress: '',
            //Docker-compose clusters keep the SSH host public key (known_hosts) as-is in the CA field, unlike the
            //Kubernetes CA which is base64-encoded: the RunnerFactory binds it verbatim to the host.
            caCertificate: 'behatKnownHosts',
            token: '',
            supportRegistry: false,
            registryUrl: null,
            useHnc: false,
            clientKey: 'fake-ssh-private-key',
            username: 'deployer',
        );

        $this->persistAndRegister($cluster);

        //Tell the docker-compose steps which SSH target this scenario deploys to (and the host public key its
        //credentials carry), so the Ansible inventory and the known_hosts rendered by the driver can be asserted.
        $this->setExpectedComposeSshTarget("docker-host.{$slug}.behat", 22, 'behatKnownHosts');
    }

    #[Given('an account environment on :clusterName for the environment :environmentName')]
    public function anAccountEnvironmentOnForTheEnvironment(string $clusterName, string $environmentName): void
    {
        $account = $this->recall(Account::class);
        $sac = mb_strtolower(str_replace(' ', '-', (string) $account));

        $namespace = '';
        $account->namespaceIsItDefined(
            function (string $ns, string $pf) use (&$namespace, $environmentName): void {
                $namespace = $pf . $ns . '-' . $environmentName;
            }
        );

        $accountCluster = $this->recall(AccountCluster::class);
        $caCertificate = '';
        $token = '';
        $accountCluster->visit([
            'caCertificate' => function (string $v) use (&$caCertificate): void {
                $caCertificate = $v;
            },
            'token' => function (string $v) use (&$token): void {
                $token = $v;
            },
        ]);

        $accountEnvironment = new AccountEnvironment(
            account: $account,
            clusterName: $clusterName,
            envName: $environmentName,
            namespace: $namespace,
            serviceAccountName:  $sac . "-{$environmentName}-account",
            roleName: $sac . "-{$environmentName}-role",
            roleBindingName: $sac . "-{$environmentName}-role-binding",
            caCertificate: $caCertificate,
            clientCertificate: "",
            clientKey: "",
            token: $token,
            metadata: [],
        );
        $accountEnvironment->setId($this->generateId());

        $this->persistAndRegister($accountEnvironment);
    }

    /**
     * Whether an entry of the persisted account histories records `$message` with an `extra` accepted by
     * `$extraMatches`.
     *
     * @param callable(array<string, mixed>): bool $extraMatches
     */
    private function accountHistoryRecords(string $message, callable $extraMatches): bool
    {
        $found = false;
        /** @var AccountHistory $accountHistory */
        foreach ($this->listObjects(AccountHistory::class) as $accountHistory) {
            $accountHistory->passMeYouHistory(
                function (History $history) use (&$found, $message, $extraMatches): void {
                    foreach ($this->historyEntries($history) as $entry) {
                        if ($message === $entry->getMessage() && $extraMatches($entry->getExtra())) {
                            $found = true;
                        }
                    }
                }
            );
        }

        return $found;
    }

    #[Then('the account history must record the quota refresh skipped for :environment on :cluster')]
    public function theAccountHistoryMustRecordTheQuotaRefreshSkippedForOn(string $environment, string $cluster): void
    {
        Assert::assertTrue(
            $this->accountHistoryRecords(
                'teknoo.space.text.account.docker_compose.quota_not_applicable',
                static fn (array $extra): bool => ($extra['environment'] ?? null) === $environment
                    && ($extra['cluster'] ?? null) === $cluster,
            ),
            "The account history does not record the skipped quota refresh of `$environment` on `$cluster`",
        );
    }

    #[Then('the account history must record the registries login for :environment on :cluster')]
    public function theAccountHistoryMustRecordTheRegistriesLoginForOn(string $environment, string $cluster): void
    {
        $accountRegistry = $this->recall(AccountRegistry::class);
        Assert::assertInstanceOf(AccountRegistry::class, $accountRegistry);

        //Only the registry hosts are recorded, never a credential
        $expectedExtra = [
            'environment' => $environment,
            'cluster' => $cluster,
            'registries' => [$accountRegistry->getRegistryUrl()],
        ];

        Assert::assertTrue(
            $this->accountHistoryRecords(
                'teknoo.space.text.account.docker_compose.registries_login',
                static fn (array $extra): bool => $extra === $expectedExtra,
            ),
            "The account history does not record the registries login for `$environment` on `$cluster`",
        );
    }

    #[Then('the project must be persisted')]
    public function theProjectMustBePersisted(): void
    {
        $this->isAFinalResponse();

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

    #[Then('the project must be updated')]
    public function theProjectMustBeUpdated(): void
    {
        $this->isAFinalResponse();

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
     * The cluster of the last project, which must have only one.
     */
    private function lastProjectSingleCluster(): Cluster
    {
        /** @var Project $project */
        $project = $this->recall(Project::class);

        $clusters = [];
        $project->visit(
            'clusters',
            static function (iterable $projectClusters) use (&$clusters): void {
                foreach ($projectClusters as $cluster) {
                    $clusters[] = $cluster;
                }
            },
        );

        Assert::assertCount(1, $clusters, 'The project must have a single cluster');
        Assert::assertInstanceOf(Cluster::class, $clusters[0]);

        return $clusters[0];
    }

    #[Then("the SSH username of the last project's cluster is :username")]
    public function theSshUsernameOfTheLastProjectsClusterIs(string $username): void
    {
        $this->lastProjectSingleCluster()->visit(
            'identity',
            static function (ClusterCredentials $identity) use ($username): void {
                Assert::assertEquals($username, $identity->getUsername());
            },
        );
    }

    #[Then("the last project's cluster uses hierarchical namespaces")]
    public function theLastProjectsClusterUsesHierarchicalNamespaces(): void
    {
        $this->lastProjectSingleCluster()->visit(
            'useHierarchicalNamespaces',
            static function (bool $useHnc): void {
                Assert::assertTrue($useHnc);
            },
        );
    }

    #[Then('the project is not deleted')]
    public function theProjectIsNotDeleted(): void
    {
        $projects = $this->listObjects(Project::class);
        Assert::assertNotEmpty($projects);
    }

    #[Then('the account cluster is not deleted')]
    public function theAccountClusterIsNotDeleted(): void
    {
        $clusters = $this->listObjects(AccountCluster::class);
        Assert::assertNotEmpty($clusters);
    }

    #[Then('there are no project persisted variables')]
    public function thereAreNoProjectPersistedVariables(): void
    {
        Assert::assertEmpty($this->listObjects(ProjectPersistedVariable::class));
    }

    #[Then('data have been saved')]
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

    #[Then('the project keeps these persisted variables')]
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
                    fn (ProjectPersistedVariable $ppv): ProjectPersistedVariable => $ppv,
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

    #[Then('the project must have these persisted variables')]
    public function theProjectMustHaveTheseePersistedVariables(TableNode $expectedVariables): void
    {
        $this->isAFinalResponse();

        $this->theProjectKeepsTheseePersistedVariables($expectedVariables);
    }

    #[Given('the project has these persisted variables:')]
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

    #[Given(':number jobs for the project')]
    public function jobsForTheProject(int $number): void
    {
        $project = $this->recall(Project::class);
        $env = $this->recall(Environment::class);
        $cluster = $this->recall(Cluster::class);
        $registry = $this->recall(ImageRegistry::class);
        $repository = $this->recall(GitRepository::class);

        for ($i = 0; $i < $number; ++$i) {
            $job = new Job();
            $job->setProject($project);
            $job->setEnvironment($env);
            $job->setClusters([$cluster]);
            $job->setSourceRepository($repository);
            $job->setImagesRegistry($registry);

            $this->persistAndRegister($job);
        }
    }

    #[When('the job is deleted')]
    public function theJobIsDeleted(): void
    {
        Assert::assertEmpty(
            $this->listObjects(Job::class),
        );
    }

    #[Then('there is a project in the memory for this account')]
    public function thereIsAProjectInTheMemoryForThisAccount(): void
    {
        Assert::assertCount(
            1,
            $this->listObjects(ProjectOrigin::class),
        );
    }

    #[Then('there is an account cluster in the memory for this account')]
    public function thereIsAnAccountClusterInTheMemoryForThisAccount(): void
    {
        Assert::assertCount(
            1,
            $this->listObjects(AccountCluster::class),
        );
    }

    #[Then('there is a user in the memory')]
    public function thereIsAUserInTheMemory(): void
    {
        Assert::assertCount(
            2,
            $this->listObjects(User::class),
        );
    }

    #[Then('there is an account in the memory')]
    public function thereIsAnAccountInTheMemory(): void
    {
        Assert::assertNotEmpty(
            $this->listObjects(User::class),
        );
    }

    #[Then('no object has been deleted')]
    public function noObjectHasBeenDeleted(): void
    {
        Assert::assertEmpty($this->removedObjects);
    }

    #[Then('the old account environment account :namespace must be deleted')]
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
     * Registries created before the cluster name was recorded hydrate `clusterName` to null: the provisioning
     * falls back to the first cluster supporting the registry, and records its name on the next reinstall.
     */
    /**
     * The persisted registry of the account, looked up in the persisted objects rather than recalled: after a
     * reinstall, the recalled registry is the replaced one.
     */
    private function findAccountRegistryOf(AccountOrigin $account): AccountRegistry
    {
        /** @var AccountRegistry $registry */
        foreach ($this->listObjects(AccountRegistry::class) as $registry) {
            if ($registry->getAccount() === $account) {
                return $registry;
            }
        }

        Assert::fail('Missing AccountRegistry');
    }

    #[Given('the account registry does not record its cluster')]
    public function theAccountRegistryDoesNotRecordItsCluster(): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        $registry = $this->findAccountRegistryOf($account);

        $legacy = new AccountRegistry(
            account: $account,
            registryNamespace: $registry->getRegistryNamespace(),
            registryUrl: $registry->getRegistryUrl(),
            registryAccountName: $registry->getRegistryAccountName(),
            registryConfigName: $registry->getRegistryConfigName(),
            registryPassword: $registry->getRegistryPassword(),
            persistentVolumeClaimName: $registry->getPersistentVolumeClaimName(),
        );
        $legacy->setId($registry->getId());

        $this->persistAndRegister($legacy);
    }

    #[Then('the account registry is recorded on the cluster :clusterName')]
    public function theAccountRegistryIsRecordedOnTheCluster(string $clusterName): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        Assert::assertSame($clusterName, $this->findAccountRegistryOf($account)->getClusterName());
    }

    #[Then('the old account registry object has been deleted and remplaced')]
    public function theOldAccountRegistryObjectHasBeenDeletedAndRemplaced(): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        Assert::assertNotEmpty($this->removedObjects[AccountRegistry::class]);
        Assert::assertCount(1, $this->removedObjects[AccountRegistry::class]);

        foreach ($this->removedObjects[AccountRegistry::class] as $oldAR) {
            break;
        }

        $ar = $this->findAccountRegistryOf($account);
        Assert::assertEquals(
            $oldAR->getRegistryNamespace(),
            $ar->getRegistryNamespace(),
        );

        //A registry stays on the cluster it was installed on: a reinstall must not move it.
        Assert::assertEquals(
            $oldAR->getClusterName(),
            $ar->getClusterName(),
        );
    }

    #[Then('the old account environment :namespace object has been deleted and remplaced')]
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

    #[Then('the project is deleted')]
    public function theProjectIsDeleted(): void
    {
        Assert::assertEmpty(
            $this->listObjects(Project::class),
        );
    }

    #[Then('the account cluster is deleted')]
    public function theAccountClusterIsDeleted(): void
    {
        Assert::assertEmpty(
            $this->listObjects(AccountCluster::class),
        );
    }

    #[Then('the user is deleted')]
    public function theUserIsDeleted(): void
    {
        Assert::assertCount(
            1,
            $this->listObjects(User::class),
        );
    }

    #[Then('the account is deleted')]
    public function theAccountIsDeleted(): void
    {
        Assert::assertEmpty(
            $this->listObjects(Account::class),
        );
    }

    #[When('the job is not deleted')]
    public function theJobIsNotDeleted(): void
    {
        Assert::assertNotEmpty(
            $this->listObjects(Job::class),
        );
    }

    /**
     * The first job persisted during the scenario: a scenario deploys a single one.
     */
    private function firstJob(): JobOrigin
    {
        $jobs = $this->listObjects(JobOrigin::class);
        Assert::assertNotEmpty($jobs);

        $job = current($jobs);
        Assert::assertInstanceOf(JobOrigin::class, $job);

        return $job;
    }

    /**
     * The last entry of the history of the job, which must be final and hold the dispatched result.
     */
    private function finalJobHistory(): History
    {
        $history = $this->firstJob()->getHistory();
        Assert::assertInstanceOf(History::class, $history);

        Assert::assertTrue($history->isFinal(), 'History is not final');
        Assert::assertEquals(DispatchResultInterface::class, $history->getMessage());

        return $history;
    }

    #[Then('job must be successful finished')]
    public function jobMustBeSuccessfulFinished(): void
    {
        $this->finalJobHistory();
    }

    #[Then('job must be finished with an error about a timeout')]
    public function jobMustBeErrorAboutTimeout(): void
    {
        $history = $this->finalJobHistory();

        Assert::assertStringContainsString(
            "Error, time limit exceeded",
            (string) ($history->getExtra()['result'][0] ?? ''),
        );
    }

    #[Then('job must be finished with an error about a :type allowed in v1')]
    public function jobMustBeErrorAboutJobNotAllowedInV1(string $type): void
    {
        $history = $this->finalJobHistory();

        Assert::assertStringContainsString(
            match ($type) {
                'job' => "jobs': This element is not expected",
                'conditions' => "if{ENV=prod}' is not a valid value of the atomic type",
                'expose shortcuts' => "services': This element is not expected",
                default => throw new LogicException('Unknown type in test'),
            },
            (string) ($history->getExtra()['result'][0] ?? ''),
        );
    }

    #[Then('job must be finished with an error about expose shortcuts not allowed in v1.1')]
    public function jobMustBeErrorAboutExposeShortcutsNotAllowedInV1dot1(): void
    {
        //Behat placeholders capture a single word, so the two-words type is forwarded explicitly.
        $this->jobMustBeErrorAboutJobNotAllowedInV1('expose shortcuts');
    }

    #[Then('job must be finished with an error about a duplicated :type')]
    public function jobMustBeErrorAboutDuplicatedExposition(string $type): void
    {
        $history = $this->finalJobHistory();

        //The AlreadyDefinedException is wrapped by the CompileDeployment step into a compilation error, its
        //message is the second entry of the result, after the compilation error translation key (like quotas).
        Assert::assertStringContainsString(
            match ($type) {
                'service' => 'Service demo-nginx is already defined in the deployment',
                'ingress' => 'Ingress demo-nginx is already defined in the deployment',
                default => throw new LogicException('Unknown type in test'),
            },
            (string) ($history->getExtra()['result'][1] ?? ''),
        );
    }

    #[Then('An account :accountName is created')]
    public function anAccountIsCreated(string $accountName): void
    {
        $accounts = $this->listObjects(AccountOrigin::class);
        Assert::assertNotEmpty($accounts);

        Assert::assertNotEmpty(
            $accountName,
            (string) current($accounts),
        );
    }

    #[Then('a user :email is created')]
    public function aUserIsCreated(string $email): void
    {
        $users = $this->listObjects(User::class);
        Assert::assertNotEmpty($users);

        Assert::assertNotEmpty(
            $email,
            current($users)->getEmail(),
        );
    }
}
