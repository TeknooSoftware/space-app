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

namespace Teknoo\Space\Writer;

use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Writer\PersistTrait;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\EncryptableVariableInterface;
use Teknoo\Space\Service\PersistedVariableEncryption;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType
 */
trait PersistedVariableWriterTrait
{
    /**
     * @use PersistTrait<TSuccessArgType>
     */
    use PersistTrait;

    public function __construct(
        private ManagerInterface $manager,
        private PersistedVariableEncryption $encryptionService,
        private ?DatesService $datesService = null,
        protected bool $preferRealDateOnUpdate = false,
    ) {
    }

    /**
     * @param TSuccessArgType $object
     * @param PromiseInterface<TSuccessArgType, mixed>|null $promise
     * @return WriterInterface<TSuccessArgType>
     */
    public function save(
        ObjectInterface $object,
        ?PromiseInterface $promise = null,
        ?bool $preferRealDateOnUpdate = null,
    ): WriterInterface {
        /** @var Promise<EncryptableVariableInterface, mixed, mixed> $decoredPromise */
        $decoredPromise = new Promise(
            fn (ObjectInterface $variable) => $this->persist($variable, $promise, $preferRealDateOnUpdate),
            fn (Throwable $error) => $promise?->fail($error),
        );

        if (
            !$object instanceof EncryptableVariableInterface
            || !$object->isSecret()
            || $object->isEncrypted()
            || !$object->mustEncrypt()
        ) {
            $decoredPromise->success($object);
        } else {
            $this->encryptionService->encrypt($object, $decoredPromise);
        }

        return $this;
    }
}
