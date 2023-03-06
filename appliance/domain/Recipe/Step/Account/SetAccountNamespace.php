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

namespace Teknoo\Space\Recipe\Step\Account;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Object\Account;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SetAccountNamespace
{
    public function __construct(
        private FindSlugService $findSlugService,
        private AccountLoader $accountLoader,
    ) {
    }

    /**
     * @return SluggableInterface<Account>
     */
    private function createSluggableObject(
        ManagerInterface $manager,
        Account $accountInstance
    ): SluggableInterface {
        return new class ($accountInstance, $manager) implements SluggableInterface, IdentifiedObjectInterface {
            public function __construct(
                private Account $account,
                private ManagerInterface $manager,
            ) {
            }

            public function getId(): string
            {
                return $this->account->getId();
            }

            public function prepareSlugNear(
                LoaderInterface $loader,
                FindSlugService $findSlugService,
                string $slugField
            ): SluggableInterface {
                return $this;
            }

            public function setSlug(string $slug): SluggableInterface
            {
                $this->account->setNamespace($slug);
                $this->manager->updateWorkPlan(['accountNamespace' => $slug]);

                return $this;
            }
        };
    }

    public function __invoke(
        ManagerInterface $manager,
        Account $accountInstance,
    ): self {
        $sluggable = $this->createSluggableObject($manager, $accountInstance);

        $accountNamespace = (string) $accountInstance;
        $accountInstance->namespaceIsItDefined(
            function ($ns) use (&$accountNamespace) {
                $accountNamespace = $ns;
            }
        );

        $this->findSlugService->process(
            $this->accountLoader,
            'namespace',
            $sluggable,
            [
                $accountNamespace,
            ]
        );

        return $this;
    }
}
