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

namespace Teknoo\Space\Tests\Behat;

use Behat\Testwork\ServiceContainer\Extension as TestworkExtension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\Foundation\Extension\Manager;

use function class_exists;
use function dirname;
use function glob;
use function is_dir;
use function is_array;
use function str_replace;
use function substr;

/**
 * Discovers and registers Behat feature files and context classes from
 * all enabled Space extensions (via Teknoo\East\Foundation\Extension\Manager).
 */
final class ExtensionsDiscoveryExtension implements TestworkExtension
{
    public function getConfigKey(): string
    {
        return 'space_discovery_extensions';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $settings = [];
        if ($container->hasParameter('suite.configurations')) {
            $settings = $container->getParameter('suite.configurations');
        }

        if (!is_array($settings)) {
            return;
        }

        foreach (Manager::run()->listLoadedExtensions() as $extensionClass => $_) {
            if (!class_exists($extensionClass)) {
                continue;
            }

            $rc = new ReflectionClass($extensionClass);
            $extensionBase = dirname($rc->getFileName());

            // Add feature paths
            $featuresDir = $extensionBase . '/features/';
            $settings['default']['settings']['paths'] ??= [];
            if (is_dir($featuresDir)) {
                $featureFiles = glob($featuresDir . '*.feature');
                if ($featureFiles) {
                    $featureDirs = array_unique(array_map('dirname', $featureFiles));
                    foreach ($featureDirs as $dir) {
                        if (!in_array($dir, $settings['default']['settings']['paths'], true)) {
                            $settings['default']['settings']['paths'][] = $dir;
                        }
                    }
                }
            }

            // Add context classes
            $contextDir = $extensionBase . '/Tests/Behat/Context/';
            if (is_dir($contextDir)) {
                $contextFiles = glob($contextDir . '*Context.php');
                $settings['default']['settings']['contexts'] ??= [];
                if ($contextFiles) {
                    foreach ($contextFiles as $contextFile) {
                        $className = substr(basename($contextFile), 0, -4);
                        $fqcn = $rc->getNamespaceName() . '\\Tests\\Behat\\Context\\' . $className;
                        if (class_exists($fqcn) && !in_array($fqcn, $settings['default']['settings']['contexts'], true)) {
                            $settings['default']['settings']['contexts'][] = $fqcn;
                        }
                    }
                }
            }
        }

        $container->setParameter('suite.configurations', $settings);
    }

    public function process(ContainerBuilder $container): void
    {
    }
}
