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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Doctrine\Listener;

use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\Driver\SimplifiedXmlDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\MediaMetadata;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\Space\Infrastructures\Doctrine\Listener\AuthDataDiscriminatorMapListener;

use function array_key_last;
use function array_search;
use function dirname;
use function sys_get_temp_dir;

/**
 * Test the listener with the real Doctrine ODM metadata, loaded from the XML mapping shipped by East Common : the
 * mapping is not covered by the other tests, which use a persistence in memory. No MongoDB server is needed,
 * the client is lazy.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AuthDataDiscriminatorMapListener::class)]
class AuthDataDiscriminatorMapListenerTest extends TestCase
{
    private const string LEGACY_API_KEYS_AUTH = 'Teknoo\Space\Object\Persisted\ApiKeysAuth';

    private function buildListener(): AuthDataDiscriminatorMapListener
    {
        return new AuthDataDiscriminatorMapListener([
            self::LEGACY_API_KEYS_AUTH => ApiKeysAuth::class,
        ]);
    }

    private function buildDocumentManager(): DocumentManager
    {
        $vendorDir = dirname(__DIR__, 4) . '/vendor/teknoo/east-common/infrastructures/doctrine/config';

        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl(
            new SimplifiedXmlDriver(
                [
                    $vendorDir . '/universal' => 'Teknoo\East\Common\Object',
                ],
                '.mongodb.xml',
            )
        );
        $configuration->setHydratorDir(sys_get_temp_dir() . '/space-tests-odm/Hydrators');
        $configuration->setHydratorNamespace('SpaceTestsOdmHydrators');
        $configuration->setProxyDir(sys_get_temp_dir() . '/space-tests-odm/Proxies');
        $configuration->setProxyNamespace('SpaceTestsOdmProxies');
        $configuration->setDefaultDB('space_tests');

        $eventManager = new EventManager();
        $eventManager->addEventListener(Events::loadClassMetadata, $this->buildListener());

        return DocumentManager::create(null, $configuration, $eventManager);
    }

    public function testTheMapListsAllAuthDataClassesThenTheAliases(): void
    {
        $metadata = $this->buildDocumentManager()->getClassMetadata(User::class);

        $expected = [
            ApiKeyToken::class => ApiKeyToken::class,
            ApiKeysAuth::class => ApiKeysAuth::class,
            RecoveryAccess::class => RecoveryAccess::class,
            StoredPassword::class => StoredPassword::class,
            TOTPAuth::class => TOTPAuth::class,
            ThirdPartyAuth::class => ThirdPartyAuth::class,
            self::LEGACY_API_KEYS_AUTH => ApiKeysAuth::class,
        ];

        $map = $metadata->fieldMappings['authData']['discriminatorMap'] ?? [];
        $this->assertEquals($expected, $map);
        //Aliases are after the class names
        $this->assertSame(self::LEGACY_API_KEYS_AUTH, array_key_last($map));
        $this->assertArrayNotHasKey(MediaMetadata::class, $map);
        $this->assertArrayNotHasKey(User::class, $map);

        //Both copies of the mapping are used by Doctrine
        $this->assertSame($map, $metadata->associationMappings['authData']['discriminatorMap'] ?? null);
    }

    public function testALegacyDocumentIsReadWithTheCurrentClass(): void
    {
        $documentManager = $this->buildDocumentManager();
        $mapping = $documentManager->getClassMetadata(User::class)->fieldMappings['authData'];

        $this->assertSame(
            ApiKeysAuth::class,
            $documentManager->getClassNameForAssociation(
                $mapping,
                ['type' => self::LEGACY_API_KEYS_AUTH, 'tokens' => []],
            ),
        );

        $this->assertSame(
            ApiKeysAuth::class,
            $documentManager->getClassNameForAssociation($mapping, ['type' => ApiKeysAuth::class, 'tokens' => []]),
        );

        $this->assertSame(
            StoredPassword::class,
            $documentManager->getClassNameForAssociation($mapping, ['type' => StoredPassword::class]),
        );
    }

    public function testAuthDataAreWrittenWithTheirCurrentClassName(): void
    {
        $map = $this->buildDocumentManager()
            ->getClassMetadata(User::class)
            ->fieldMappings['authData']['discriminatorMap'] ?? [];

        //Same search as the Doctrine's PersistenceBuilder : the first value found is written in the document
        foreach ([ApiKeysAuth::class, StoredPassword::class, TOTPAuth::class, ThirdPartyAuth::class] as $className) {
            $this->assertSame($className, array_search($className, $map));
        }
    }

    public function testAnAliasNeverReplacesAMappedClass(): void
    {
        $documentManager = $this->buildDocumentManager();

        $metadata = $documentManager->getClassMetadata(User::class);
        new AuthDataDiscriminatorMapListener([StoredPassword::class => ApiKeysAuth::class])->loadClassMetadata(
            new LoadClassMetadataEventArgs($metadata, $documentManager),
        );

        $this->assertSame(
            StoredPassword::class,
            $metadata->fieldMappings['authData']['discriminatorMap'][StoredPassword::class] ?? null,
        );
    }

    public function testOtherDocumentsAreNotChanged(): void
    {
        $metadata = $this->buildDocumentManager()->getClassMetadata(ApiKeysAuth::class);

        $this->assertArrayNotHasKey('discriminatorMap', $metadata->fieldMappings['tokens']);
    }

    public function testAUserWithoutAuthDataMappingIsNotChanged(): void
    {
        $metadata = new ClassMetadata(User::class);

        $this->buildListener()->loadClassMetadata(
            new LoadClassMetadataEventArgs($metadata, $this->buildDocumentManager()),
        );

        $this->assertSame([], $metadata->fieldMappings);
        $this->assertSame([], $metadata->associationMappings);
    }
}
