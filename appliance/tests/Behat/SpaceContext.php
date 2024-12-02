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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat;

use Behat\Behat\Context\Context;
use DateTime;
use DateTimeInterface;
use DI\Container as DiContainer;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use phpseclib3\Crypt\RSA;
use PHPUnit\Framework\MockObject\Generator\Generator;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use Psr\Cache\CacheItemPoolInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Liveness\TimeoutServiceInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Contracts\Hook\HooksCollectionInterface;
use Teknoo\East\Paas\Infrastructures\Image\Contracts\ProcessFactoryInterface;
use Teknoo\East\Paas\Infrastructures\PhpSecLib\Configuration\Algorithm;
use Teknoo\East\Paas\Job\History\SerialGenerator;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Account\SpaceSubscriptionType;
use Teknoo\Space\Tests\Behat\Traits\ApiTrait;
use Teknoo\Space\Tests\Behat\Traits\BrowserActionTrait;
use Teknoo\Space\Tests\Behat\Traits\BrowserCrawlingTrait;
use Teknoo\Space\Tests\Behat\Traits\BuilderTrait;
use Teknoo\Space\Tests\Behat\Traits\HttpTrait;
use Teknoo\Space\Tests\Behat\Traits\JwtTrait;
use Teknoo\Space\Tests\Behat\Traits\KubernetesTrait;
use Teknoo\Space\Tests\Behat\Traits\NotificationTrait;
use Teknoo\Space\Tests\Behat\Traits\PersistenceOperationTrait;
use Teknoo\Space\Tests\Behat\Traits\PersistenceStepsTrait;
use Teknoo\Space\Tests\Behat\Traits\WorkerTrait;
use Zenstruck\Messenger\Test\Transport\TestTransport;
use Zenstruck\Messenger\Test\Transport\TestTransportRegistry;

use function file_exists;
use function gc_collect_cycles;
use function is_readable;
use function pcntl_alarm;
use function str_replace;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
class SpaceContext implements Context
{
    use ApiTrait;
    use BrowserActionTrait;
    use BrowserCrawlingTrait;
    use BuilderTrait;
    use HttpTrait;
    use JwtTrait;
    use KubernetesTrait;
    use NotificationTrait;
    use PersistenceOperationTrait;
    use PersistenceStepsTrait;
    use WorkerTrait;

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

    /**
     * @var array<string, ObjectInterface>
     */
    private array $removedObjects = [];

    private ?string $currentUrl = null;

    /**
     * @var array<string, mixed>
     */
    private array $workMemory = [];

    private array $quotasAllowed = [];

    private string $quotasMode = '';

    private string $defaultsMode = '';

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

    private array $deletedManifests = [];

    private ?string $jwtToken = null;

    private ?int $itemsPerPages = null;

    private ?HooksCollectionInterface $hookCollection = null;

    private ?string $apiPendingJobUrl = null;

    private bool $clearJobMemory = false;

    private string $messagePrivateKey = __DIR__ . '/../var/keys/messages/private.pem';

    private string $messagePublicKey = __DIR__ . '/../var/keys/messages/public.pem';

    private string $varPrivateKey = __DIR__ . '/../var/keys/variables/private.pem';

    private string $varPublicKey = __DIR__ . '/../var/keys/variables/public.pem';

    private readonly string $defaultClusterName;

    private readonly string $defaultClusterAddress;

    private static ?self $currentInstance = null;

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
        string $defaultClusterName,
        private readonly string $defaultClusterType,
        string $defaultClusterAddress,
    ) {
        $this->defaultClusterName = str_replace('Legacy ', '', $defaultClusterName);
        $this->defaultClusterAddress = str_replace('legacy-', '', $defaultClusterAddress);

        self::$currentInstance = $this;
    }

    public function current(): self
    {
        return $this;
    }

    private function clear(): void
    {
        $this->request = null;
        $this->response = null;
        $this->sfContainer = null;
        $this->objectManager = null;
        $this->repositories = [];
        $this->objects = [];
        $this->removedObjects = [];
        $this->cookies = [];
        $this->clientIp = '127.0.0.1';
        $this->currentUrl = null;
        $this->workMemory = [];
        $this->quotasAllowed = [];
        $this->quotasMode = '';
        $this->defaultsMode = '';
        $this->hasBeenRedirected = false;
        $this->formName = null;
        $this->originalProjectName = null;
        $this->paasFile = null;
        $this->slowBuilder = false;
        $this->useHnc = false;
        $this->hncSuffix = '';
        $this->manifests = [];
        $this->deletedManifests = [];
        $this->projectPrefix = null;
        Query::$testsContext = $this;
        Query::$testsObjecttManager = null;
        $this->hookCollection = null;
        $this->jwtToken = null;
        $this->itemsPerPages = null;
        $this->getTokenStorageService->tokenStorage?->setToken(null);
        $this->apiPendingJobUrl = null;
        $this->clearJobMemory = false;
        $this->timeoutService->disable();

        $envVarsNames = [
            'TEKNOO_PAAS_SECURITY_ALGORITHM',
            'TEKNOO_PAAS_SECURITY_PRIVATE_KEY',
            'TEKNOO_PAAS_SECURITY_PRIVATE_KEY_PASSPHRASE',
            'TEKNOO_PAAS_SECURITY_PUBLIC_KEY',
            'SPACE_PERSISTED_VAR_SECURITY_ALGORITHM',
            'SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY',
            'SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY_PASSPHRASE',
            'SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY',
            'SPACE_PERSISTED_VAR_AGENT_MODE',
            'SPACE_HOOKS_COLLECTION_JSON',
            'SPACE_HOOKS_COLLECTION_FILE',
        ];

        foreach ($envVarsNames as $name) {
            if (!empty($_ENV[$name])) {
                unset($_ENV[$name]);
            }
        }

        if (!empty($_ENV['SPACE_SUBSCRIPTION_DEFAULT_PLAN'])) {
            $this->clearRouterCache();
            unset($_ENV['SPACE_SUBSCRIPTION_DEFAULT_PLAN']);
        }

        gc_collect_cycles();
        pcntl_alarm(0);

        TestTransport::disableResetOnKernelShutdown();
    }

    /**
     * @BeforeScenario
     */
    public function prepareScenario(): void
    {
        $this->clear();
    }

    /**
     * @AfterScenario
     */
    public function cleanAfterScenario(): void
    {
        $this->clear();
    }

    public function clearRouterCache(): void
    {
        //Hack to reset router cache to update attribute from env value
        // (not needed on dev or prod env, only in behat)
        $rc = new \ReflectionClass(Router::class);
        $rc->setStaticPropertyValue('cache', []);
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

        if (!empty($_ENV['SPACE_SUBSCRIPTION_DEFAULT_PLAN'])) {
            $this->clearRouterCache();
            unset($_ENV['SPACE_SUBSCRIPTION_DEFAULT_PLAN']);
        }
    }

    public function setDateTime(DateTimeInterface $dateTime): void
    {
        $this->sfContainer?->get(DatesService::class)
            ->setCurrentDate($dateTime);
    }

    public function setSerialGenerator(callable $generator): void
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
        if (!file_exists($this->messagePrivateKey) || !is_readable($this->messagePrivateKey)) {
            $pk = RSA::createKey(2048);

            file_put_contents($this->messagePrivateKey, $pk->toString('PKCS8'));
            file_put_contents($this->messagePublicKey, $pk->getPublicKey()->toString('PKCS8'));
        }

        $_ENV['TEKNOO_PAAS_SECURITY_ALGORITHM'] = Algorithm::RSA->value;
        $_ENV['TEKNOO_PAAS_SECURITY_PRIVATE_KEY'] = $this->messagePrivateKey;
        $_ENV['TEKNOO_PAAS_SECURITY_PUBLIC_KEY'] = $this->messagePublicKey;
    }

    /**
     * @Given encryption of persisted variables in the database
     */
    public function encryptionOfPersistedVariablesInTheDatabase(): void
    {
        if (!file_exists($this->varPrivateKey) || !is_readable($this->varPrivateKey)) {
            $pk = RSA::createKey(2048);

            file_put_contents($this->varPrivateKey, $pk->toString('PKCS8'));
            file_put_contents($this->varPublicKey, $pk->getPublicKey()->toString('PKCS8'));
        }

        $_ENV['SPACE_PERSISTED_VAR_SECURITY_ALGORITHM'] = Algorithm::RSA->value;
        $_ENV['SPACE_PERSISTED_VAR_SECURITY_PRIVATE_KEY'] = $this->varPrivateKey;
        $_ENV['SPACE_PERSISTED_VAR_SECURITY_PUBLIC_KEY'] = $this->varPublicKey;
        $_ENV['SPACE_PERSISTED_VAR_AGENT_MODE'] = false;
    }

    private function getEncryptAlgoForVar(): ?string
    {
        return $_ENV['SPACE_PERSISTED_VAR_SECURITY_ALGORITHM'] ?? null;
    }

    /**
     * @Given a subscription plan :id is selected
     */
    public function aSubscriptionPlanIsSelected(string $id): void
    {
        $_ENV['SPACE_SUBSCRIPTION_DEFAULT_PLAN'] = $id;
        $this->quotasMode = $id;

        $this->clearRouterCache();
    }

    /**
     * @Given the platform is booted
     */
    public function thePlatformIsBooted(): void
    {
    }

    /**
     * @Given the project has a complete paas file
     */
    public function theProjectHasACompletePaasFile(): void
    {
        $this->paasFile = __DIR__ . '/Project/Basic/paas.yaml';
        $this->quotasMode = '';
        $this->defaultsMode = '';
    }

    /**
     * @Given the project has a complete paas file with defaults
     */
    public function theProjectHasACompletePaasFileWithDefaults(): void
    {
        $this->paasFile = __DIR__ . '/Project/WithDefaults/paas.yaml';
        $this->quotasMode = '';
        $this->defaultsMode = 'generic';
    }

    /**
     * @Given the project has a complete paas file with defaults for the cluster
     */
    public function theProjectHasACompletePaasFileWithDefaultsForCluster(): void
    {
        $this->paasFile = __DIR__ . '/Project/WithDefaults/paas.with-clusters.yaml';
        $this->quotasMode = '';
        $this->defaultsMode = 'cluster';
    }

    /**
     * @Given a project with a paas file using extends
     */
    public function aProjectWithAPaasFileUsingExtends(): void
    {
        $this->paasFile = __DIR__ . '/Project/WithExtends/paas.yaml';
        $this->quotasMode = '';
    }

    /**
     * @Given the project has a complete paas file without resources
     */
    public function aProjectWithAPaasFileWithoutResource()
    {
        $this->paasFile = __DIR__ . '/Project/Basic/paas.yaml';
        $this->quotasMode = 'automatic';
    }

    /**
     * @Given the project has a complete paas file with partial resources
     */
    public function aProjectWithAPaasFileWithPartialResources()
    {
        $this->paasFile = __DIR__ . '/Project/Basic/paas.with-partial-resources.yaml';
        $this->quotasMode = 'partial';
    }

    /**
     * @Given the project has a complete paas file with resources
     */
    public function aProjectWithAPaasFileWithResources()
    {
        $this->paasFile = __DIR__ . '/Project/Basic/paas.with-resources.yaml';
        $this->quotasMode = 'full';
    }

    /**
     * @Given the project has a complete paas file with limited quota
     */
    public function aProjectWithAPaasFileWithLimitedQuota()
    {
        $this->paasFile = __DIR__ . '/Project/Basic/paas.with-quotas-exceeded.yaml';
        $this->quotasMode = 'limited';
    }

    /**
     * @Then with the subscription plan :id
     */
    public function withTheSubscriptionPlan(string $id): void
    {
        $this->quotasMode = $id;
    }

    /**
     * @Given simulate a too long image building
     */
    public function simulateATooLongImageBuilding(): void
    {
        $this->slowBuilder = true;
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
     * @Given an OCI builder
     */
    public function anOciBuilder(): void
    {
        $generator = new Generator();
        $mock = $generator->testDouble(
            type: Process::class,
            mockObject: true,
            markAsMockObject: false,
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

        /** @noinspection PhpParamsInspection */
        $this->sfContainer->get(DiContainer::class)->set(
            'teknoo.east.paas.img_builder.build.platforms',
            'space',
        );
    }
}
