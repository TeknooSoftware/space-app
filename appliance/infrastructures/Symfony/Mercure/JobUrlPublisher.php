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

namespace Teknoo\Space\Infrastructures\Symfony\Mercure;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobUrlPublisher
{
    public function __construct(
        private HubInterface $hub,
        private bool $enabled = true,
    ) {
    }

    public function publish(
        string $url,
        string $newJobId,
        ?string $jobUrl,
    ): static {
        if (!$this->enabled) {
            return $this;
        }

        $id = null;
        if (null === $jobUrl) {
            $id = $newJobId;
        }

        $update = new Update(
            topics: $url,
            data: json_encode(
                [
                    'new_job_id' => $newJobId,
                    'job_url' => $jobUrl,
                ],
                JSON_THROW_ON_ERROR,
            ),
            id: $id,
        );

        $this->hub->publish($update);

        return $this;
    }
}
