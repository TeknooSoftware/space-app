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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat;

use Teknoo\East\Paas\Contracts\Hook\HookInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
class HookMock implements HookInterface
{
    public function __construct(
        public array $options = [],
    ) {
    }

    public function setPath(string $path): HookInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options, PromiseInterface $promise): HookInterface
    {
        $this->options = $options;
        $promise->success();
        return $this;
    }

    public function run(PromiseInterface $promise): HookInterface
    {
        $promise->success('foo');
        return $this;
    }
}
