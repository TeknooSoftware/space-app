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

use Behat\Step\When;
use DateTime;
use PHPUnit\Framework\Assert;

use function trim;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait JwtTrait
{
    /**     *
     * @throws \DateMalformedStringException
     */
    #[When('get a JWT token for the user')]
    public function getAJwtTokenForTheUser(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_my_settings_jwt_token',
        );

        $dateInFuture = new DateTime('now'); //Use now, because JWT Bundle does not use DatesService
        $dateInFuture->modify('+2 days');

        $values = $this->createForm('jwt_configuration')->getPhpValues();
        $values['jwt_configuration']['expirationDate'] = $dateInFuture->format('Y-m-d');

        $this->executeRequest(
            method: 'POST',
            url: $this->getPathFromRoute('space_my_settings_jwt_token'),
            params: $values
        );

        $node = $this->createCrawler()->filter('.jwt-token-value');
        $this->jwtToken = trim((string) $node->getNode(0)?->textContent);

        Assert::assertNotEmpty($this->jwtToken);
    }
}
