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

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use DateTime;
use DateTimeInterface;
use DI\Container as DiContainer;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use OTPHP\TOTP;
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
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Foundation\Liveness\TimeoutServiceInterface;
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
use function count;
use function current;
use function end;
use function explode;
use function get_parent_class;
use function hash;
use function in_array;
use function is_array;
use function is_iterable;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function key;
use function mb_strtolower;
use function method_exists;
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
     * @var array<ObjectInterface>
     */
    private array $objects = [];

    private ?string $currentUrl = null;

    private array $workMemory = [];

    private bool $hasBeenRedirected = false;

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
        $this->setSerialGenerator(fn() => 0);

        HttpClientDiscovery::registerInstantiator(SymfonyHttplug::class, Symfony::class);
    }

    /**
     * @Given A kubernetes client
     */
    public function aKubernetesClient(): void
    {
        MockClientInstantiator::$testsContext = $this;

        HttpClientDiscovery::registerInstantiator(SymfonyHttplug::class, MockClientInstantiator::class);
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
                default=> $expected === $value,
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
    ): iterable
    {
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

    private function getObjectUniqueId(object $object)
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

    public function persistObject(object $object): void {
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

    public function removeObject(object $object): void {
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

            $response = $this->executeRequest('get', $newUrl, []);
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

        $this->executeRequest('get', $url);
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

    private function checkIfResponseIsAFinal(): void
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
            billingName: $accountName . ' SAS',
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
     * @Given an user, called :lastName :firstName with the :email with the password :password
     */
    public function anUserCalledWithTheWithThePassword(
        string $lastName,
        string $firstName,
        string $email,
        string $password,
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
        $user->setRoles(['ROLE_USER']);
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
    public function itGoesToAccountSettings()
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
    public function openTheAccountVariablesPage()
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
    public function itObtainsAEmptyAccountsVariablesForm()
    {
        $formValues = $this->createForm('account_vars')->getPhpValues();
        Assert::assertFalse(isset($formValues['account_vars']['sets']));
    }

    /**
     * @When it submits the form:
     */
    public function itSubmitsTheForm(TableNode $formFields)
    {
        Assert::assertNotEmpty($this->formName);

        $form = $this->createForm($this->formName);
        $final = [];
        $formValue = $form->getPhpValues();

        foreach ($formFields as $field) {
            if (
                '<auto>' === $field['value']
            ) {
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
    public function theAccountMustHaveTheseePersistedVariables(TableNode $expectedVariables)
    {
        $this->checkIfResponseIsAFinal();

        $crawler = $this->createCrawler();
        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);
        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );

        $vars = $this->listObjects(AccountPersistedVariable::class);
        Assert::assertCount(count($expectedVariables->getLines()) - 1, $vars);
        $account = $this->recall(Account::class);

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
    public function theUserObtainsTheForm(TableNode $formFields)
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
    public function theAccountHaveThesePersistedVariables(TableNode $variables)
    {
        $account = $this->recall(Account::class);
        foreach ($variables as $var) {
            $apv = new AccountPersistedVariable(
                account: $account,
                id: $var['id'] . $this->generateId(),
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
            method: 'get',
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
    public function itHasAWelcomeMessageWithInTheDashboardHeader(string $fullName)
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
    public function itMustRedirectedToTheTotpCodePage()
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
    public function itMustHaveATotpError()
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('p#2fa-error');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertNotEmpty($nodeValue,);
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
    public function theUserEnterAValidTotpCode()
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
    public function itIsRedirectedToTheLoginPageWithAnError()
    {
        $this->checkIfUserHasBeenRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('account_login'),
            $this->currentUrl,
        );

        $crawler = $this->createCrawler();
        $node = $crawler->filter('#login-error');
        $nodeValue = trim((string) $node?->getNode(0)?->textContent);

        Assert::assertNotEmpty($nodeValue,);
    }

    /**
     * @When the user enter a wrong TOTP code
     */
    public function theUserEnterAWrongTotpCode()
    {
        $this->submitTotpCode(
            code: 'fooBar'
        );
    }

    /**
     * @Given a standard website project :projectName
     */
    public function aStandardWebsiteProject(string $projectName)
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
            new ImageRegistry(
                $credential->getRegistryUrl(),
                new XRegistryAuth(
                    $credential->getRegistryAccountName(),
                    $credential->getRegistryPassword(),
                    '',
                    '',
                    $credential->getRegistryUrl()
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
     * @Given a standard website project :projectName and a prefix :prefix
     */
    public function aStandardWebsiteProjectAndAPrefix(string $projectName, string $prefix)
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
            $imageRegistry = new ImageRegistry(
                $credential->getRegistryUrl(),
                new XRegistryAuth(
                    $credential->getRegistryAccountName(),
                    $credential->getRegistryPassword(),
                    '',
                    '',
                    $credential->getRegistryUrl()
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
     * @When It goes to projects list page
     */
    public function itGoesToProjectsListPage()
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_list',
        );
    }

    /**
     * @Then the user obtains a project list:
     */
    public function theUserObtainsAProjectList(TableNode $projects)
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
    public function itGoesToNewProjectPage()
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
    public function itObtainsAEmptyProjectsForm()
    {
        $formValues = $this->createForm('space_project')->getPhpValues();
        Assert::assertEmpty($formValues['space_project']['project']['name']);
    }

    /**
     * @Then the project must be persisted
     */
    public function theProjectMustBePersisted()
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
    public function itOpensTheProjectPageOf(string $projectName)
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
    public function theProjectMustBeUpdated()
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
    public function itGoesToProjectPageOfOf(string $projectName, string $accountName)
    {
        $this->checkIfResponseIsAFinal();

        $url = $this->getPathFromRoute(
            route: 'space_project_edit',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->executeRequest('get', $url);
    }

    /**
     * @Then the user must have a :code error
     */
    public function theUserMustHaveAError(int $code)
    {
        Assert::assertEquals($code, $this->response?->getStatusCode());
    }

    /**
     * @Then the project is not deleted
     */
    public function theProjectIsNotDeleted()
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

        $this->executeRequest('get', $url);
    }

    /**
     * @When it goes to project page of :projectName
     */
    public function itGoesToProjectPageOf(string $projectName)
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
    public function openTheProjectVariablesPage()
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
    public function itObtainsAEmptyProjectsVariablesForm()
    {
        $formValues = $this->createForm('project_vars')->getPhpValues();
        Assert::assertFalse(isset($formValues['project_vars']['sets']));
    }

    /**
     * @Then the project must have these persisted variables
     */
    public function theProjectMustHaveTheseePersistedVariables(TableNode $expectedVariables)
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
    public function theProjectHaveThesePersistedVariables(TableNode $variables)
    {
        $project = $this->recall(Project::class);
        foreach ($variables as $var) {
            $apv = new PersistedVariable(
                project: $project,
                id: $var['id'] . $this->generateId(),
                name: $var['name'],
                value: $var['value'],
                environmentName: $var['environment'],
                secret: !empty($var['secret']),
            );

            $this->persistObject($apv);
        }
    }

    /**
     * @Given a project with a complete paas file
     */
    public function aProjectWithACompletePaasFile()
    {
        $this->paasFile = __DIR__ . '/Project/Default/paas.yaml';
    }

    /**
     * @Given :number jobs for the project
     */
    public function jobsForTheProject(int $number)
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
    public function getAJwtTokenForTheUser()
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
    public function theApiIsCalledToListOfJobs()
    {
        $project = $this->recall(Project::class);

        $this->executeRequest(
            method: 'get',
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
     * @When the API is called to get the last job
     */
    public function theApiIsCalledToGetTheLastJob()
    {
        $project = $this->recall(Project::class);
        $job = $this->recall(Job::class);

        $this->executeRequest(
            method: 'get',
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
     * @When the API is called to get the last generated job
     */
    public function theApiIsCalledToGetTheLastGeneratedJob()
    {
        $project = $this->recall(Project::class);
        $jobs = $this->listObjects(JobOrigin::class);
        $job = end($jobs);

        $this->register($job);

        $this->executeRequest(
            method: 'get',
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
     * @When the API is called to delete the last job
     * @When the API is called to delete the last job with :method method
     */
    public function theApiIsCalledToDeleteTheLastJob(string $method = 'GET')
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
    public function theApiIsCalledToCreateANewJobWithAJsonBody(TableNode $bodyFields)
    {
        $this->theApiIsCalledToCreateANewJob($bodyFields, 'json');
    }

    /**
     * @When the API is called to create a new job:
     */
    public function theApiIsCalledToCreateANewJob(TableNode $bodyFields, string $format = 'default')
    {
        $project = $this->recall(Project::class);

        $final = [];
        foreach ($bodyFields as $field) {
            $this->setRequestParameters($final, $field['field'], $field['value']);
        }

        $final = match ($format) {
            'json' => json_encode($final),
            default => $final,
        };

        $this->executeRequest(
            method: 'post',
            url: $this->getPathFromRoute(
                route: 'space_api_job_new',
                parameters: [
                    'projectId' => $project->getId(),
                ],
            ),
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
     * @When the API is called to pending job status api
     */
    public function theApiIsCalledToPendingJobStatusApi()
    {
        Assert::assertNotEmpty($this->apiPendingJobUrl);

        $this->executeRequest(
            method: 'get',
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
    public function theJobIsDeleted()
    {
        Assert::assertEmpty(
            $this->listObjects(Job::class),
        );
    }

    /**
     * @When the job is not deleted
     */
    public function theJobIsNotDeleted()
    {
        Assert::assertNotEmpty(
            $this->listObjects(Job::class),
        );
    }

    /**
     * @Then get a JSON reponse
     */
    public function getAJsonReponse()
    {
        Assert::assertEquals(
            'application/json; charset=utf-8',
            $this->response->headers->get('Content-Type'),
        );
    }

    /**
     * @When an :arg1 error
     */
    public function anError(int $code)
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
    public function aPendingJobId()
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
    public function aPendingJobStatusWithoutAJobId()
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
    public function isASerializedCollectionOfItemsOnPages(int $count, int $page)
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
     * @When the a list of serialized jobs
     */
    public function theListSerializedJobs()
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

        Assert::assertEquals(
            $normalized,
            $unserialized['data'],
        );
    }

    /**
     * @When the serialized job
     */
    public function theSerializedJob()
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

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );
    }

    /**
     * @When the serialized deleted job
     */
    public function theSerializedDeletedJob()
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
                'groups' => ['api'],
            ],
        );

        Assert::assertEquals(
            $normalized,
            $unserialized,
        );
    }

    /**
     * @Given a project with a paas file using extends
     */
    public function aProjectWithAPaasFileUsingExtends()
    {
        $this->paasFile = __DIR__ . '/Project/WithExtends/paas.yaml';
    }

    /**
     * @Given simulate a too long image building
     */
    public function simulateATooLongImageBuilding()
    {
        $this->slowBuilder = true;
    }

    /**
     * @When it runs a job
     */
    public function itRunsAJob()
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
    public function itObtainsADeploymentPage()
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
    public function itIsForwaredToJobPage()
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

        $this->executeRequest('get', $url);
    }

    /**
     * @Then it has an error about a timeout
     */
    public function itHasAnErrorAboutATimeout()
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
    public function jobMustBeSuccessfulFinished()
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
    public function someKubernetesManifestsHaveBeenCreatedAndExecuted()
    {
        $jobs = $this->listObjects(JobOrigin::class);
        Assert::assertNotEmpty($jobs);

        /** @var JobOrigin $job */
        $job = current($jobs);
        Assert::assertInstanceOf(JobOrigin::class, $job);

        $json = stripslashes(json_encode($this->manifests, JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT));

        $id = $job->getId();
        if (strlen($id) < 9) {
            return $id;
        }

        $jobId = substr(string: $id, offset: 0, length: 4) . '-' . substr(string: $id, offset: -4);

        $expected = (new ManifestGenerator)->fullDeployment(
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
    public function aClusterSupportingHierarchicalNamespace()
    {
        $this->useHnc = true;
    }

    /**
     * @Given a subscription restriction
     */
    public function aSubscriptionRestriction()
    {
        $type = $this->sfContainer->get(SpaceSubscriptionType::class);
        $type->setEnableCodeRestriction(true);
    }

    /**
     * @Given without a subscription restriction
     */
    public function withoutAaSubscriptionRestriction()
    {
        $type = $this->sfContainer->get(SpaceSubscriptionType::class);
        $type->setEnableCodeRestriction(false);
    }

    /**
     * @When an user go to subscription page
     */
    public function anUserGoToSubscriptionPage()
    {
        $url = $this->getPathFromRoute(
            route: 'space_subscription',
        );

        $this->executeRequest('get', $url);
        $this->formName = 'space_subscription';
    }

    /**
     * @Then the user obtains an error
     */
    public function theUserObtainsAnError()
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
    public function aPasswordMismatchError()
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
    public function anInvalidCodeError()
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
    public function anAccountIsCreated(string $accountName)
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
    public function anUserIsCreated(string $email)
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
    public function aKubernetesNamespaceIsCreatedAndPopulated(string $namespace)
    {
        $expected = trim((new ManifestGenerator)->namespaceCreation($namespace));
        $json = trim(json_encode($this->manifests, JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT));
        Assert::assertEquals(
            $expected,
            $json,
        );
    }

    /**
     * @Then the user is redirected to the dashboard page
     */
    public function theUserIsRedirectedToTheDashboardPage()
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
    public function theAccountNameIsNow(string $accountName)
    {
        $this->checkIfResponseIsAFinal();

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
    public function itGoesToUserSettings()
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_my_settings',
        );

        $this->formName = 'space_user';
    }

    /**
     * @Then its name is now :fullName
     */
    public function itsNameIsNow(string $fullName)
    {
        $this->checkIfResponseIsAFinal();

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
    public function theUserLogsOut()
    {
        $this->executeRequest(
            method: 'get',
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
    public function aNewSessionIsOpen()
    {
        Assert::assertNotEmpty($token = $this->getTokenStorageService->tokenStorage?->getToken());
        Assert::assertInstanceOf(PasswordAuthenticatedUser::class, $token?->getUser());
    }

    /**
     * @Then Space executes the job
     */
    public function spaceExecutesTheJob()
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
    public function extensionsLibrariesProvidedByAdministrators()
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
                    'version' => 7.4,
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
    public function aJobWorkspaceAgent()
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
    public function aGitCloningAgent()
    {
        $cloningAgent = new class implements CloningAgentInterface {
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
    public function aComposerHookAsHookBuilder()
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
    public function anOciBuilder()
    {
        $generator = new Generator();
        $mock = $generator->getMock(
            type: Process::class,
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
    public function withoutAnyHooksPathDefined()
    {
        $diCi = $this->sfContainer->get(DiContainer::class);
        $diCi->set(
            'teknoo.east.paas.composer.path',
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
    public function aComposerPathSetInTheDi()
    {
        $diCi = $this->sfContainer->get(DiContainer::class);
        $diCi->set(
            'teknoo.east.paas.composer.path',
            'composer'
        );
    }

    /**
     * @When the hook library is generated
     */
    public function theHookLibraryIsGenerated()
    {
        $this->hookCollection = $this->sfContainer->get(HooksCollectionInterface::class);
    }

    /**
     * @Then it obtains non empty hooks library with :name key.
     */
    public function itObtainsNonEmptyHooksLibraryWithKey(string $name)
    {
        $hooks = iterator_to_array($this->hookCollection);
        Assert::assertArrayHasKey($name, $hooks);
    }

    /**
     * @Then it obtains empty hooks library
     */
    public function itObtainsEmptyHooksLibrary()
    {
        $hooks = iterator_to_array($this->hookCollection);
        Assert::assertEmpty($hooks);
    }
}
