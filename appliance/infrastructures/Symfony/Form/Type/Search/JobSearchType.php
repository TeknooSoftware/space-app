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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Search;

use Symfony\Component\Form\AbstractType;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Query\Expr\Regex;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\Search;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobSearchType extends AbstractType
{
    use DefaultSearchTrait;

    public function getBlockPrefix(): string
    {
        return 'job_search';
    }

    protected static function onSubmit(Search $search, ManagerInterface $manager): void
    {
        $manager->updateWorkPlan([
            'criteria' => [
                'jobSearch' => new InclusiveOr(
                    [
                        'id' => new Regex($search->search),
                    ],
                    [
                        'created_at' => new Regex($search->search),
                    ],
                    [
                        'base_namespace' => new Regex($search->search),
                    ],
                )
            ],
        ]);
    }
}
