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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

/**
 * Class AccountPersistedVariableTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountPersistedVariable::class)]
class AccountPersistedVariableTest extends TestCase
{
    private AccountPersistedVariable $accountPersistedVariable;

    private Account|MockObject $account;

    private string $id;

    private string $name;

    private string $value;

    private string $envName;

    private bool $secret;

    private string $encryptionAlgorithm;

    private bool $needEncryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->id = '42';
        $this->name = '42';
        $this->value = '42';
        $this->envName = '42';
        $this->secret = true;
        $this->encryptionAlgorithm = 'rsa';
        $this->needEncryption = false;
        $this->accountPersistedVariable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            $this->secret,
            $this->encryptionAlgorithm,
            $this->needEncryption,
        );
    }

    public function testGetAccount(): void
    {
        $this->assertInstanceOf(Account::class, $this->accountPersistedVariable->getAccount());
        $this->assertSame($this->account, $this->accountPersistedVariable->getAccount());
    }

    public function testGetName(): void
    {
        $this->assertEquals($this->name, $this->accountPersistedVariable->getName());
    }

    public function testGetValue(): void
    {
        $this->assertEquals($this->value, $this->accountPersistedVariable->getValue());
    }

    public function testGetEnvName(): void
    {
        $this->assertEquals($this->envName, $this->accountPersistedVariable->getEnvName());
    }

    public function testIsSecret(): void
    {
        $this->assertTrue($this->accountPersistedVariable->isSecret());

        $variable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            false,
            $this->encryptionAlgorithm,
            $this->needEncryption,
        );
        $this->assertFalse($variable->isSecret());
    }

    public function testSetValue(): void
    {
        $newValue = 'newValue123';
        $result = $this->accountPersistedVariable->setValue($newValue);

        $this->assertInstanceOf(AccountPersistedVariable::class, $result);
        $this->assertEquals($newValue, $this->accountPersistedVariable->getValue());
    }

    public function testIsEncrypted(): void
    {
        // encryptionAlgorithm='rsa', needEncryption=false -> should be encrypted
        $this->assertTrue($this->accountPersistedVariable->isEncrypted());

        // encryptionAlgorithm=null, needEncryption=false -> not encrypted
        $variable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            $this->secret,
            null,
            false,
        );
        $this->assertFalse($variable->isEncrypted());

        // encryptionAlgorithm='aes256', needEncryption=true -> not yet encrypted
        $variable2 = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            $this->secret,
            'aes256',
            true,
        );
        $this->assertFalse($variable2->isEncrypted());
    }

    public function testMustEncrypt(): void
    {
        $this->assertFalse($this->accountPersistedVariable->mustEncrypt());

        $variable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            $this->secret,
            null,
            true,
        );
        $this->assertTrue($variable->mustEncrypt());
    }

    public function testSetEncryptedValue(): void
    {
        $variable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            $this->secret,
            null,
            true,
        );

        $algo = 'aes256';
        $encryptedValue = 'encrypted_content';
        $result = $variable->setEncryptedValue($algo, $encryptedValue);

        $this->assertInstanceOf(AccountPersistedVariable::class, $result);
        $this->assertEquals($encryptedValue, $variable->getValue());
        $this->assertFalse($variable->mustEncrypt());
        $this->assertTrue($variable->isEncrypted());
    }

    public function testSetEncryptedValueWhenNotNeeded(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This variable 42 not need encryption');

        $this->accountPersistedVariable->setEncryptedValue('aes256', 'encrypted');
    }

    public function testGetContent(): void
    {
        $this->assertEquals($this->value, $this->accountPersistedVariable->getContent());
    }

    public function testGetEncryptionAlgorithm(): void
    {
        $this->assertEquals($this->encryptionAlgorithm, $this->accountPersistedVariable->getEncryptionAlgorithm());

        $variable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            $this->secret,
            null,
            false,
        );
        $this->assertNull($variable->getEncryptionAlgorithm());
    }

    public function testCloneWithEncryption(): void
    {
        $variable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            $this->secret,
            null,
            true,
        );

        $newContent = 'encrypted_content';
        $algo = 'aes256';
        $result = $variable->cloneWith($newContent, $algo);

        $this->assertSame($variable, $result);
        $this->assertEquals($newContent, $variable->getValue());
        $this->assertTrue($variable->isEncrypted());
    }

    public function testCloneWithoutEncryption(): void
    {
        $newContent = 'new_content';
        $result = $this->accountPersistedVariable->cloneWith($newContent, null);

        $this->assertNotSame($this->accountPersistedVariable, $result);
        $this->assertEquals($newContent, $result->getValue());
        $this->assertEquals($this->value, $this->accountPersistedVariable->getValue());
    }

    public function testExportToMeDataWithSecretVariable(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with(
                $this->callback(function ($data) {
                    $this->assertIsArray($data);
                    $this->assertArrayHasKey('name', $data);
                    $this->assertArrayHasKey('secret', $data);
                    $this->assertArrayHasKey('value', $data);
                    $this->assertNull($data['value']);
                    return true;
                })
            );

        $result = $this->accountPersistedVariable->exportToMeData($normalizer, ['groups' => ['crud_variables']]);

        $this->assertInstanceOf(AccountPersistedVariable::class, $result);
    }

    public function testExportToMeDataWithDefaultGroup(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with(
                $this->callback(function ($data) {
                    $this->assertIsArray($data);
                    $this->assertArrayHasKey('@class', $data);
                    $this->assertArrayHasKey('id', $data);
                    // 'name', 'value', etc. are not in default group
                    return true;
                })
            );

        $result = $this->accountPersistedVariable->exportToMeData($normalizer, []);

        $this->assertInstanceOf(AccountPersistedVariable::class, $result);
    }

    public function testExportToMeDataWithNonSecretVariable(): void
    {
        $variable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            false,
            $this->encryptionAlgorithm,
            $this->needEncryption,
        );

        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with(
                $this->callback(function ($data) {
                    $this->assertIsArray($data);
                    $this->assertArrayHasKey('value', $data);
                    $this->assertEquals('42', $data['value']);
                    return true;
                })
            );

        $result = $variable->exportToMeData($normalizer, ['groups' => ['crud_variables']]);

        $this->assertInstanceOf(AccountPersistedVariable::class, $result);
    }

    public function testVerifyAccessToUser(): void
    {
        $user = $this->createMock(User::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->account->expects($this->once())
            ->method('__call')
            ->with('verifyAccessToUser', [$user, $promise]);

        $result = $this->accountPersistedVariable->verifyAccessToUser($user, $promise);

        $this->assertInstanceOf(AccountPersistedVariable::class, $result);
        $this->assertSame($this->accountPersistedVariable, $result);
    }
}
