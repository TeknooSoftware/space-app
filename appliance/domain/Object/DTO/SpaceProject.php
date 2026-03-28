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

namespace Teknoo\Space\Object\DTO;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Foundation\Normalizer\Object\AutoTrait;
use Teknoo\East\Foundation\Normalizer\Object\ClassGroup;
use Teknoo\East\Foundation\Normalizer\Object\Normalize;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[ClassGroup('default', 'api', 'crud', 'crud_variables', 'digest')]
class SpaceProject implements IdentifiedObjectInterface, NormalizableInterface, \Stringable
{
    use AutoTrait;

    #[Normalize(['default', 'api', 'crud', 'digest'], loader: '@lazy')]
    public Project $project;

    /**
     * @param iterable<ProjectPersistedVariable>|ProjectPersistedVariable[] $variables
     */
    public function __construct(
        Project|Account $projectOrAccount,
        #[Normalize('crud', loader: '@lazy')]
        public ?ProjectMetadata $projectMetadata = null,
        #[Normalize('crud_variables', loader: '@lazy')]
        public iterable $variables = [],
        public ?string $addClusterName = null,
        public ?string $addClusterEnv = null,
    ) {
        if ($projectOrAccount instanceof Account) {
            $this->project = new Project($projectOrAccount);
        } else {
            $this->project = $projectOrAccount;
        }
    }

    public function getId(): string
    {
        return $this->project->getId();
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
