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

namespace Teknoo\Space\Recipe\Plan;

use Stringable;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Plan\ListObjectEndPoint;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Step\Account\LoadAccountFromRequest;
use Teknoo\Space\Recipe\Step\Misc\PrepareCriteria;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ProjectList extends ListObjectEndPoint
{
    /**
     * @param array<string, string> $loadListObjectsWiths
     */
    public function __construct(
        RecipeInterface $recipe,
        ExtractPage $extractPage,
        ExtractOrder $extractOrder,
        private LoadAccountFromRequest $loadAccountFromRequest,
        private PrepareCriteria $prepareCriteria,
        LoadListObjects $loadListObjects,
        RenderList $renderList,
        RenderError $renderError,
        SearchFormLoaderInterface $searchFormLoader,
        ListObjectsAccessControlInterface $listObjectsAccessControl,
        string|Stringable $defaultErrorTemplate,
        array $loadListObjectsWiths,
    ) {
        parent::__construct(
            $recipe,
            $extractPage,
            $extractOrder,
            $loadListObjects,
            $renderList,
            $renderError,
            $searchFormLoader,
            $listObjectsAccessControl,
            $defaultErrorTemplate,
            $loadListObjectsWiths
        );
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = parent::populateRecipe($recipe);

        $recipe = $recipe->cook($this->loadAccountFromRequest, LoadAccountFromRequest::class, [], 24);
        $recipe = $recipe->cook($this->prepareCriteria, PrepareCriteria::class, [], 25);

        return $recipe;
    }
}
