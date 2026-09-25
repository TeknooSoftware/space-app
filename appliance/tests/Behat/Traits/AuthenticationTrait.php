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

namespace Teknoo\Space\Tests\Behat\Traits;

use Behat\Step\Given;

/**
 * Composite steps replacing the sign in / TOTP / JWT / logout ritual repeated in almost every
 * scenario. They only chain the steps already defined in the other traits, they implement nothing
 * on their own, so a scenario testing the login flow itself keeps using the explicit steps.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait AuthenticationTrait
{
    #[Given('the user is signed in with :email and the password :password')]
    public function theUserIsSignedInWith(string $email, string $password): void
    {
        $this->theUserSignInWithAndThePassword(email: $email, password: $password);
        $this->itMustBeRedirectedToTheTotpCodePage();
        $this->theUserEnterAValidTotpCode();
    }

    #[Given('the user is authenticated on the API with :email and the password :password')]
    public function theUserIsAuthenticatedOnTheApiWith(string $email, string $password): void
    {
        $this->theUserIsSignedInWith(email: $email, password: $password);
        $this->getAJwtTokenForTheUser();
        $this->theUserLogsOut();
    }
}
