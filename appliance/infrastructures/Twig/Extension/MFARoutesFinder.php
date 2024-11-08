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

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as TFIGoogle;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TFGeneric;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\Space\Infrastructures\Twig\Extension\MFARoutesFinder\Exception\MissingRouteException;
use Teknoo\Space\Infrastructures\Twig\Extension\MFARoutesFinder\Exception\RuntimeException;
use Teknoo\Space\Infrastructures\Twig\Extension\MFARoutesFinder\Operation;
use Teknoo\Space\Infrastructures\Twig\Extension\MFARoutesFinder\Provider;
use Throwable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class MFARoutesFinder extends AbstractExtension
{
    /**
     * @param array<string, array<string, string>> $routesFor2FAByProviders
     */
    public function __construct(
        private string $default2FAProviderName,
        private array $routesFor2FAByProviders,
    ) {
    }

    public function getFunctions(): array
    {
        return array(
            new TwigFunction(
                name: 'space_2fa_route_finder',
                callable: $this->find(...),
            )
        );
    }

    public function getName(): string
    {
        return 'space_2fa_route_finder';
    }

    public function find(
        UserInterface $user,
        string $operationStr,
    ): string {
        try {
            $provider = match (true) {
                $user instanceof TFIGoogle => Provider::GOOGLE,
                $user instanceof TFGeneric => Provider::GENERIC,
                default => Provider::from($this->default2FAProviderName),
            };

            $operation = Operation::from($operationStr);

            $route = $this->routesFor2FAByProviders[$provider->value][$operation->value] ?? null;

            if (empty($route)) {
                throw new MissingRouteException(
                    'No route for "' . $provider->value . '" with "' . $operation->value . '"',
                );
            }

            return $route;
        } catch (Throwable $error) {
            throw new RuntimeException(
                message: $error->getMessage(),
                code: $error->getCode(),
                previous: $error->getPrevious(),
            );
        }
    }
}
