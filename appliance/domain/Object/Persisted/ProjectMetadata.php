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

namespace Teknoo\Space\Object\Persisted;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\East\Paas\Object\Traits\ExportConfigurationsTrait;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ProjectMetadata implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    VisitableInterface,
    NormalizableInterface
{
    use ObjectTrait;
    use GroupsTrait;
    use ExportConfigurationsTrait;

    private Project $project;

    private ?string $projectUrl = null;

    /**
     * @var array<string, string[]>
     */
    private static array $exportConfigurations = [
        '@class' => ['default', 'crud'],
        'projectUrl' => ['crud'],
    ];

    public function __construct(
        ?Project $project = null,
        ?string $projectUrl = null
    ) {
        if ($project instanceof Project) {
            $this->project = $project;
        }

        $this->projectUrl = $projectUrl;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function setProjectUrl(?string $projectUrl): self
    {
        $this->projectUrl = $projectUrl;

        return $this;
    }

    public function visit($visitors): VisitableInterface
    {
        if (isset($visitors['projectUrl'])) {
            $visitors['projectUrl']($this->projectUrl);
        }

        return $this;
    }

    public function export(ParametersBag $bag): self
    {
        $bag->set('projectUrl', $this->projectUrl);

        return $this;
    }

    public function exportToMeData(EastNormalizerInterface $normalizer, array $context = []): NormalizableInterface
    {
        $data = [
            '@class' => self::class,
            'projectUrl' => $this->projectUrl,
        ];

        $this->setGroupsConfiguration(self::$exportConfigurations);

        $normalizer->injectData(
            $this->filterExport(
                data: $data,
                groups: (array) ($context['groups'] ?? ['default']),
            )
        );

        return $this;
    }
}
