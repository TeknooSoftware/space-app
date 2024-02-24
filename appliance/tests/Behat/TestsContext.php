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

use ArrayObject;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use DateTime;
use DateTimeInterface;
use DI\Container as DiContainer;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use Http\Adapter\Guzzle7\Client as ClientAlias;
use OTPHP\TOTP;
use phpseclib3\Crypt\RSA;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\Generator\Generator;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionObject;
use RuntimeException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpClient\HttplugClient as SymfonyHttplug;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Event\MessageEvents;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mailer\Test\Constraint\EmailCount;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Doctrine\Object\Media as MediaODM;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess;
use Teknoo\East\Foundation\Liveness\TimeoutServiceInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Compilation\CompiledDeployment\Expose\Transport;
use Teknoo\East\Paas\Contracts\Compilation\ConductorInterface;
use Teknoo\East\Paas\Contracts\Hook\HooksCollectionInterface;
use Teknoo\East\Paas\Contracts\Job\JobUnitInterface;
use Teknoo\East\Paas\Contracts\Object\SourceRepositoryInterface;
use Teknoo\East\Paas\Contracts\Repository\CloningAgentInterface;
use Teknoo\East\Paas\Contracts\Workspace\FileInterface;
use Teknoo\East\Paas\Contracts\Workspace\JobWorkspaceInterface;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Account;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Job;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Project;
use Teknoo\East\Paas\Infrastructures\Image\Contracts\ProcessFactoryInterface;
use Teknoo\East\Paas\Infrastructures\PhpSecLib\Configuration\Algorithm;
use Teknoo\East\Paas\Job\History\SerialGenerator;
use Teknoo\East\Paas\Object\Account as AccountOrigin;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\GitRepository;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\Job as JobOrigin;
use Teknoo\East\Paas\Object\Project as ProjectOrigin;
use Teknoo\East\Paas\Object\SshIdentity;
use Teknoo\East\Paas\Object\XRegistryAuth;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Kubernetes\HttpClient\Instantiator\Symfony;
use Teknoo\Kubernetes\HttpClientDiscovery;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Account\SpaceSubscriptionType;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\PersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Tests\Behat\ODM\GridFSMemoryRepository;
use Teknoo\Space\Tests\Behat\ODM\MemoryObjectManager;
use Teknoo\Space\Tests\Behat\ODM\MemoryRepository;
use Traversable;
use Zenstruck\Messenger\Test\Transport\TestTransportRegistry;

use function array_merge;
use function array_shift;
use function array_slice;
use function array_values;
use function class_exists;
use function count;
use function current;
use function end;
use function explode;
use function file_exists;
use function get_parent_class;
use function hash;
use function in_array;
use function is_array;
use function is_iterable;
use function is_readable;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function key;
use function mb_strtolower;
use function method_exists;
use function random_int;
use function spl_object_hash;
use function str_contains;
use function str_replace;
use function substr;
use function trim;
use function uniqid;

class TestsContext implements Context
{
    private array $cookies = [];

    private string $clientIp = '127.0.0.1';

    private ?Request $request = null;

    private ?Response $response = null;

    private ?Container $sfContainer = null;

    private ?ObjectManager $objectManager = null;

    /**
     * @var DocumentRepository[]
     */
    private array $repositories = [];

    /**
     * @var array<string, ObjectInterface>
     */
    private array $objects = [];

    private ?string $currentUrl = null;

    /**
     * @var array<string, mixed>
     */
    private array $workMemory = [];

    private bool $hasBeenRedirected = false;

    private bool $isApiCall = false;

    private ?string $formName = null;

    private ?string $originalProjectName = null;

    private ?string $paasFile = null;

    private ?string $projectPrefix;

    public bool $slowBuilder = false;

    private bool $useHnc = false;

    private string $hncSuffix = '';

    private array $manifests = [];

    private ?string $jwtToken = null;

    private ?int $itemsPerPages = null;

    private ?HooksCollectionInterface $hookCollection = null;

    private ?string $apiPendingJobUrl = null;

    private string $privateKey = __DIR__ . '/../var/keys/private.pem';

    private string $publicKey = __DIR__ . '/../var/keys/public.pem';

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly GoogleAuthenticatorInterface $authenticator,
        private readonly TranslatorInterface $translator,
        private readonly GetTokenStorageService $getTokenStorageService,
        private readonly TestTransportRegistry $testTransport,
        private readonly TimeoutServiceInterface $timeoutService,
        private readonly CacheItemPoolInterface $cacheExpiredLinks,
        private readonly NormalizerInterface $normalizer,
        private readonly ?MessageLoggerListener $messageLoggerListener,
        private readonly string $appHostname,
        private readonly string $defaultClusterName,
        private readonly string $defaultClusterType,
        private readonly string $defaultClusterAddress,
        private readonly string $defaultClusterEnv,
    ) {
    }

    /**
     * @BeforeScenario
     */
    public function prepareScenario(): void
    {
        $this->request = null;
        $this->response = null;
        $this->sfContainer = null;
        $this->objectManager = null;
        $this->repositories = [];
        $this->objects = [];
        $this->cookies = [];
        $this->clientIp = '127.0.0.1';
        $this->currentUrl = null;
        $this->workMemory = [];
        $this->hasBeenRedirected = false;
        $this->formName = null;
        $this->originalProjectName = null;
        $this->paasFile = null;
        $this->slowBuilder = false;
        $this->useHnc = false;
        $this->hncSuffix = '';
        $this->manifests = [];
        $this->projectPrefix = null;
        Query::$testsContext = $this;
        Query::$testsObjecttManager = null;
        $this->hookCollection = null;
        $this->jwtToken = null;
        $this->itemsPerPages = null;
        $this->getTokenStorageService->tokenStorage?->setToken(null);
        $this->apiPendingJobUrl = null;
        $this->timeoutService->disable();

        if (!empty($_ENV['TEKNOO_PAAS_SECURITY_ALGORITHM'])) {
            unset($_ENV['TEKNOO_PAAS_SECURITY_ALGORITHM']);
        }

        if (!empty($_ENV['TEKNOO_PAAS_SECURITY_PRIVATE_KEY'])) {
            unset($_ENV['TEKNOO_PAAS_SECURITY_PRIVATE_KEY']);
        }

        if (!empty($_ENV['TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE'])) {
            unset($_ENV['TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE']);
        }

        if (!empty($_ENV['TEKNOO_PAAS_SECURITY_PUBLIC_KEY'])) {
            unset($_ENV['TEKNOO_PAAS_SECURITY_PUBLIC_KEY']);
        }
    }

    /**
     * @AfterScenario
     */
    public function cleanScenario(): void
    {
        Query::$testsContext = $this;
        Query::$testsObjecttManager = null;
        $this->getTokenStorageService->tokenStorage?->setToken(null);
        $this->timeoutService->disable();
        $this->cacheExpiredLinks->clear();
    }

    private function setDateTime(DateTimeInterface $dateTime): void
    {
        $this->sfContainer?->get(DatesService::class)
            ->setCurrentDate($dateTime);
    }

    private function setSerialGenerator(callable $generator): void
    {
        $this->sfContainer?->get(SerialGenerator::class)
            ->setGenerator($generator);
    }

    /**
     * @Given A Space app instance
     */
    public function aSpaceAppInstance(): void
    {
        $this->kernel->boot();
        $this->sfContainer = $this->kernel->getContainer();

        $this->setDateTime(new DateTime('2018-10-01 02:03:04', new \DateTimeZone('UTC')));
        $counter = 0;
        $this->setSerialGenerator(
            function () use (&$counter) {
                return ++$counter;
            }
        );
    }

    /**
     * @Given encryption capacities between servers and agents
     */
    public function encryptionCapacitiesBetweenServersAndAgents(): void
    {
        if (!file_exists($this->privateKey) || !is_readable($this->privateKey)) {
            $pk = RSA::createKey(1024);

            file_put_contents($this->privateKey, $pk->toString('PKCS8'));
            file_put_contents($this->publicKey, $pk->getPublicKey()->toString('PKCS8'));
        }

        $_ENV['TEKNOO_PAAS_SECURITY_ALGORITHM'] = Algorithm::RSA->value;
        $_ENV['TEKNOO_PAAS_SECURITY_PRIVATE_KEY'] = $this->privateKey;
        $_ENV['TEKNOO_PAAS_SECURITY_PUBLIC_KEY'] = $this->publicKey;
    }

    /**
     * @Given A kubernetes client
     */
    public function aKubernetesClient(): void
    {
        MockClientInstantiator::$testsContext = $this;

        if (class_exists(SymfonyHttplug::class)) {
            HttpClientDiscovery::registerInstantiator(SymfonyHttplug::class, MockClientInstantiator::class);
        }

        if (class_exists(ClientAlias::class)) {
            HttpClientDiscovery::registerInstantiator(ClientAlias::class, MockClientInstantiator::class);
        }
    }

    public function setManifests(string $uri, array $manifests): void
    {
        if (isset($manifests['metadata']['labels']['id'])) {
            $manifests['metadata']['labels']['id'] = '#ID#';
        }

        $this->manifests[$uri][] = $manifests;
    }

    public function findObjectById(string $className, mixed $id): ?object
    {
        foreach ($this->objects[$className] ?? [] as $object) {
            if (!$object instanceof IdentifiedObjectInterface) {
                continue;
            }

            if ($object->getId() == $id) {
                return $object;
            }
        }

        return null;
    }

    private function checkValue(mixed $expected, mixed $value): bool
    {
        if (is_array($value)) {
            return match (key($value)) {
                '$in' => in_array($expected, current($value)),
                '$nin' => !in_array($expected, current($value)),
                default => $expected === $value,
            };
        }

        return $expected === $value;
    }

    private function checkACriteria(string $criteria, mixed &$value, ReflectionObject $roInstance, object $object): bool
    {
        $checkByGetter = function (string $method, mixed $value) use ($roInstance, $object): bool {
            if (!$roInstance->hasMethod($method)) {
                return false;
            }

            return $this->checkValue($object->{$method}(), $value);
        };

        if ('$or' === $criteria) {
            $valid = false;
            foreach ($value as $subCriterias) {
                $valid = $this->checkListOfCriteria($subCriterias, $roInstance, $object);

                if ($valid) {
                    break;
                }
            }

            return $valid;
        }

        if ('$and' === $criteria) {
            $valid = true;
            foreach ($value as $subCriterias) {
                $valid = $valid && $this->checkListOfCriteria($subCriterias, $roInstance, $object);

                if (!$valid) {
                    break;
                }
            }

            return $valid;
        }

        if (str_contains($criteria, '.$')) {
            [$property, $attr] = explode('.$', $criteria);

            if ($roInstance->hasProperty($property)) {
                $rp = $roInstance->getProperty($property);
                $rp->setAccessible(true);

                $subObjectsList = $rp->getValue($object);
                if (!is_iterable($subObjectsList)) {
                    $subObjectsList = [$subObjectsList];
                }

                foreach ($subObjectsList as &$subObject) {
                    $sro = new ReflectionObject($subObject);
                    if ($this->checkACriteria($attr, $value, $sro, $subObject)) {
                        return true;
                    }
                }

                return false;
            }
        }

        if ($roInstance->hasProperty($criteria)) {
            $rp = $roInstance->getProperty($criteria);
            $rp->setAccessible(true);

            if ($this->checkValue($rp->getValue($object), $value)) {
                return true;
            }
        }

        if ($checkByGetter("get{$criteria}", $value)) {
            return true;
        }

        if ($checkByGetter("is{$criteria}", $value)) {
            return true;
        }

        if ($checkByGetter("has{$criteria}", $value)) {
            return true;
        }

        return false;
    }

    private function checkListOfCriteria(array $criteria, ReflectionObject $roInstance, object $object): bool
    {
        foreach ($criteria as $name => &$value) {
            if (!$this->checkACriteria($name, $value, $roInstance, $object)) {
                return false;
            }
        }

        return true;
    }

    public function findObjectsBycriteria(
        string $className,
        array $criteria,
        ?int $limit = null,
    ): iterable {
        $final = [];
        do {
            if (empty($this->objects[$className])) {
                continue;
            }

            $final = [];
            foreach ($this->objects[$className] as $object) {
                $roInstance = new ReflectionObject($object);

                if ($this->checkListOfCriteria($criteria, $roInstance, $object)) {
                    $final[] = $object;
                }

                if (null !== $limit && count($final) >= $limit) {
                    break;
                }
            }
        } while (empty($final) && !empty($className = get_parent_class($className)));

        return $final;
    }

    public function listObjects(string $className): iterable
    {
        return $this->objects[$className] ?? [];
    }

    private function getObjectUniqueId(object $object): string
    {
        if ($object instanceof IdentifiedObjectInterface) {
            return 'IdentifiedObjectInterface:' . $object->getId();
        }

        return spl_object_hash($object);
    }

    public function getListOfPersistedObjects(string $class): array
    {
        return $this->objects[$class];
    }

    public function persistObject(object $object): void
    {
        if ($object instanceof IdentifiedObjectInterface) {
            if (
                empty($object->getId())
                && method_exists($object, 'setId')
            ) {
                $object->setId($this->generateId());
            }
        }

        $this->objects[$object::class][$this->getObjectUniqueId($object)] = $object;
    }

    public function removeObject(object $object): void
    {
        if (isset($this->objects[$object::class][$this->getObjectUniqueId($object)])) {
            unset($this->objects[$object::class][$this->getObjectUniqueId($object)]);
        }
    }

    public function clearObjects(): void
    {
        $this->objects = [];
    }

    public function buildObjectManager(): ObjectManager
    {
        $this->objectManager ??= new MemoryObjectManager($this->getRepository(...), $this);

        Query::$testsObjecttManager = $this->objectManager;
        return $this->objectManager;
    }

    public function getRepository(string $className): DocumentRepository
    {
        if (!isset($this->repositories[$className])) {
            throw new RuntimeException("Missing $className");
        }

        return $this->repositories[$className];
    }

    private function buildRepository(string $className): DocumentRepository
    {
        return $this->repositories[$className] = new MemoryRepository(
            $className,
            $this->buildObjectManager(),
            $this,
        );
    }

    private function buildGridFSRepository(string $className): DocumentRepository
    {
        return $this->repositories[$className] = new GridFSMemoryRepository(
            $className,
            $this->buildObjectManager(),
            $this,
        );
    }

    /**
     * @Given A memory document database
     */
    public function aMemoryDocumentDatabase(): void
    {
        $this->sfContainer->set(ObjectManager::class, $this->buildObjectManager());

        $this->buildRepository(User::class);
        $this->buildGridFSRepository(MediaODM::class);

        $this->buildRepository(Account::class);
        $this->buildRepository(Cluster::class);
        $this->buildRepository(Job::class);
        $this->buildRepository(Project::class);

        $this->buildRepository(AccountCredential::class);
        $this->buildRepository(AccountData::class);
        $this->buildRepository(AccountHistory::class);
        $this->buildRepository(AccountPersistedVariable::class);
        $this->buildRepository(PersistedVariable::class);
        $this->buildRepository(ProjectMetadata::class);
        $this->buildRepository(UserData::class);
    }

    /**
     * @param Cookie[] $cookies
     * @return array
     */
    private function extractCookies(array &$cookies): array
    {
        $final = [];
        foreach ($cookies as $cookie) {
            $final[$cookie->getName()] = $cookie->getValue();
        }

        return $final;
    }

    private function getPathFromRoute(string $route, array $parameters = []): string
    {
        return $this->urlGenerator->generate(
            name: $route,
            parameters: $parameters,
        );
    }

    private function executeRequest(
        string $method,
        string $url,
        array $params = [],
        array $headers = [],
        bool $noCookies = false,
        bool $clearCookies = false,
        ?string $content = null,
    ): Response {
        $this->hasBeenRedirected = false;
        $this->isApiCall = !empty($headers['HTTP_AUTHORIZATION']);

        $host = $originalHost = 'https://' . $this->appHostname;
        if (true === str_starts_with($url, 'http')) {
            $host = '';
        }

        $cookies = [];
        if (false === $noCookies) {
            $cookies = $this->cookies;
        }

        $this->currentUrl = str_replace($originalHost, '', $url);

        $this->request = Request::create(
            uri: $host . $url,
            method: $method,
            parameters: $params,
            cookies: $cookies,
            server: array_merge(
                [
                    'REMOTE_ADDR' => $this->clientIp,
                ],
                $headers,
            ),
            content: $content,
        );

        $this->response = $this->kernel->handle($this->request);

        if (true === $clearCookies) {
            $this->cookies = [];
        } elseif (
            false === $clearCookies
            && false === $noCookies
            && !empty($cookies = $this->response->headers->getCookies())
        ) {
            $this->cookies = array_merge($this->cookies, $this->extractCookies($cookies));
        }

        if (302 === $this->response->getStatusCode()) {
            if ($this->response instanceof RedirectResponse) {
                $newUrl = $this->response->getTargetUrl();
            } else {
                $newUrl = $this->response->headers->get('location');
            }

            if (isset($headers['CONTENT_TYPE'])) {
                unset($headers['CONTENT_TYPE']);
            }

            $response = $this->executeRequest(
                method: 'GET',
                url: $newUrl,
                headers: $headers,
            );
            $this->hasBeenRedirected = true;

            return $response;
        }

        return $this->response;
    }

    private function createCrawler(?string $url = null, ?Response $response = null): Crawler
    {
        $url ??= 'https://' . $this->appHostname . $this->currentUrl;
        $response ??= $this->response;
        $crawler = new Crawler(null, $url);
        $crawler->addContent($response->getContent(), $response->headers->get('Content-Type'));

        return $crawler;
    }

    private function findUrlFromRouteInPageAndOpenIt(Crawler $crawler, string $routeName, array $parameters = []): void
    {
        $this->checkIfResponseIsAFinal();

        $url = $this->getPathFromRoute($routeName, $parameters);
        $node = $crawler->filter("a[href=\"{$url}\"]");

        if (0 === $node->count()) {
            Assert::fail("The route '{$routeName}' with url '{$url}' was not found");
        }

        $this->executeRequest('GET', $url);
    }

    private function getCSRFToken(Crawler $crawler, ?string $formName = null, ?string $fieldName = null): ?string
    {
        $fieldName ??= "{$formName}[_token]";
        $field = $crawler->filter("[name=\"{$fieldName}\"]");

        return $field?->getNode(0)?->attributes->getNamedItem('value')->value;
    }

    private function register(object $object): void
    {
        $this->workMemory[$object::class] = $object;
    }

    private function persistAndRegister(object $object): void
    {
        $this->persistObject($object);
        $this->register($object);
    }

    /**
     * @param class-string $classname
     * @return object|null
     */
    private function recall(string $classname): ?object
    {
        return $this->workMemory[$classname] ?? null;
    }

    private function generateId(): string
    {
        return hash(
            algo: 'sha256',
            data: uniqid('space', true)
        );
    }

    private function checkIfUserHasBeenRedirected(): void
    {
        Assert::assertTrue($this->hasBeenRedirected);
        $this->hasBeenRedirected = false;
    }

    public function checkIfResponseIsAFinal(): void
    {
        Assert::assertEquals(200, $this->response?->getStatusCode());
    }

    /**
     * @Given an account for :accountName with the account namespace :accountNamespace
     */
    public function anAccountForWithTheAccountNamespace(string $accountName, string $accountNamespace): void
    {
        $account = new Account();
        $account->setId($this->generateId());
        $account->setName($accountName);
        $account->setNamespace($accountNamespace);
        $account->setUseHierarchicalNamespaces($this->useHnc);
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
        );
        $accountData->setId($this->generateId());

        $this->persistAndRegister($accountData);

        $sac = mb_strtolower(str_replace(' ', '-', $accountName));
        $accountCredentials = new AccountCredential(
            account: $account,
            clusterName: 'Behat Test Cluster',
            registryUrl: $sac . '.registry.demo.teknoo.space',
            registryAccountName: $sac . '-registry',
            registryConfigName: $sac . 'docker-config',
            registryPassword: $sac . '-foobar',
            serviceAccountName:  $sac . '-account',
            roleName: $sac . '-role',
            roleBindingName: $sac . '-role-binding',
            caCertificate: "-----BEGIN CERTIFICATE-----FooBar",
            clientCertificate: "",
            clientKey: "",
            token: "aFakeToken",
            persistentVolumeClaimName: $sac . '-pvc',
        );
        $accountCredentials->setId($this->generateId());

        $this->persistAndRegister($accountCredentials);
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
     * @Given the platform is booted
     */
    public function thePlatformIsBooted(): void
    {
    }

    /**
     * @When It goes to account settings
     */
    public function itGoesToAccountSettings(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_account_settings',
        );

        $this->formName = 'space_account';
    }

    /**
     * @When open the account variables page
     */
    public function openTheAccountVariablesPage(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_account_edit_variables',
        );

        $this->formName = 'account_vars';
    }

    private function createForm(string $formName, ?Crawler $crawler = null): Form
    {
        $crawler ??= $this->createCrawler();

        return $crawler->filter("[name=\"{$formName}\"]")->form();
    }

    private function setRequestParameters(
        array &$final,
        string $dottedField,
        mixed $value,
        ?array $fieldsList = null,
    ): void {
        if (null === $fieldsList) {
            $fieldsList = explode('.', $dottedField);
        }

        $fieldName = array_shift($fieldsList);
        if (empty($fieldsList)) {
            $final[$fieldName] = $value;

            return;
        }

        if (!isset($final[$fieldName])) {
            $final[$fieldName] = [];
        }

        $this->setRequestParameters($final[$fieldName], $dottedField, $value, $fieldsList);
    }

    private function validFormFieldValue(
        array &$formValues,
        string $dottedField,
        mixed $value,
        ?array $fieldsList = null,
        string $prefix = '',
    ): void {
        if (null === $fieldsList) {
            $fieldsList = explode('.', $dottedField);
        }

        $fieldName = array_shift($fieldsList);
        if (empty($fieldsList)) {
            if ('id' === $fieldName && 'x' === $value) {
                Assert::assertNotEmpty(
                    $value,
                    "Invalid value for key {$prefix}{$fieldName}"
                );
            } elseif ('id' === $fieldName && 'x' !== $value) {
                Assert::assertEquals(
                    substr($formValues[$fieldName] ?? '', 0, 3),
                    $value,
                    "Invalid value for key {$prefix}{$fieldName}",
                );
            } else {
                Assert::assertEquals(
                    $formValues[$fieldName] ?? null,
                    $value,
                    "Invalid value for key {$prefix}{$fieldName}",
                );
            }

            return;
        }

        if (!isset($formValues[$fieldName])) {
            Assert::fail("Missing key {$prefix}{$fieldName}");
        }

        $this->validFormFieldValue(
            formValues: $formValues[$fieldName],
            dottedField: $dottedField,
            value: $value,
            fieldsList: $fieldsList,
            prefix: $prefix . $fieldName . '.',
        );
    }

    private function getFormFieldValue(
        array &$formValues,
        string $dottedField,
        ?array $fieldsList = null,
    ): mixed {
        if (null === $fieldsList) {
            $fieldsList = explode('.', $dottedField);
        }

        $fieldName = array_shift($fieldsList);
        if (empty($fieldsList)) {
            return $formValues[$fieldName] ?? null;
        }

        if (!isset($formValues[$fieldName])) {
            return null;
        }

        return $this->getFormFieldValue(
            formValues: $formValues[$fieldName],
            dottedField: $dottedField,
            fieldsList: $fieldsList,
        );
    }

    /**
     * @Then it obtains a empty account's variables form
     */
    public function itObtainsAEmptyAccountsVariablesForm(): void
    {
        $formValues = $this->createForm('account_vars')->getPhpValues();
        Assert::assertFalse(isset($formValues['account_vars']['sets']));
    }

    /**
     * @When it submits the form:
     */
    public function itSubmitsTheForm(TableNode $formFields): void
    {
        Assert::assertNotEmpty($this->formName);

        $form = $this->createForm($this->formName);
        $final = [];
        $formValue = $form->getPhpValues();

        foreach ($formFields as $field) {
            if ('<auto>' === $field['value']) {
                $field['value'] = $this->getFormFieldValue($formValue, $field['field']);
            }

            $this->setRequestParameters($final, $field['field'], $field['value']);
        }

        $this->executeRequest(
            method: $form->getMethod(),
            url: $form->getUri(),
            params: $final
        );
    }

    /**
     * @Then the account must have these persisted variables
     */
    public function theAccountMustHaveTheseePersistedVariables(TableNode $expectedVariables): void
    {
        $this->checkIfResponseIsAFinal();

        if (!$this->isApiCall) {
            $crawler = $this->createCrawler();
            $node = $crawler->filter('.space-form-success');
            $nodeValue = trim((string)$node?->getNode(0)?->textContent);
            Assert::assertEquals(
                $this->translator->trans('teknoo.space.alert.data_saved'),
                $nodeValue,
            );
        }

        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);
        $vars = $this->getRepository(AccountPersistedVariable::class)->findBy(['account' => $account]);
        Assert::assertCount(count($expectedVariables->getLines()) - 1, $vars);

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

            Assert::assertEquals(
                $expVar['value'],
                $var->getValue(),
            );

            Assert::assertEquals(
                $expVar['environment'],
                $var->getEnvironmentName(),
            );
        }
    }

    /**
     * @Then the user obtains the form:
     */
    public function theUserObtainsTheForm(TableNode $formFields): void
    {
        $formValues = $this->createForm($this->formName)->getPhpValues();
        foreach ($formFields as $field) {
            $this->validFormFieldValue(
                formValues: $formValues,
                dottedField: $field['field'],
                value: $field['value'],
            );
        }
    }

    /**
     * @Given the account have these persisted variables:
     */
    public function theAccountHaveThesePersistedVariables(TableNode $variables): void
    {
        $account = $this->recall(Account::class);
        foreach ($variables as $var) {
            $apv = new AccountPersistedVariable(
                account: $account,
                id: $var['id'],
                name: $var['name'],
                value: $var['value'],
                environmentName: $var['environment'],
                secret: !empty($var['secret']),
            );

            $this->persistObject($apv);
        }
    }

    /**
     * @When the user sign in with :email and the password :password
     */
    public function theUserSignInWithAndThePassword(string $email, string $password): void
    {
        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'account_login',
            ),
        );

        $crawler = $this->createCrawler();
        $token = $this->getCSRFToken(crawler: $crawler, fieldName: '_csrf_token');

        $this->executeRequest(
            method: 'post',
            url: $this->getPathFromRoute(
                route: 'account_check',
            ),
            params: [
                '_username' => $email,
                '_password' => $password,
                '_csrf_token' => $token,
            ],
        );
    }

    /**
     * @Then it is redirected to the dashboard
     */
    public function itIsRedirectedToTheDashboard(): void
    {
        $this->checkIfUserHasBeenRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('space_dashboard'),
            $this->currentUrl,
        );
    }

    /**
     * @Then It has a welcome message with :fullName in the dashboard header
     */
    public function itHasAWelcomeMessageWithInTheDashboardHeader(string $fullName): void
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();

        $node = $crawler->filter('h6#welcome-message');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertEquals(
            $this->translator->trans('teknoo.space.text.welcome_back', ['user' => $fullName,]),
            $nodeValue,
        );
    }

    /**
     * @Then it must redirected to the TOTP code page
     */
    public function itMustRedirectedToTheTotpCodePage(): void
    {
        $this->checkIfUserHasBeenRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('2fa_login'),
            $this->currentUrl,
        );
    }

    /**
     * @Then it must have a TOTP error
     */
    public function itMustHaveATotpError(): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('p#2fa-error');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertNotEmpty($nodeValue);
    }

    private function submitTotpCode(string $code): void
    {
        $this->executeRequest(
            method: 'post',
            url: $this->getPathFromRoute(
                route: '2fa_login_check',
            ),
            params: [
                '_auth_code' => $code,
            ],
        );
    }

    /**
     * @When the user enter a valid TOTP code
     */
    public function theUserEnterAValidTotpCode(): void
    {
        $this->submitTotpCode(
            code: TOTP::createFromSecret(
                secret: (string) $this->recall(TOTPAuth::class)?->getTopSecret()
            )->now()
        );
    }

    /**
     * @Then it is redirected to the login page with an error
     */
    public function itIsRedirectedToTheLoginPageWithAnError(): void
    {
        $this->checkIfUserHasBeenRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('account_login'),
            $this->currentUrl,
        );

        $crawler = $this->createCrawler();
        $node = $crawler->filter('#login-error');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertNotEmpty($nodeValue);
    }

    /**
     * @When the user enter a wrong TOTP code
     */
    public function theUserEnterAWrongTotpCode(): void
    {
        $this->submitTotpCode(
            code: 'fooBar'
        );
    }

    /**
     * @Given a standard website project :projectName
     */
    public function aStandardWebsiteProject(string $projectName): void
    {
        /** @var AccountCredential $credential */
        $credential = $this->recall(AccountCredential::class);

        $account = $this->recall(Account::class);
        $project = new Project($account);
        $project->setId($this->generateId());
        $this->originalProjectName = $projectName;
        $project->setName($projectName);
        $project->setPrefix($this->projectPrefix = '');

        $project->setImagesRegistry(
            repository: new ImageRegistry(
                apiUrl: $credential->getRegistryUrl(),
                identity: new XRegistryAuth(
                    username: $credential->getRegistryAccountName(),
                    password: $credential->getRegistryPassword(),
                    auth: $credential->getRegistryConfigName(),
                    serverAddress: $credential->getRegistryUrl(),
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
        $cluster->setEnvironment(new Environment($this->defaultClusterEnv));
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
        /** @var AccountCredential $credential */
        $credential = $this->recall(AccountCredential::class);

        $account = $this->recall(Account::class);
        $project = new Project($account);
        $project->setId($this->generateId());
        $this->originalProjectName = $projectName;
        $project->setName($projectName);
        $project->setPrefix($this->projectPrefix = $prefix);

        $project->setImagesRegistry(
            repository: $imageRegistry = new ImageRegistry(
                apiUrl: $credential->getRegistryUrl(),
                identity: new XRegistryAuth(
                    username: $credential->getRegistryAccountName(),
                    password: $credential->getRegistryPassword(),
                    auth: $credential->getRegistryConfigName(),
                    serverAddress: $credential->getRegistryUrl(),
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
        $cluster->setEnvironment($env = new Environment($this->defaultClusterEnv));
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

        for ($i = 1; $i <= $count; $i++) {
            $pVar = new PersistedVariable(
                project: $project,
                id: null,
                name: 'var ' . $i,
                value: 'value ' . $i,
                environmentName: 'prod',
                secret: ($i % 3) === 0
            );

            $this->persistAndRegister($pVar);
        }
    }

    /**
     * @When It goes to projects list page
     */
    public function itGoesToProjectsListPage(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_list',
        );
    }

    /**
     * @Then the user obtains a project list:
     */
    public function theUserObtainsAProjectList(TableNode $projects): void
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();

        $final = [];
        $nodes = $crawler->filter('.space-project-name');
        foreach ($nodes as $node) {
            $final[] = [
                trim((string)$node?->textContent),
            ];
        }

        $expectedProjects = $projects->getRows();
        array_shift($expectedProjects);

        Assert::assertEquals(
            $expectedProjects,
            $final,
        );
    }

    /**
     * @When It goes to new project page
     */
    public function itGoesToNewProjectPage(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_list',
        );

        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_new',
        );

        $this->formName = 'space_project';
    }

    /**
     * @Then it obtains a empty project's form
     */
    public function itObtainsAEmptyProjectsForm(): void
    {
        $formValues = $this->createForm('space_project')->getPhpValues();
        Assert::assertEmpty($formValues['space_project']['project']['name']);
    }

    /**
     * @Then the project must be persisted
     */
    public function theProjectMustBePersisted(): void
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);
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
     * @When it opens the project page of :projectName
     */
    public function itOpensTheProjectPageOf(string $projectName): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_edit',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->formName = 'space_project';
    }

    /**
     * @Then the project must be updated
     */
    public function theProjectMustBeUpdated(): void
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);
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
     * @When It goes to project page of :projectName of :accountName
     */
    public function itGoesToProjectPageOfOf(string $projectName, string $accountName): void
    {
        $this->checkIfResponseIsAFinal();

        $url = $this->getPathFromRoute(
            route: 'space_project_edit',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->executeRequest('GET', $url);
    }

    /**
     * @Then the user must have a :code error
     */
    public function theUserMustHaveAError(int $code): void
    {
        Assert::assertEquals($code, $this->response?->getStatusCode());
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
     * @When It goes to delete the project :projectName of :accountName
     */
    public function itGoesToDeleteTheProjectOf(string $projectName, string $accountName)
    {
        $this->checkIfResponseIsAFinal();

        $url = $this->getPathFromRoute(
            route: 'space_project_delete',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->executeRequest('GET', $url);
    }

    /**
     * @When it goes to project page of :projectName
     */
    public function itGoesToProjectPageOf(string $projectName): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_edit',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );
    }

    /**
     * @When open the project variables page
     */
    public function openTheProjectVariablesPage(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_edit_variables',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->formName = 'project_vars';
    }

    /**
     * @Then it obtains a empty project's variables form
     */
    public function itObtainsAEmptyProjectsVariablesForm(): void
    {
        $formValues = $this->createForm('project_vars')->getPhpValues();
        Assert::assertFalse(isset($formValues['project_vars']['sets']));
    }

    /**
     * @Then the project must have these persisted variables
     */
    public function theProjectMustHaveTheseePersistedVariables(TableNode $expectedVariables): void
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();
        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);
        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );

        $vars = $this->listObjects(PersistedVariable::class);
        Assert::assertCount(count($expectedVariables->getLines()) - 1, $vars);
        $project = $this->recall(Project::class);

        foreach ($expectedVariables as $expVar) {
            $var = array_shift($vars);
            /** @var PersistedVariable $var */
            Assert::assertInstanceOf(PersistedVariable::class, $var);

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

            Assert::assertEquals(
                $expVar['value'],
                $var->getValue(),
            );

            Assert::assertEquals(
                $expVar['environment'],
                $var->getEnvironmentName(),
            );
        }
    }

    /**
     * @Given the project have these persisted variables:
     */
    public function theProjectHaveThesePersistedVariables(TableNode $variables): void
    {
        $project = $this->recall(Project::class);
        foreach ($variables as $var) {
            $apv = new PersistedVariable(
                project: $project,
                id: $var['id'],
                name: $var['name'],
                value: $var['value'],
                environmentName: $var['environment'],
                secret: !empty($var['secret']),
            );

            $this->persistObject($apv);
        }
    }

    /**
     * @Given the project has a complete paas file
     */
    public function theProjectHasACompletePaasFile(): void
    {
        $this->paasFile = __DIR__ . '/Project/Default/paas.yaml';
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
     * @When get a JWT token for the user
     */
    public function getAJwtTokenForTheUser(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_my_settings_token',
        );

        $dateInFuture = new DateTime('now'); //Use now, because JWT Bundle does not use DatesService
        $dateInFuture->modify("+2 days");

        $values = $this->createForm('jwt_configuration')->getPhpValues();
        $values['jwt_configuration']['expirationDate'] = $dateInFuture->format('Y-m-d');

        $this->executeRequest(
            method: 'POST',
            url: $this->getPathFromRoute('space_my_settings_token'),
            params: $values
        );

        $node = $this->createCrawler()->filter('.jwt-token-value');
        $this->jwtToken = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertNotEmpty($this->jwtToken);
    }

    /**
     * @When the API is called to list of jobs
     */
    public function theApiIsCalledToListOfJobs(): void
    {
        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_api_job_list',
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
                    'admin' => 'space_api_admin_project_list',
                    default => 'space_api_project_list',
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
                route: 'space_api_admin_user_list',
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
                route: 'space_api_admin_account_list',
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
                route: 'space_api_job_get',
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
        $this->theApiIsCalledToGetTheLastProject('space_api_admin_project_edit');
    }

    /**
     * @When the API is called to get the last project
     */
    public function theApiIsCalledToGetTheLastProject(string $routeName = 'space_api_project_edit'): void
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
                route: 'space_api_admin_user_edit',
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
                route: 'space_api_admin_account_edit',
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
        $this->theApiIsCalledToGetTheLastProject('space_api_project_edit_variables');
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
                route: 'space_api_job_get',
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
     */
    public function theApiIsCalledToDeleteTheLastProject(string $method = 'GET'): void
    {
        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: 'space_api_project_delete',
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
    public function theApiIsCalledToDeleteTheLastUser(string $method = 'GET'): void
    {
        $user = $this->recall(User::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: 'space_api_admin_user_delete',
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
    public function theApiIsCalledToDeleteTheLastAccount(string $method = 'GET'): void
    {
        $account = $this->recall(Account::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: 'space_api_admin_account_delete',
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
    public function theApiIsCalledToDeleteTheLastJob(string $method = 'GET'): void
    {
        $project = $this->recall(Project::class);
        $job = $this->recall(Job::class);

        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: 'space_api_job_delete',
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

    private function encodeAPIBody(TableNode $bodyFields, string $format = 'default'): string|array
    {
        $final = [];
        foreach ($bodyFields as $field) {
            $this->setRequestParameters($final, $field['field'], $field['value']);
        }

        return match ($format) {
            'json' => json_encode($final),
            default => $final,
        };
    }

    private function submitValuesThroughAPI(string $url, TableNode $bodyFields, string $format = 'default'): void
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
        $this->theApiIsCalledToEditAProject($bodyFields, 'json', 'space_api_project_edit_variables');
    }

    /**
     * @When the API is called to edit a project's variables:
     */
    public function theApiIsCalledToEditAProjectsVariables(TableNode $bodyFields): void
    {
        $this->theApiIsCalledToEditAProject($bodyFields, 'form', 'space_api_project_edit_variables');
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
            routeName: 'space_api_admin_project_edit',
        );
    }

    /**
     * @When the API is called to edit a project:
     */
    public function theApiIsCalledToEditAProject(
        TableNode $bodyFields,
        string $format = 'default',
        string $routeName = 'space_api_project_edit',
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
                route: 'space_api_admin_project_new',
                parameters: [
                    'accountId' => $this->recall(Account::class)->getId(),
                ]
            );
        } else {
            $url = $this->getPathFromRoute(
                route: 'space_api_project_new',
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
                route: 'space_api_admin_user_new',
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
                route: 'space_api_admin_account_new',
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
                route: 'space_api_admin_user_edit',
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
                route: 'space_api_admin_account_edit',
                parameters: [
                    'id' => $account->getId(),
                ]
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
    }

    /**
     * @When the API is called to get user's settings
     */
    public function theApiIsCalledToGetUsersSettings(): void
    {
        $this->executeRequest(
            method: 'get',
            url: $this->getPathFromRoute('space_api_my_settings'),
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
            url: $this->getPathFromRoute('space_api_account_settings'),
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
                route: 'space_api_my_settings',
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
                route: 'space_api_account_settings',
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
                route: 'space_api_account_edit_variables',
            );
        } else {
            $account = $this->recall(Account::class);
            Assert::assertNotEmpty($account);

            $url = $this->getPathFromRoute(
                route: 'space_api_admin_account_edit_variables',
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
                route: 'space_api_account_edit_variables',
            );
        } else {
            $account = $this->recall(Account::class);
            Assert::assertNotEmpty($account);

            $url = $this->getPathFromRoute(
                route: 'space_api_admin_account_edit_variables',
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
                route: 'space_api_job_new',
                parameters: [
                    'projectId' => $project->getId(),
                ]
            ),
            bodyFields: $bodyFields,
            format: $format,
        );
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
        $projectsVars = $this->getRepository(PersistedVariable::class)->findBy(['project' => $project]);

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

        if (null !== $name) {
            $found = false;
            foreach ($projectsVars as $var) {
                if ($name === $var->getName()) {
                    $found = true;
                    Assert::assertEquals(
                        $value,
                        $var->getValue()
                    );
                }
            }

            Assert::assertTrue($found);
        }
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

    /**
     * @Given a project with a paas file using extends
     */
    public function aProjectWithAPaasFileUsingExtends(): void
    {
        $this->paasFile = __DIR__ . '/Project/WithExtends/paas.yaml';
    }

    /**
     * @Given simulate a too long image building
     */
    public function simulateATooLongImageBuilding(): void
    {
        $this->slowBuilder = true;
    }

    /**
     * @When it runs a job
     */
    public function itRunsAJob(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_job_new',
            parameters: [
                'projectId' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->formName = 'new_job';
    }

    /**
     * @Then it obtains a deployment page
     */
    public function itObtainsADeploymentPage(): void
    {
        $this->checkIfUserHasBeenRedirected();
        Assert::assertStringStartsWith(
            '/job/pending/',
            $this->currentUrl,
        );
    }

    /**
     * @Then it is forwared to job page
     */
    public function itIsForwaredToJobPage(): void
    {
        $jobs = $this->listObjects(JobOrigin::class);
        Assert::assertNotEmpty($jobs);

        /** @var JobOrigin $job */
        $job = current($jobs);
        Assert::assertInstanceOf(JobOrigin::class, $job);

        $project = $this->recall(Project::class);
        Assert::assertEquals(
            $project,
            $job->getProject(),
        );

        $url = $this->getPathFromRoute(
            route: 'space_job_get',
            parameters: [
                'id' => $job->getId(),
            ],
        );

        $this->executeRequest('GET', $url);
    }

    /**
     * @Then it has an error about a timeout
     */
    public function itHasAnErrorAboutATimeout(): void
    {
        $jobs = $this->listObjects(JobOrigin::class);
        Assert::assertNotEmpty($jobs);

        /** @var JobOrigin $job */
        $job = current($jobs);
        Assert::assertInstanceOf(JobOrigin::class, $job);

        Assert::assertTrue($job->getHistory()->isFinal());
        Assert::assertEquals(
            ['Error, time limit exceeded'],
            $job->getHistory()->getExtra()['result'] ?? []
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

        Assert::assertTrue($job->getHistory()->isFinal());
        Assert::assertEquals(
            'Teknoo\East\Paas\Contracts\Recipe\Step\Job\DispatchResultInterface',
            $job->getHistory()->getMessage(),
        );
    }


    /**
     * @Then some Kubernetes manifests have been created and executed
     */
    public function someKubernetesManifestsHaveBeenCreatedAndExecuted(): void
    {
        $jobs = $this->listObjects(JobOrigin::class);
        Assert::assertNotEmpty($jobs);

        /** @var JobOrigin $job */
        $job = current($jobs);
        Assert::assertInstanceOf(JobOrigin::class, $job);

        $json = stripslashes(json_encode($this->manifests, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        $id = $job->getId();
        if (strlen($id) < 9) {
            return;
        }

        $jobId = substr(string: $id, offset: 0, length: 4) . '-' . substr(string: $id, offset: -4);

        $expected = (new ManifestGenerator())->fullDeployment(
            projectPrefix: $this->projectPrefix,
            jobId: $jobId,
            hncSuffix: $this->hncSuffix,
            useHnc: $this->useHnc,
        );

        Assert::assertEquals(
            $expected,
            $json,
        );
    }

    /**
     * @Given a cluster supporting hierarchical namespace
     */
    public function aClusterSupportingHierarchicalNamespace(): void
    {
        $this->useHnc = true;
    }

    /**
     * @Given a subscription restriction
     */
    public function aSubscriptionRestriction(): void
    {
        $type = $this->sfContainer->get(SpaceSubscriptionType::class);
        $type->setEnableCodeRestriction(true);
    }

    /**
     * @Given without a subscription restriction
     */
    public function withoutAaSubscriptionRestriction(): void
    {
        $type = $this->sfContainer->get(SpaceSubscriptionType::class);
        $type->setEnableCodeRestriction(false);
    }

    /**
     * @When an user go to subscription page
     */
    public function anUserGoToSubscriptionPage(): void
    {
        $url = $this->getPathFromRoute(
            route: 'space_subscription',
        );

        $this->executeRequest('GET', $url);
        $this->formName = 'space_subscription';
    }

    /**
     * @Then the user obtains an error
     */
    public function theUserObtainsAnError(): void
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();
        $node = $crawler->filter('.space-form-error');

        Assert::assertNotEmpty(
            trim((string) $node?->getNode(0)?->textContent),
        );
    }

    /**
     * @Then a password mismatch error
     */
    public function aPasswordMismatchError(): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-error');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertEquals(
            $this->translator->trans('The password fields must match.'),
            $nodeValue,
        );
    }

    /**
     * @Then an invalid code error
     */
    public function anInvalidCodeError(): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-error');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertEquals(
            $this->translator->trans('teknoo.space.error.code_not_accepted'),
            $nodeValue,
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

    /**
     * @Then a Kubernetes namespace :namespace is created and populated
     */
    public function aKubernetesNamespaceIsCreatedAndPopulated(string $namespace): void
    {
        $expected = trim((new ManifestGenerator())->namespaceCreation($namespace));
        $json = trim(json_encode($this->manifests, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        Assert::assertEquals(
            $expected,
            $json,
        );
    }

    /**
     * @Then the user is redirected to the dashboard page
     */
    public function theUserIsRedirectedToTheDashboardPage(): void
    {
        $this->checkIfUserHasBeenRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('space_dashboard'),
            $this->currentUrl,
        );
    }

    /**
     * @Then the account name is now :accountName
     */
    public function theAccountNameIsNow(string $accountName): void
    {
        $this->checkIfResponseIsAFinal();

        if ($this->isApiCall) {
            $account = $this->recall(Account::class);
            Assert::assertEquals(
                $accountName,
                (string) $account,
            );

            return;
        }

        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);
        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );

        $node = $crawler->filter('small#space-account-name');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertEquals(
            $accountName,
            $nodeValue,
        );
    }

    /**
     * @When It goes to user settings
     */
    public function itGoesToUserSettings(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_my_settings',
        );

        $this->formName = 'space_user';
    }

    /**
     * @Then the user's name is now :fullName
     * @Then its name is now :fullName
     */
    public function itsNameIsNow(string $fullName): void
    {
        $this->checkIfResponseIsAFinal();

        if ($this->isApiCall) {
            $user = $this->recall(User::class);
            Assert::assertEquals(
                $fullName,
                $user?->getFirstName() . ' ' . $user?->getLastName(),
            );

            return;
        }

        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);
        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );

        $node = $crawler->filter('span#space-user-name');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertEquals(
            $fullName,
            $nodeValue,
        );
    }

    /**
     * @When the user logs out
     */
    public function theUserLogsOut(): void
    {
        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'account_logout',
            ),
            clearCookies: true,
        );

        $this->checkIfResponseIsAFinal();
    }

    /**
     * @Then a session is opened
     * @Then a new session is open
     */
    public function aNewSessionIsOpen(): void
    {
        Assert::assertNotEmpty($token = $this->getTokenStorageService->tokenStorage?->getToken());
        Assert::assertInstanceOf(PasswordAuthenticatedUser::class, $token?->getUser());
    }

    /**
     * @Then a recovery session is opened
     */
    public function aNewRecoverySessionIsOpen(): void
    {
        Assert::assertNotEmpty($token = $this->getTokenStorageService->tokenStorage?->getToken());
        Assert::assertInstanceOf(UserWithRecoveryAccess::class, $token?->getUser());
    }

    /**
     * @Then a session must be not opened
     */
    public function aSessionMustBeNotOpened(): void
    {
        Assert::assertEmpty($this->getTokenStorageService->tokenStorage?->getToken());
    }

    /**
     * @Then Space executes the job
     */
    public function spaceExecutesTheJob(): void
    {
        $newJobTransport = $this->testTransport->get('new_job');
        $executeJobTransport = $this->testTransport->get('execute_job');
        $historySentTransport = $this->testTransport->get('history_sent');
        $jobDoneTransport = $this->testTransport->get('job_done');

        $newJobTransport->process();
        $executeJobTransport->process();
        $historySentTransport->process();
        $jobDoneTransport->process();
    }

    /**
     * @Given extensions libraries provided by administrators
     */
    public function extensionsLibrariesProvidedByAdministrators(): void
    {
        $lib = $this->sfContainer->get('teknoo.east.paas.compilation.ingresses_extends.library');
        $lib['demo-extends'] = [
            'service' => [
                'name' => 'demo',
                'port' => 8080,
            ],
        ];

        $lib = $this->sfContainer->get('teknoo.east.paas.compilation.pods_extends.library');
        $lib['php-pods-extends'] = [
            'replicas' => 2,
            'requires' => [
                'x86_64',
                'avx',
            ],
            'upgrade' => [
                'max-upgrading-pods' => 2,
                'max-unavailable-pods' => 1,
            ],
            'containers' => [
                'php-run' => [
                    'image' => 'registry.teknoo.software/php-run',
                    'version' => '7.4',
                    'listen' => [8080],
                    'volumes' => [
                        'extra' => [
                            'from' => 'extra',
                            'mount-path' => '/opt/extra',
                        ],
                        'data' => [
                            'mount-path' => '/opt/data',
                            'persistent' => true,
                            'storage-size' => '3Gi',
                        ],
                        'data-replicated' => [
                            'mount-path' => '/opt/data-replicated',
                            'persistent' => true,
                            'storage-provider' => 'replicated-provider',
                            'storage-size' => '3Gi',
                        ],
                        'map' => [
                            'mount-path' => '/map',
                            'from-map' => 'map2',
                        ],
                    ],
                    'variables' => [
                        'SERVER_SCRIPT' => '${SERVER_SCRIPT}',
                    ],
                    'healthcheck' => [
                        'initial-delay-seconds' => 10,
                        'period-seconds' => 30,
                        'probe' => [
                            'command' => ['ps', 'aux', 'php'],
                        ],
                    ],
                ],
            ],
        ];

        $lib = $this->sfContainer->get('teknoo.east.paas.compilation.containers_extends.library');
        $lib['bash-extends'] = [
            'image' => 'registry.hub.docker.com/bash',
            'version' => 'alpine',
        ];

        $lib = $this->sfContainer->get('teknoo.east.paas.compilation.services_extends.library');
        $lib['php-pods-extends'] = [
            'pod' => 'php-pods',
            'internal' => false,
            'protocol' => Transport::Tcp->value,
            'ports' => [
                [
                    'listen' => 9876,
                    'target' => 8080,
                ],
            ],
        ];
    }

    /**
     * @Given a job workspace agent
     */
    public function aJobWorkspaceAgent(): void
    {
        $workspace = new class ($this->paasFile) implements JobWorkspaceInterface {
            use ImmutableTrait;

            public function __construct(
                private ?string &$paasFile,
            ) {
            }

            public function setJob(JobUnitInterface $job): JobWorkspaceInterface
            {
                return $this;
            }

            public function clean(): JobWorkspaceInterface
            {
                return $this;
            }

            public function writeFile(FileInterface $file, callable $return = null): JobWorkspaceInterface
            {
                return $this;
            }

            public function prepareRepository(CloningAgentInterface $cloningAgent): JobWorkspaceInterface
            {
                return $this;
            }

            public function loadDeploymentIntoConductor(
                ConductorInterface $conductor,
                PromiseInterface $promise
            ): JobWorkspaceInterface {
                if (empty($this->paasFile) || !file_exists($this->paasFile)) {
                    throw new RuntimeException('Error, the paas file was not defined for this test');
                }

                $conf = file_get_contents($this->paasFile);

                $conductor->prepare(
                    $conf,
                    $promise
                );

                return $this;
            }

            public function hasDirectory(string $path, PromiseInterface $promise): JobWorkspaceInterface
            {
                $promise->success();

                return $this;
            }

            public function runInRepositoryPath(callable $callback): JobWorkspaceInterface
            {
                $callback('/foo');

                return $this;
            }
        };

        $this->sfContainer->set(
            JobWorkspaceInterface::class,
            $workspace
        );
    }

    /**
     * @Given a git cloning agent
     */
    public function aGitCloningAgent(): void
    {
        $cloningAgent = new class () implements CloningAgentInterface {
            use ImmutableTrait;

            private ?JobWorkspaceInterface $workspace = null;

            public function configure(
                SourceRepositoryInterface $repository,
                JobWorkspaceInterface $workspace
            ): CloningAgentInterface {
                $that = clone $this;

                $that->workspace = $workspace;

                return $that;
            }

            public function run(): CloningAgentInterface
            {
                $this->workspace->prepareRepository($this);

                return $this;
            }

            public function cloningIntoPath(string $jobRootPath, string $repositoryFolder): CloningAgentInterface
            {
                return $this;
            }
        };

        $this->sfContainer->set(
            CloningAgentInterface::class,
            $cloningAgent
        );
    }

    /**
     * @Given a composer hook as hook builder
     */
    public function aComposerHookAsHookBuilder(): void
    {
        $hook = new HookMock();

        $hooks = ['composer' => clone $hook, 'hook-id' => clone $hook];
        $collection = new class ($hooks) implements HooksCollectionInterface {
            private iterable $hooks;

            public function __construct(iterable $hooks)
            {
                $this->hooks = $hooks;
            }

            public function getIterator(): Traversable
            {
                yield from $this->hooks;
            }
        };

        $this->sfContainer->set(
            HooksCollectionInterface::class,
            $collection
        );
    }

    /**
     * @Given an OCI builder
     */
    public function anOciBuilder(): void
    {
        $generator = new Generator();
        $mock = $generator->testDouble(
            type: Process::class,
            mockObject: true,
            callOriginalConstructor: false,
            callOriginalClone: false,
            callOriginalMethods: false,
        );

        $mock->expects(new AnyInvokedCountMatcher())
            ->method('isSuccessful')
            ->willReturnCallback(
                function () {
                    if ($this->slowBuilder) {
                        $expectedTime = time() + 20;
                        while (time() < $expectedTime) {
                            $x = str_repeat('x', 100000);
                        }
                    }

                    return true;
                }
            );

        $this->sfContainer->set(
            ProcessFactoryInterface::class,
            new class ($mock) implements ProcessFactoryInterface {
                public function __construct(
                    private Process $process,
                ) {
                }

                public function __invoke(string $cwd): Process
                {
                    return $this->process;
                }
            }
        );

        $this->sfContainer->get(DiContainer::class)->set(
            'teknoo.east.paas.img_builder.build.platforms',
            'space',
        );
    }

    /**
     * @Given without any hooks path defined
     */
    public function withoutAnyHooksPathDefined(): void
    {
        $diCi = $this->sfContainer->get(DiContainer::class);
        $diCi->set(
            'teknoo.east.paas.composer.path',
            null
        );
        $diCi->set(
            'teknoo.east.paas.symfony_console.path',
            null
        );
        $diCi->set(
            'teknoo.east.paas.npm.path',
            null
        );
        $diCi->set(
            'teknoo.east.paas.pip.path',
            null
        );
        $diCi->set(
            'teknoo.east.paas.make.path',
            null
        );
    }

    /**
     * @Given a composer path set in the DI
     */
    public function aComposerPathSetInTheDi(): void
    {
        $diCi = $this->sfContainer->get(DiContainer::class);
        $diCi->set(
            'teknoo.east.paas.composer.path',
            new ArrayObject(['composer'])
        );
    }

    /**
     * @When the hook library is generated
     */
    public function theHookLibraryIsGenerated(): void
    {
        $this->hookCollection = $this->sfContainer->get(HooksCollectionInterface::class);
    }

    /**
     * @Then it obtains non empty hooks library with :name key.
     */
    public function itObtainsNonEmptyHooksLibraryWithKey(string $name): void
    {
        $hooks = iterator_to_array($this->hookCollection);
        Assert::assertArrayHasKey($name, $hooks);
    }

    /**
     * @Then it obtains empty hooks library
     */
    public function itObtainsEmptyHooksLibrary(): void
    {
        $hooks = iterator_to_array($this->hookCollection);
        Assert::assertEmpty($hooks);
    }

    /**
     * @When an user go to recovery request page
     */
    public function anUserGoToRecoveryRequestPage(): void
    {
        $url = $this->getPathFromRoute(
            route: '_teknoo_common_user_recovery',
        );

        $this->executeRequest('GET', $url);
        $this->formName = 'email_form';
    }

    /**
     * @Then The client must go to recovery request sent page
     */
    public function theClientMustGoToRecoveryRequestSentPage(): void
    {
        Assert::assertStringStartsWith(
            $this->getPathFromRoute('_teknoo_common_user_recovery'),
            $this->currentUrl,
        );
    }

    /**
     * @Then it is redirected to the recovery password page
     */
    public function itIsRedirectedToTheRecoveryPasswordPage(): void
    {
        $this->checkIfUserHasBeenRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('space_update_password'),
            $this->currentUrl,
        );
    }

    private function getMailerEvents(): MessageEvents
    {
        Assert::assertNotNull(
            $this->messageLoggerListener,
            'Symfony Mailer is not configured'
        );

        return $this->messageLoggerListener->getEvents();
    }

    /**
     * @Then no notification must be sent
     */
    public function noNotificationMustBeSent(): void
    {
        Assert::assertThat(
            $this->getMailerEvents(),
            new EmailCount(0),
        );
    }

    /**
     * @Then a notification must be sent
     */
    public function aNotificationMustBeSent(): void
    {
        Assert::assertThat(
            $this->getMailerEvents(),
            new EmailCount(1),
        );
    }

    /**
     * @When the user click on the link in the notification
     */
    public function theUserClickOnTheLinkInTheNotification(): void
    {
        $message = $this->getMailerEvents()->getMessages(null)[0];
        $context = $message->getContext();
        $actionUrl = $context['action_url'] ?? '';

        Assert::assertNotEmpty($actionUrl);

        $this->executeRequest(
            method: 'GET',
            url: $actionUrl,
        );
    }
}
