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

namespace Teknoo\Space\Tests\Behat\Traits;

use Behat\Step\Then;
use PHPUnit\Framework\Assert;
use Symfony\Component\Mailer\Event\MessageEvents;
use Symfony\Component\Mailer\Test\Constraint\EmailCount;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait NotificationTrait
{
    public function getMailerEvents(): MessageEvents
    {
        Assert::assertNotNull(
            $this->messageLoggerListener,
            'Symfony Mailer is not configured'
        );

        return $this->messageLoggerListener->getEvents();
    }

    #[Then('no notification must be sent')]
    public function noNotificationMustBeSent(): void
    {
        Assert::assertThat(
            $this->getMailerEvents(),
            new EmailCount(0),
        );
    }

    #[Then('a notification must be sent')]
    public function aNotificationMustBeSent(): void
    {
        Assert::assertThat(
            $this->getMailerEvents(),
            new EmailCount(1),
        );
    }
}
