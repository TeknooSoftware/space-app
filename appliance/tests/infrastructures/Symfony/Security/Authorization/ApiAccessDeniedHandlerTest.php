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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Security\Authorization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Teknoo\East\CommonBundle\Twig\Extension\ApiErrorSerializing;
use Teknoo\Space\Infrastructures\Symfony\Security\Authorization\ApiAccessDeniedHandler;

/**
 * Class ApiAccessDeniedHandlerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ApiAccessDeniedHandler::class)]
class ApiAccessDeniedHandlerTest extends TestCase
{
    public function testHandleRendersTheJsonErrorEnvelope(): void
    {
        $serializing = $this->createMock(ApiErrorSerializing::class);
        $serializing->expects($this->once())
            ->method('serialize')
            ->with(
                $this->callback(
                    static fn (RuntimeException $error): bool =>
                        'teknoo.space.error.access_control.not_authorized' === $error->getMessage()
                        && 403 === $error->getCode()
                        && null === $error->getPrevious()
                )
            )
            ->willReturn('{"meta":{"error":true},"data":{"code":403}}');

        $response = (new ApiAccessDeniedHandler($serializing))->handle(
            new Request(),
            new AccessDeniedException(),
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('{"meta":{"error":true},"data":{"code":403}}', $response->getContent());
        $this->assertStringStartsWith('application/json', (string) $response->headers->get('Content-Type'));
    }
}
