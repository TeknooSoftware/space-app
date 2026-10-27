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

namespace Teknoo\Space\Infrastructures\Symfony\Security\Authorization;

use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Teknoo\East\CommonBundle\Twig\Extension\ApiErrorSerializing;

/**
 * Render a refusal of the firewall of the API (rules of `access_control`) like the other errors of the JSON API, as
 * East Common does for the authentication failures (`ApiAuthenticationFailureListener`). Without it, the response has
 * an HTML body.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiAccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private readonly ApiErrorSerializing $errorSerializing,
    ) {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): Response
    {
        return JsonResponse::fromJsonString(
            $this->errorSerializing->serialize(
                new RuntimeException(
                    'teknoo.space.error.access_control.not_authorized',
                    Response::HTTP_FORBIDDEN,
                ),
            ),
            Response::HTTP_FORBIDDEN,
        );
    }
}
