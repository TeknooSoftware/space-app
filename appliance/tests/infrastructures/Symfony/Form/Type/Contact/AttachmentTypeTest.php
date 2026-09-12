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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\Contact;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Contact\AttachmentType;
use Teknoo\Space\Object\DTO\ContactAttachment;

/**
 * Class AttachmentTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AttachmentType::class)]
class AttachmentTypeTest extends TestCase
{
    private AttachmentType $attachmentType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->attachmentType = new AttachmentType();
    }

    public function testBuildForm(): void
    {
        $this->attachmentType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            [],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->attachmentType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }

    public function testBuildFormPostSubmitListener(): void
    {
        $listeners = [];
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('add')->willReturnSelf();
        $builder->method('addEventListener')
            ->willReturnCallback(
                function (string $eventName, callable $listener) use (&$listeners, $builder): FormBuilderInterface {
                    $listeners[$eventName][] = $listener;

                    return $builder;
                }
            );

        $this->attachmentType->buildForm($builder, []);

        $this->assertCount(1, $listeners[FormEvents::POST_SUBMIT]);
        $listener = $listeners[FormEvents::POST_SUBMIT][0];

        $attachment = new ContactAttachment();

        $form = $this->createStub(FormInterface::class);
        $form->method('isValid')->willReturn(false);
        $listener(new FormEvent($form, $attachment));
        $this->assertSame('', $attachment->fileName);

        $fileForm = $this->createStub(FormInterface::class);
        $fileForm->method('getData')->willReturn(null);
        $form = $this->createStub(FormInterface::class);
        $form->method('isValid')->willReturn(true);
        $form->method('get')->willReturn($fileForm);
        $listener(new FormEvent($form, $attachment));
        $this->assertSame('', $attachment->fileName);

        $fileForm = $this->createStub(FormInterface::class);
        $fileForm->method('getData')->willReturn(new UploadedFile(__FILE__, 'test.php', 'text/plain', null, true));
        $form = $this->createStub(FormInterface::class);
        $form->method('isValid')->willReturn(true);
        $form->method('get')->willReturn($fileForm);
        $listener(new FormEvent($form, $attachment));
        $this->assertSame('test.php', $attachment->fileName);
        $this->assertNotEmpty($attachment->mimeType);
        $this->assertGreaterThan(0, $attachment->fileLength);
        $this->assertStringContainsString('AttachmentTypeTest', $attachment->fileContent);
    }
}
