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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\DTO\JobVarsSet;

/**
 * Class JobVarsSetTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Object\DTO\JobVarsSet
 */
class JobVarsSetTest extends TestCase
{
    private JobVarsSet $jobVarsSet;

    private string $environmentName;

    private array $variables;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->environmentName = '42';
        $this->variables = [];
        $this->jobVarsSet = new JobVarsSet($this->environmentName, $this->variables);
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            JobVarsSet::class,
            $this->jobVarsSet,
        );
    }
}
