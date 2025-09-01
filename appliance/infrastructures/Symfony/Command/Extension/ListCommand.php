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

namespace Teknoo\Space\Infrastructures\Symfony\Command\Extension;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\East\Foundation\Extension\FileLoader;

use function array_flip;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ListCommand extends Command
{
    use ExtensionTrait;

    public function __construct(
        private string $spacePath,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('teknoo:space:extension:list')
            ->setDescription('To list all extensions installed in the space, enabled and disabled');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->getFormatter()->setDecorated(true);

        if (!empty($_ENV['TEKNOO_EAST_EXTENSION_DISABLED'])) {
            $output->writeln('<error>Extensions are disabled.</error>');

            return self::FAILURE;
        }

        $enabledExtensions = $this->listEnabledExtensions();

        // Création du tableau
        $table = new Table($output);
        $table->setHeaders(['Extension', 'Enabled']);

        if (FileLoader::class !== ($_ENV['TEKNOO_EAST_EXTENSION_LOADER'] ?? FileLoader::class)) {
            $output->writeln('<error>With the current extension loader, only enabled extensions can be shown.</error>');

            $rows = [];
            foreach ($enabledExtensions as $extension) {
                $rows[] = [$extension, 'Yes'];
            }

            $table->setRows($rows);
        } else {
            $enabledExtensions = array_flip($enabledExtensions);
            $availablesExtensions = $this->listAvailablesExtensions();
            $rows = [];
            foreach ($availablesExtensions as $classExtension => $extension) {
                $rows[] = [$extension, isset($enabledExtensions[(string) $classExtension]) ? 'Yes' : 'No'];
            }

            $table->setRows($rows);
        }

        $table->render();

        return self::SUCCESS;
    }
}
