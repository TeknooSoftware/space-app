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

namespace Teknoo\Space\Tests\Behat\Traits;

use Http\Adapter\Guzzle7\Client as ClientAlias;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpClient\HttplugClient as SymfonyHttplug;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Account;
use Teknoo\East\Paas\Object\Job as JobOrigin;
use Teknoo\Kubernetes\HttpClientDiscovery;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Tests\Behat\ManifestGenerator;
use Teknoo\Space\Tests\Behat\MockClientInstantiator;

use function class_exists;
use function current;
use function explode;
use function json_encode;
use function preg_replace;
use function strtolower;
use function trim;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait KubernetesTrait
{
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

    public function setDeletedManifests(string $uri): void
    {
        $this->deletedManifests[$uri] = true;
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

        $json = json_encode($this->manifests, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $id = $job->getId();
        if (strlen($id) < 9) {
            return;
        }

        $expected = (new ManifestGenerator())->fullDeployment(
            projectPrefix: $this->projectPrefix,
            jobId: strtolower(trim((string) preg_replace('#[^A-Za-z0-9-]+#', '', (string) $job->getProject()))),
            hncSuffix: $this->hncSuffix,
            useHnc: $this->useHnc,
            quotaMode: $this->quotasMode,
            defaultsMods: $this->defaultsMode,
        );

        Assert::assertEquals(
            $expected,
            $json,
        );
    }

    /**
     * @Then no Kubernetes manifests must not be created
     */
    public function noKubernetesManifestsMustNotBeCreated(): void
    {
        Assert::assertEmpty($this->manifests);
    }

    /**
     * @Then no Kubernetes manifests must not be deleted
     */
    public function noKubernetesManifestsMustNotBeDeleted(): void
    {
        Assert::assertEmpty($this->deletedManifests);
    }

    /**
     * @Then a Kubernetes namespace dedicated to registry for :namespace is applied and populated
     */
    public function aKubernetesNamespaceDedicatedToRegistryIsAppliedAndPopulated(string $namespace): void
    {
        $expected = trim(
            (new ManifestGenerator())->registryCreation(
                $namespace,
            )
        );

        Assert::assertNotEmpty(
            $this->manifests["namespaces/space-registry-$namespace/secrets"],
        );

        foreach ($this->manifests["namespaces/space-registry-$namespace/secrets"] as &$secret) {
            if (!empty($secret['data']['htpasswd'])) {
                $secret['data']['htpasswd'] = '===';
            }
        }

        $json = trim(json_encode($this->manifests, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        Assert::assertEquals(
            $expected,
            $json,
        );
    }

    /**
     * @Then a Kubernetes manifests dedicated to quota for the last account has been applied
     */
    public function aKubernetesManifestsDedicatedToQuotaForTheLastAccountHasBeenApplied(): void
    {
        $account = $this->recall(Account::class);
        Assert::assertNotNull($account);

        $prNr = new Promise(fn ($s): string => $s);
        $prQt = new Promise(fn ($q): array => $q);
        $account->visit(
            [
                'namespace' => $prNr,
                'quotas' => $prQt,
            ]
        );

        $namespaces = [];

        /** @var AccountEnvironment $ae */
        foreach ($this->listObjects(AccountEnvironment::class) as $ae) {
            if ($ae->getAccount() === $account) {
                $namespaces[] = $ae->getNamespace();
            }
        }

        $expected = trim(
            (new ManifestGenerator())->quotaRefresh(
                $prNr->fetchResult(''),
                $namespaces,
                $prQt->fetchResult([]),
            )
        );

        $json = trim(json_encode($this->manifests, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        Assert::assertEquals(
            $expected,
            $json,
        );
    }

    /**
     * @Then a Kubernetes namespaces :namespaces must be deleted
     */
    public function aKubernetesNamespacesMustBeDeleted(string $namespaces): void
    {
        $nsList = explode(',', $namespaces);
        Assert::assertEquals(
            $nsList,
            array_keys($this->deletedManifests),
        );
    }

    /**
     * @Then a Kubernetes namespace for :namespace dedicated to :cluster is applied and populated
     */
    public function aKubernetesNamespaceDedicatedToClusterIsAppliedAndPopulated(string $namespace): void
    {
        $account = $this->recall(Account::class);
        $prNr = new Promise(fn ($s): string => $s);
        $prQt = new Promise(fn ($q): array => $q);
        $account->visit(
            [
                'namespace' => $prNr,
                'quotas' => $prQt,
            ]
        );

        $registry = $this->recall(AccountRegistry::class);

        $expected = trim(
            (new ManifestGenerator())->namespaceCreation(
                $prNr->fetchResult(),
                $namespace,
                $prQt->fetchResult([]),
                $registry,
            )
        );

        Assert::assertNotEmpty(
            $this->manifests["namespaces/space-client-$namespace/secrets"],
        );

        foreach ($this->manifests["namespaces/space-client-$namespace/secrets"] as &$secret) {
            if (!empty($secret['data']['.dockerconfigjson'])) {
                $secret['data']['.dockerconfigjson'] = '===';
            }
        }

        $json = trim(json_encode($this->manifests, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        Assert::assertEquals(
            $expected,
            $json,
        );
    }
}
