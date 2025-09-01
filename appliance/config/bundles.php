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

namespace Teknoo\Space\App\Config;

use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle;
use KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MercureBundle\MercureBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Teknoo\DI\SymfonyBridge\DIBridgeBundle;
use Teknoo\East\CommonBundle\TeknooEastCommonBundle;
use Teknoo\East\FoundationBundle\EastFoundationBundle;
use Teknoo\East\FoundationBundle\Extension\Bundles as BundlesExtension;
use Teknoo\East\Paas\Infrastructures\EastPaasBundle\TeknooEastPaasBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
use Zenstruck\Messenger\Test\ZenstruckMessengerTestBundle;

$bundles = [
    DoctrineMongoDBBundle::class => ['all' => true],
    DIBridgeBundle::class => ['all' => true],
    EastFoundationBundle::class => ['all' => true],
    TeknooEastCommonBundle::class => ['all' => true],
    TeknooEastPaasBundle::class => ['all' => true],
    FrameworkBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    KnpUOAuth2ClientBundle::class => ['all' => true],
    SchebTwoFactorBundle::class => ['all' => true],
    LexikJWTAuthenticationBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],
    MercureBundle::class => ['all' => true],
    DebugBundle::class => ['dev' => true, 'test' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    FriendsOfBehatSymfonyExtensionBundle::class => ['test' => true],
    ZenstruckMessengerTestBundle::class => ['test' => true],
];

BundlesExtension::extendsBundles($bundles);

return $bundles;
