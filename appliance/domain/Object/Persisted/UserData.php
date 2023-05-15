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

namespace Teknoo\Space\Object\Persisted;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Common\Object\User;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserData implements IdentifiedObjectInterface, TimestampableInterface, VisitableInterface
{
    use ObjectTrait;

    private ?Media $picture = null;

    public function __construct(
        private User $user,
        ?Media $picture = null,
    ) {
        $this->picture = $picture;
    }

    public function setUser(User $user): UserData
    {
        $this->user = $user;

        return $this;
    }

    public function setPicture(?Media $picture): UserData
    {
        $this->picture = $picture;

        return $this;
    }

    public function getPicture(): ?Media
    {
        return $this->picture;
    }

    public function visit($visitors): VisitableInterface
    {
        if (isset($visitors['picture'])) {
            $visitors['picture']($this->picture);
        }

        return $this;
    }
}
