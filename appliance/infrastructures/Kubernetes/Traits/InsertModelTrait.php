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

namespace Teknoo\Space\Infrastructures\Kubernetes\Traits;

use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Kubernetes\Model\Model;
use Teknoo\Kubernetes\Repository\Repository;
use Teknoo\Space\Infrastructures\Kubernetes\Exception\KubernetesErrorException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template T of \Teknoo\Kubernetes\Model\Model
 */
trait InsertModelTrait
{
    private KubernetesClient $client;

    /**
     * @param Repository<T> $repository
     */
    private function insertModel(Repository $repository, Model $model, bool $updateIfExist = false): void
    {
        if (!$repository->exists((string) $model->getMetadata('name'))) {
            $result = $repository->create($model);
        } elseif ($updateIfExist) {
            $result = $repository->update($model);
        }

        if (!empty($result['status']) && 'Failure' === $result['status']) {
            throw new KubernetesErrorException($result['message'] ?? 'Error in kubernetes request');
        }
    }
}
