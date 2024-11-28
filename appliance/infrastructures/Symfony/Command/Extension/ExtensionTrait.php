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

namespace Teknoo\Space\Infrastructures\Symfony\Command\Extension;

use DomainException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Teknoo\East\Foundation\Extension\ExtensionInterface;

use function class_exists;
use function is_a;
use function is_array;
use function json_decode;
use function str_replace;

use const JSON_THROW_ON_ERROR;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait ExtensionTrait
{
    private ?Filesystem $filesystem = null;

    private function getFilesystem(): Filesystem
    {
        return $this->filesystem ??= new Filesystem(
            new LocalFilesystemAdapter($this->spacePath)
        );
    }

    /**
     * @return iterable<string, string>
     * @throws FilesystemException
     */
    private function listAvailablesExtensions(): iterable
    {
        foreach ($this->getFilesystem()->listContents('/extensions', false) as $item) {
            if (!$item->isDir()) {
                continue;
            }

            $name = str_replace('extensions/', '', $item->path());
            $classExtension = 'Teknoo\\Space\\Extensions\\' . $name . '\\Extension';

            if (!class_exists($classExtension, true) || !is_a($classExtension, ExtensionInterface::class, true)) {
                continue;
            }

            yield $classExtension => $name;
        }
    }

    /**
     * @throws FilesystemException
     */
    private function getFileAboutEnabledExtensions(): string
    {
        $fileName = $_ENV['TEKNOO_EAST_EXTENSION_FILE'] ?? 'extensions/enabled.json';

        $fileSystem = $this->getFilesystem();
        if (!$fileSystem->fileExists($fileName)) {
            throw new DomainException("The extension file $fileName doesn't exists.");
        }

        return $fileSystem->read($fileName);
    }

    /**
     * @return string[]
     */
    private function listEnabledExtensions(): array
    {
        $content = $this->getFileAboutEnabledExtensions();

        $list = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($list)) {
            $list = [];
        }

        return $list;
    }

    private function updateFileAboutEnabledExtensions(string $content): void
    {
        $fileName = $_ENV['TEKNOO_EAST_EXTENSION_FILE'] ?? 'extensions/enabled.json';

        $this->getFilesystem()->write($fileName, $content);
    }

    /**
     * @param string[] $classesList
     */
    private function updateEnabledExtensions(array $classesList): void
    {
        $this->updateFileAboutEnabledExtensions(json_encode($classesList, JSON_THROW_ON_ERROR));
    }
}
