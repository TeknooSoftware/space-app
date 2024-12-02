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

namespace Teknoo\Space\Object\DTO;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Project;
use Teknoo\East\Paas\Object\Traits\ExportConfigurationsTrait;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SpaceProject implements IdentifiedObjectInterface, NormalizableInterface
{
    use GroupsTrait;
    use ExportConfigurationsTrait;

    public Project $project;

    /**
     * @var array<string, string[]>
     */
    private static array $exportConfigurations = [
        '@class' => ['default', 'api', 'crud', 'crud_variables', 'digest'],
        'project' => ['default', 'api', 'crud', 'digest'],
        'projectMetadata' => ['crud'],
        'variables' => ['crud_variables'],
    ];

    /**
     * @param iterable<ProjectPersistedVariable>|ProjectPersistedVariable[] $variables
     */
    public function __construct(
        Project|Account $projectOrAccount,
        public ?ProjectMetadata $projectMetadata = null,
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

    public function exportToMeData(EastNormalizerInterface $normalizer, array $context = []): NormalizableInterface
    {
        $data = [
            '@class' => self::class,
            'project' => fn () => $this->project,
            'projectMetadata' => fn () => $this->projectMetadata,
            'variables' => fn () => $this->variables,
        ];

        $this->setGroupsConfiguration(self::$exportConfigurations);

        $normalizer->injectData(
            $this->filterExport(
                data: $data,
                groups: (array) ($context['groups'] ?? ['default']),
                lazyData: true,
            )
        );

        return $this;
    }
}
