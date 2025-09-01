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

namespace Teknoo\Space\Recipe\Step\SpaceProject;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\SpaceProject;

use function str_contains;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PrepareRedirection
{
    /**
     * @param array<string, string> $parameters
     */
    public function __invoke(
        ManagerInterface $manager,
        SpaceProject $spaceProject,
        string $route,
        bool|string|null $objectSaved = null,
        array $parameters = [],
        ?string $accountId = null,
    ): self {
        $parameters['id'] = $spaceProject->getId();
        $parameters['objectSaved'] = $objectSaved;

        if (null !== $accountId || str_contains($route, '_admin')) {
            $parameters['accountId'] = $accountId;
        }

        $manager->updateWorkPlan([
            'id' => $spaceProject->getId(),
            'parameters' => $parameters,
        ]);

        return $this;
    }
}
