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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat\ODM;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\ODM\MongoDB\Repository\UploadOptions;
use Doctrine\Persistence\ObjectManager;
use Teknoo\Space\Tests\Behat\TestsContext;

class GridFSMemoryRepository extends MemoryRepository implements GridFSRepository
{
    public function openDownloadStream($id)
    {
        throw new \Exception('todo1');
    }

    public function downloadToStream($id, $destination): void
    {
        throw new \Exception('todo2');
    }

    public function openUploadStream(string $filename, ?UploadOptions $uploadOptions = null)
    {
        throw new \Exception('todo3');
    }

    public function uploadFromStream(string $filename, $source, ?UploadOptions $uploadOptions = null)
    {
        throw new \Exception('todo4');
    }

    public function uploadFromFile(string $source, ?string $filename = null, ?UploadOptions $uploadOptions = null)
    {
        throw new \Exception('todo5');
    }
}
