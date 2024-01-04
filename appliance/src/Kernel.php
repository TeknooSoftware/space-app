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

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use function rtrim;
use function str_contains;
use function strlen;
use function strrpos;
use function substr;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const PHAR_NAME = 'space.phar';

    private const PHAR_SCHEME = 'phar://';

    private ?string $rootDir = null;

    private static function isPhar(): bool
    {
        return str_contains(__FILE__, self::PHAR_NAME);
    }

    private static function getParentDirOf(string $path, string $find): string
    {
        $pos = strrpos($path, $find);
        if (false === $pos) {
            return $path;
        }

        return substr($path, 0, $pos);
    }

    public function getProjectDir(): string
    {
        $projectDir = parent::getProjectDir();

        if (self::isPhar()) {
            $projectDir = self::getParentDirOf($projectDir, '/src');
        }

        return $projectDir;
    }

    public function getRootDir(): string
    {
        if (null !== $this->rootDir) {
            return $this->rootDir;
        }

        $rootDir = rtrim(self::getParentDirOf($this->getProjectDir(), self::PHAR_NAME), '/');
        if (str_contains($rootDir, self::PHAR_SCHEME)) {
            $rootDir = substr($rootDir, strlen(self::PHAR_SCHEME));
        }

        return $this->rootDir = $rootDir;
    }

    public function getCacheDir(): string
    {
        if (self::isPhar()) {
            return $this->getRootDir() . '/var/cache/' . $this->environment;
        }

        return parent::getCacheDir();
    }

    public function getLogDir(): string
    {
        if (self::isPhar()) {
            return $this->getRootDir() . '/var/log';
        }

        return parent::getLogDir();
    }

    /**
     * @return array<string, string>
     */
    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();

        if (self::isPhar()) {
            $parameters['space.root_dir'] = $this->getRootDir();
        } else {
            $parameters['space.root_dir'] = $this->getProjectDir();
        }

        return $parameters;
    }
}
