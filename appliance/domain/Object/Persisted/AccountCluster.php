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

use SensitiveParameter;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountCluster implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    ImmutableInterface,
    SluggableInterface
{
    use ObjectTrait;
    use ImmutableTrait;

    private Account $account;

    private string $name;

    private string $slug;

    private string $type;

    private string $masterAddress;

    private string $storageProvisioner;

    private string $dashboardAddress;

    private string $caCertificate;

    private string $token;

    private bool $useHnc;

    public function __construct(
        Account $account,
        string $name,
        string $slug,
        string $type,
        string $masterAddress,
        string $storageProvisioner,
        string $dashboardAddress,
        string $caCertificate,
        #[SensitiveParameter]
        string $token,
        bool $useHnc,
    ) {
        $this->uniqueConstructorCheck();

        $this->account = $account;
        $this->name = $name;
        $this->type = $type;
        $this->slug = $slug;
        $this->masterAddress = $masterAddress;
        $this->storageProvisioner = $storageProvisioner;
        $this->dashboardAddress = $dashboardAddress;
        $this->caCertificate = $caCertificate;
        $this->token = $token;
        $this->useHnc = $useHnc;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMasterAddress(): string
    {
        return $this->masterAddress;
    }

    public function getStorageProvisioner(): string
    {
        return $this->storageProvisioner;
    }

    public function getDashboardAddress(): string
    {
        return $this->dashboardAddress;
    }

    public function getCaCertificate(): string
    {
        return $this->caCertificate;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function isUseHnc(): bool
    {
        return $this->useHnc;
    }

    public function prepareSlugNear(
        LoaderInterface $loader,
        FindSlugService $findSlugService,
        string $slugField
    ): SluggableInterface {
        $slugValue = $this->getSlug();
        if (empty($slugValue)) {
            $slugValue = $this->getName();
        }

        $findSlugService->process(
            $loader,
            $slugField,
            $this,
            [
                $slugValue
            ]
        );

        return $this;
    }

    public function setSlug(string $slug): SluggableInterface
    {
        $this->slug = $slug;

        return $this;
    }
}
