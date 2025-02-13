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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat;

use Teknoo\East\Foundation\Extension\ModuleInterface;
use Teknoo\Space\Object\Persisted\AccountRegistry;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
class ExpectedManifestTestExtension implements ModuleInterface
{
    public function __construct(
        public readonly string $action,
        public array $json,
        public readonly ?string $name = null,
        public readonly ?string $namespace = null,
        public readonly ?array $namespaces = null,
        public readonly ?array $accountQuotas = null,
        public readonly ?AccountRegistry $registry = null,
        public readonly ?string $projectPrefix = null,
        public readonly ?string $jobId = null,
        public readonly ?string $hncSuffix = null,
        public readonly ?bool $useHnc = null,
        public readonly ?string $quotaMode = null,
        public readonly ?string $defaultsMods = null,
    ) {
    }
}
