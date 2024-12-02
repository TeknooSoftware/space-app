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

namespace Teknoo\Space\Infrastructures\Symfony\Command\Extension;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\East\Foundation\Extension\FileLoader;

use function array_flip;
use function array_unique;
use function is_string;
use function iterator_to_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class EnableCommand extends Command
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
        $this->setName('teknoo:space:extension:enable')
            ->setDescription('To enable an extension in Space')
            ->addArgument('extension', InputArgument::REQUIRED, 'Extension name to enable');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->getFormatter()->setDecorated(true);

        if (!empty($_ENV['TEKNOO_EAST_EXTENSION_DISABLED'])) {
            $output->writeln('<error>Extensions are disabled.</error>');

            return self::FAILURE;
        }

        if (FileLoader::class !== ($_ENV['TEKNOO_EAST_EXTENSION_LOADER'] ?? FileLoader::class)) {
            $output->writeln('<error>This command is only available with the FileLoader extension.</error>');

            return self::FAILURE;
        }

        $enabledExtensions = $this->listEnabledExtensions();
        $availablesExtensions = array_flip(iterator_to_array($this->listAvailablesExtensions()));

        $extensionName = $input->getArgument('extension');
        if (!\is_string($extensionName)) {
            $output->writeln("<error>The Extension name is invalid.</error>");

            return self::INVALID;
        }

        if (!isset($availablesExtensions[$extensionName])) {
            $output->writeln("<error>Extension $extensionName is not available.</error>");

            return self::INVALID;
        }

        $enabledExtensions[] = $availablesExtensions[$extensionName];
        $enabledExtensions = array_unique($enabledExtensions);

        $this->updateEnabledExtensions($enabledExtensions);

        $output->writeln("<info>Extension $extensionName is enabled.</info>");

        $command = $this->getApplication()?->find('cache:warmup');
        $returnCode = $command?->run(new ArrayInput(['command' => 'cache:warmup']), $output);

        if (Command::SUCCESS === $returnCode) {
            $output->writeln('<info>Cache warmup successful</info>');
        } else {
            $output->writeln('<error>Error during cache warlup</error>');
        }

        return self::SUCCESS;
    }
}
