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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Security\Voter;
namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Command\Extension;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\Space\Infrastructures\Symfony\Command\Extension\EnableCommand;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function unlink;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(EnableCommand::class)]
class EnableCommandTest extends TestCase
{
    private const string ENABLED_FILE = 'var/tests/extension-enable.json';

    private EnableCommand $enableCommand;

    private string $spacePath;

    private mixed $oldEnvValue = null;

    private mixed $oldDisabledValue = null;

    private mixed $oldLoaderValue = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->oldEnvValue = $_ENV['TEKNOO_EAST_EXTENSION_FILE'] ?? null;
        $this->oldDisabledValue = $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] ?? null;
        $this->oldLoaderValue = $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] ?? null;
        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = self::ENABLED_FILE;
        unset($_ENV['TEKNOO_EAST_EXTENSION_DISABLED'], $_ENV['TEKNOO_EAST_EXTENSION_LOADER']);

        $this->spacePath = __DIR__ . '/../../../../../';
        if (!is_dir($this->spacePath . 'var/tests')) {
            mkdir($this->spacePath . 'var/tests', 0777, true);
        }
        file_put_contents($this->spacePath . self::ENABLED_FILE, '[]');

        $this->enableCommand = new EnableCommand($this->spacePath);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->spacePath . self::ENABLED_FILE)) {
            unlink($this->spacePath . self::ENABLED_FILE);
        }

        $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = $this->oldEnvValue;
        $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = $this->oldDisabledValue;
        $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = $this->oldLoaderValue;
        parent::tearDown();
    }

    private function createInput(mixed $extension): InputInterface
    {
        $input = $this->createStub(InputInterface::class);
        $input->method('getArgument')->willReturn($extension);

        return $input;
    }

    private function createApplicationWithWarmup(int $returnCode): Application
    {
        $application = new Application();
        $application->addCommand(
            new class ($returnCode) extends Command {
                public function __construct(
                    private readonly int $returnCode,
                ) {
                    parent::__construct('cache:warmup');
                }

                protected function execute(InputInterface $input, OutputInterface $output): int
                {
                    return $this->returnCode;
                }
            },
        );

        return $application;
    }

    public function testGetName(): void
    {
        $this->assertEquals('teknoo:space:extension:enable', $this->enableCommand->getName());
    }

    public function testExecuteWhenExtensionsAreDisabled(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = '1';
        $output = new BufferedOutput();

        $this->assertSame(
            Command::FAILURE,
            $this->enableCommand->run($this->createInput('Enterprise'), $output),
        );
        $this->assertStringContainsString('Extensions are disabled.', $output->fetch());
    }

    public function testExecuteWithAnotherLoader(): void
    {
        $_ENV['TEKNOO_EAST_EXTENSION_LOADER'] = 'Other';
        $output = new BufferedOutput();

        $this->assertSame(
            Command::FAILURE,
            $this->enableCommand->run($this->createInput('Enterprise'), $output),
        );
        $this->assertStringContainsString('only available with the FileLoader', $output->fetch());
    }

    public function testExecuteWithInvalidExtensionName(): void
    {
        $output = new BufferedOutput();

        $this->assertSame(
            Command::INVALID,
            $this->enableCommand->run($this->createInput(null), $output),
        );
        $this->assertStringContainsString('The Extension name is invalid.', $output->fetch());
    }

    public function testExecuteWithUnknownExtension(): void
    {
        $output = new BufferedOutput();

        $this->assertSame(
            Command::INVALID,
            $this->enableCommand->run($this->createInput('Unknown'), $output),
        );
        $this->assertStringContainsString('Extension Unknown is not available.', $output->fetch());
    }

    public function testExecuteWithEnterpriseAndCacheWarmup(): void
    {
        $this->enableCommand->setApplication($this->createApplicationWithWarmup(Command::SUCCESS));
        $output = new BufferedOutput();

        $this->assertSame(
            Command::SUCCESS,
            $this->enableCommand->run($this->createInput('Enterprise'), $output),
        );
        $content = $output->fetch();
        $this->assertStringContainsString('Extension Enterprise is enabled.', $content);
        $this->assertStringContainsString('Cache warmup successful', $content);
        $this->assertSame(
            '["Teknoo\\\\Space\\\\Extensions\\\\Enterprise\\\\Extension"]',
            file_get_contents($this->spacePath . self::ENABLED_FILE),
        );
    }

    public function testExecuteWithEnterpriseWhenCacheWarmupFails(): void
    {
        $this->enableCommand->setApplication($this->createApplicationWithWarmup(Command::FAILURE));
        $output = new BufferedOutput();

        $this->assertSame(
            Command::SUCCESS,
            $this->enableCommand->run($this->createInput('Enterprise'), $output),
        );
        $content = $output->fetch();
        $this->assertStringContainsString('Extension Enterprise is enabled.', $content);
        $this->assertStringContainsString('Error during cache warmup', $content);
    }

    public function testExecuteWithEnterpriseWithoutApplication(): void
    {
        $output = new BufferedOutput();

        $this->assertSame(
            Command::SUCCESS,
            $this->enableCommand->run($this->createInput('Enterprise'), $output),
        );
        $this->assertStringContainsString('Error during cache warmup', $output->fetch());
    }

    public function testExecuteWhenFileNameBecomesInvalidBeforeWriting(): void
    {
        // The file name is validated when the enabled list is read, so the only way to reach the
        // validation performed before writing is to alter the environment between the two operations.
        $input = $this->createStub(InputInterface::class);
        $input->method('getArgument')->willReturnCallback(
            static function (): string {
                $_ENV['TEKNOO_EAST_EXTENSION_FILE'] = ['a'];

                return 'Enterprise';
            },
        );

        $this->expectException(InvalidArgumentException::class);
        $this->enableCommand->run($input, new BufferedOutput());
    }
}
