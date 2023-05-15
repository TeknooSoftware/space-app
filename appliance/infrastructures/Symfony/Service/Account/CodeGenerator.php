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

namespace Teknoo\Space\Infrastructures\Symfony\Service\Account;

use RuntimeException;
use Teknoo\Recipe\Promise\PromiseInterface;

use function base64_encode;
use function hash;
use function strtoupper;
use function substr;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CodeGenerator
{
    public function __construct(
        private string $codeGeneratorSalt,
    ) {
    }

    private function computeCode(string $value): string
    {
        $hash = hash('sha256', $this->codeGeneratorSalt . $value);
        $base64 = base64_encode($hash);
        return strtoupper(substr($base64, 0, 8));
    }

    /**
     * @param PromiseInterface<string, mixed> $promise
     */
    public function verify(
        string $value,
        string $code,
        PromiseInterface $promise,
    ): self {
        if ($this->computeCode($value) === $code) {
            $promise->success($code);
        } else {
            $promise->fail(new RuntimeException('teknoo.space.error.code_not_accepted', 403));
        }

        return $this;
    }

    /**
     * @param PromiseInterface<string, mixed> $promise
     */
    public function generateCode(
        string $value,
        PromiseInterface $promise,
    ): self {
        $promise->success(
            $this->computeCode($value)
        );

        return $this;
    }
}
