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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Search;

use Symfony\Component\Form\AbstractType;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Query\Expr\Regex;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\Search;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class MediaSearchType extends AbstractType
{
    use DefaultSearchTrait;

    public function getBlockPrefix(): string
    {
        return 'media_search';
    }

    protected static function onSubmit(Search $search, ManagerInterface $manager): void
    {
        $manager->updateWorkPlan([
            'criteria' => [
                'mediaSearch' => new InclusiveOr(
                    [
                        'metadata.file_name' => new Regex($search->search),
                    ],
                    [
                        'metadata.alternative' => new Regex($search->search),
                    ],
                )
            ],
        ]);
    }
}
