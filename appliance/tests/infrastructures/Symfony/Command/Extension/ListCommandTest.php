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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Command\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\East\Foundation\Extension\FileLoader;
use Teknoo\Space\Infrastructures\Symfony\Command\Extension\ListCommand;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ListCommand::class)]
class ListCommandTest extends TestCase
{
    private ListCommand $listCommand;

    private mixed $oldEnvValue = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->oldEnvValue = $_ENV['TEKNOO_EAST_EXTENSION_FILE'] ?? null;
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'tests/fixtures/extension/list.json';

        $this->listCommand = new ListCommand(__DIR__ . '/../../../../../');
    }

    protected function tearDown(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = $this->oldEnvValue;
        parent::tearDown();
    }

    public function testGetName(): void
    {
        $this->assertEquals('teknoo:space:extension:list', $this->listCommand->getName());
    }

    public function testExecute(): void
    {
        $this->assertIsInt(
            $this->listCommand->run(
                $this->createStub(InputInterface::class),
                $this->createStub(OutputInterface::class),
            )
        );
    }
}
