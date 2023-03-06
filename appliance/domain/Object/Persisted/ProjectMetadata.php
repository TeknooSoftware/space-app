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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
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
use Teknoo\East\Paas\Object\Project;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ProjectMetadata implements IdentifiedObjectInterface, TimestampableInterface, VisitableInterface
{
    use ObjectTrait;

    private Project $project;

    private ?string $projectUrl = null;

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
}
