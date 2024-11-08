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

namespace Teknoo\Space\Infrastructures\Twig\Extension;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function array_merge;
use function is_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UpdateQueryField extends AbstractExtension
{
    public function __construct(
        private UrlGeneratorInterface $generator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                name: 'app_update_query_field',
                callable: $this->updateQueryField(...)
            )
        ];
    }

    public function getName(): string
    {
        return 'app_update_query_field';
    }

    public function updateQueryField(
        Request $request,
        string $field,
        string $value,
    ): string {
        $params = $request->attributes->get('_route_params');
        if (!is_array($params)) {
            $params = [];
        }

        return $this->generator->generate(
            (string) $request->attributes->get('_route'),
            array_merge(
                $request->query->all(),
                $params,
                [
                    $field => $value,
                ],
            ),
        );
    }
}
