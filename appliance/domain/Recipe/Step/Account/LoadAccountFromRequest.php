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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Step\Account;

use DomainException;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;

use function in_array;

/**
 * To load from the DB an account instance. Step available only for admin user when the
 * accountId is passed to the request
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadAccountFromRequest
{
    public function __construct(
        private AccountLoader $accountLoader,
    ) {
    }

    private function getAccount(string $accountId): ?Account
    {
        /** @var Promise<Account, ?Account, mixed> $accountPromise */
        $accountPromise = new Promise(
            fn (Account $account) => $account,
        );

        $this->accountLoader->load(
            $accountId,
            $accountPromise
        );

        return $accountPromise->fetchResult();
    }

    /**
     * @param array<string, string> $parameters
     */
    public function __invoke(
        ManagerInterface $manager,
        ?User $user = null,
        ?Account $account = null,
        ?string $accountId = null,
        array $parameters = [],
    ): self {
        if (empty($accountId) || !in_array('ROLE_ADMIN', (array) $user?->getRoles())) {
            return $this;
        }

        $account ??= $this->getAccount($accountId);

        if ($accountId !== $account?->getId()) {
            throw new DomainException(
                message: 'teknoo.space.error.space_account.account.fetching',
                code: 404,
            );
        }

        $parameters['accountId'] = $accountId;

        $manager->updateWorkPlan([
            Account::class => $account,
            'parameters' => $parameters,
        ]);

        return $this;
    }
}
