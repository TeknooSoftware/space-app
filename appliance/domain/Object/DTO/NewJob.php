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

namespace Teknoo\Space\Object\DTO;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;

use function hash;
use function random_int;
use function sha1;
use function substr;
use function uniqid;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class NewJob implements ObjectInterface
{
    /**
     * @param JobVar[] $variables
     */
    public function __construct(
        public string $newJobId = '',
        public array $variables = [],
        public ?string $projectId = null,
        public ?string $envName = null,
    ) {
        if (empty($this->newJobId)) {
            $this->newJobId = substr(
                string: uniqid(
                    prefix: hash(
                        'sha256',
                        (string) random_int(
                            min: 10000,
                            max: 99999,
                        ),
                    ),
                    more_entropy: true,
                ),
                offset: 0,
                length: 24
            );
        }
    }

    /*
     * To remove all occurences of persisted object or doctrine proxies in a serialized representation
     */
    public function export(): self
    {
        $that = clone $this;
        $that->variables = [];
        foreach ($this->variables as $variable) {
            $that->variables[] = $variable->export();
        }

        return $that;
    }
}
