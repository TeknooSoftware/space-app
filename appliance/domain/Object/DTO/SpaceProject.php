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

namespace Teknoo\Space\Object\DTO;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\Persisted\PersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SpaceProject implements IdentifiedObjectInterface
{
    public Project $project;

    /**
     * @param iterable<PersistedVariable>|PersistedVariable[] $variables
     */
    public function __construct(
        Project|Account $projectOrAccount,
        public ?ProjectMetadata $projectMetadata = null,
        public iterable $variables = [],
    ) {
        if ($projectOrAccount instanceof Account) {
            $this->project = new Project($projectOrAccount);
        } else {
            $this->project = $projectOrAccount;
        }
    }

    public function getId(): string
    {
        return (string) $this->project->getId();
    }

    public function getAccount(): Account
    {
        return $this->project->getAccount();
    }

    public function __toString(): string
    {
        return (string) $this->project;
    }
}
