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

namespace Teknoo\Space\Liveness;

use Teknoo\East\Foundation\Liveness\PingService;
use Teknoo\East\Foundation\Time\TimerService;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
class PingScheduler
{
    public function __construct(
        private PingService $pingService,
        private PingFile $pingFile,
        private TimerService $timerService,
        private int $timerSeconds
    ) {
    }

    public function timerAction(): self
    {
        $this->pingService->ping();
        $this->timerRegister();

        return $this;
    }

    private function timerRegister(): self
    {
        $this->timerService->register(
            seconds: $this->timerSeconds,
            timerId: self::class,
            callback: $this->timerAction(...),
        );

        return $this;
    }

    public function enable(): self
    {
        $this->pingService->register(
            PingFile::class,
            $this->pingFile,
        );

        $this->timerRegister();

        return $this;
    }

    public function disable(): self
    {
        $this->pingService->unregister(PingFile::class);
        $this->timerService->unregister(self::class);

        return $this;
    }
}
