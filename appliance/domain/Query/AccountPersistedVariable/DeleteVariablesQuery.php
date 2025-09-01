<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Query\AccountPersistedVariable;

use Teknoo\East\Common\Contracts\DBSource\QueryExecutorInterface;
use Teknoo\East\Common\Contracts\Query\DeletingQueryInterface;
use Teknoo\East\Common\Query\Expr\NotIn;
use Teknoo\East\Common\Query\Expr\ObjectReference;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements DeletingQueryInterface<AccountPersistedVariable>
 */
class DeleteVariablesQuery implements DeletingQueryInterface, ImmutableInterface
{
    use ImmutableTrait;

    private Account $account;

    /**
     * @var array<int, string>
     */
    private array $notIds;

    /**
     * @param array<int, string> $notIds
     */
    public function __construct(Account $account, array $notIds)
    {
        $this->uniqueConstructorCheck();

        $this->account = $account;
        $this->notIds = $notIds;
    }

    public function delete(QueryExecutorInterface $queryBuilder, PromiseInterface $promise): DeletingQueryInterface
    {
        $queryBuilder->filterOn(
            AccountPersistedVariable::class,
            [
                'account' => new ObjectReference($this->account),
                'id' => new NotIn($this->notIds),
            ],
        );
        $queryBuilder->execute($promise);

        return $this;
    }
}
