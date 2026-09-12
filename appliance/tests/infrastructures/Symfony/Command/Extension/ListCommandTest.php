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

use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\East\Foundation\Extension\FileLoader;
use Teknoo\Space\Infrastructures\Symfony\Command\Extension\ListCommand;

use function dirname;

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

    private mixed $oldDisabledValue = null;

    private mixed $oldLoaderValue = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Fake extension without the ExtensionInterface, not autoloadable, for the discovery test
        require_once dirname(__DIR__, 4) . '/fixtures/extension/space/extensions/FakeNotAnExtension/Extension.php';

        $this->oldEnvValue = $_ENV['TEKNOO_EAST_EXTENSION_FILE'] ?? null;
        $this->oldDisabledValue = $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] ?? null;
        $this->oldLoaderValue = $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] ?? null;
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'tests/fixtures/extension/list.json';
        unset($_ENV['TEKNOO_EAST_EXTENSION_DISABLED'], $_ENV['TEKNOO_EAST_EXTENSION_LOADER']);

        $this->listCommand = new ListCommand(__DIR__ . '/../../../../../');
    }

    protected function tearDown(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = $this->oldEnvValue;
        $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = $this->oldDisabledValue;
        $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = $this->oldLoaderValue;
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

    public function testExecuteWhenExtensionsAreDisabled(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = '1';
        $output = new BufferedOutput();

        $this->assertSame(
            Command::FAILURE,
            $this->listCommand->run($this->createStub(InputInterface::class), $output),
        );
        $this->assertStringContainsString('Extensions are disabled.', $output->fetch());
    }

    public function testExecuteWithAnotherLoader(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'tests/fixtures/extension/enabled-one.json';
        $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = 'Other';
        $output = new BufferedOutput();

        $this->assertSame(
            Command::SUCCESS,
            $this->listCommand->run($this->createStub(InputInterface::class), $output),
        );
        $content = $output->fetch();
        $this->assertStringContainsString('only enabled extensions can be shown', $content);
        $this->assertStringContainsString('Teknoo\Space\Extensions\Enterprise\Extension', $content);
        $this->assertStringContainsString('Yes', $content);
    }

    public function testExecuteWithFileLoaderListsAvailableExtensions(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'tests/fixtures/extension/enabled-one.json';
        $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = FileLoader::class;
        $output = new BufferedOutput();

        $this->assertSame(
            Command::SUCCESS,
            $this->listCommand->run($this->createStub(InputInterface::class), $output),
        );
        $content = $output->fetch();
        $this->assertStringContainsString('Enterprise', $content);
        $this->assertStringContainsString('Yes', $content);
    }

    public function testExecuteWithNonStringFileName(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = ['a'];

        $this->expectException(InvalidArgumentException::class);
        $this->listCommand->run(
            $this->createStub(InputInterface::class),
            $this->createStub(OutputInterface::class),
        );
    }

    public function testExecuteWithMissingFile(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'tests/fixtures/extension/missing.json';

        $this->expectException(DomainException::class);
        $this->listCommand->run(
            $this->createStub(InputInterface::class),
            $this->createStub(OutputInterface::class),
        );
    }

    public function testExecuteWithNonArrayFile(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'tests/fixtures/extension/not-array.json';
        $output = new BufferedOutput();

        $this->assertSame(
            Command::SUCCESS,
            $this->listCommand->run($this->createStub(InputInterface::class), $output),
        );
        $content = $output->fetch();
        $this->assertStringContainsString('Enterprise', $content);
        $this->assertStringContainsString('No', $content);
    }

    public function testExecuteWithInvalidFile(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'tests/fixtures/extension/invalid.json';

        $this->expectException(DomainException::class);
        $this->listCommand->run(
            $this->createStub(InputInterface::class),
            $this->createStub(OutputInterface::class),
        );
    }

    public function testExecuteIgnoresDirectoriesWithoutValidExtensionClass(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = 'enabled.json';
        $command = new ListCommand(__DIR__ . '/../../../../fixtures/extension/space/');
        $output = new BufferedOutput();

        $this->assertSame(
            Command::SUCCESS,
            $command->run($this->createStub(InputInterface::class), $output),
        );
        $content = $output->fetch();
        $this->assertStringNotContainsString('Ghost', $content);
        $this->assertStringNotContainsString('FakeNotAnExtension', $content);
    }
}
