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

namespace Teknoo\Space\Recipe\Step\Dashboard;

use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;

use function in_array;

/**
 * Renders the dashboard of a user without account, not administrator, with its own template and without the clusters
 * of the platform: the next steps until the rendering are skipped.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RenderWithoutAccount
{
    public function __construct(
        private readonly string $template,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        User $user,
        ?Account $account = null,
    ): self {
        if (null !== $account || in_array('ROLE_ADMIN', (array) $user->getRoles(), true)) {
            return $this;
        }

        $manager->continue(['template' => $this->template], Render::class);

        return $this;
    }
}
