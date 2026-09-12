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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Doctrine\Form\UserData;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\East\Common\Object\MediaMetadata;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Form\DataMapper\EastDataMapper;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Doctrine\Form\UserData\UserDataType;
use Teknoo\Space\Object\Persisted\UserData;

/**
 * Class UserDataTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UserDataType::class)]
class UserDataTypeTest extends TestCase
{
    private UserDataType $userDataType;

    private MediaWriter&Stub $mediaWriter;

    private EastDataMapper&Stub $eastDataMapper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaWriter = $this->createStub(MediaWriter::class);
        $this->eastDataMapper = $this->createStub(EastDataMapper::class);

        $this->userDataType = new UserDataType(
            $this->mediaWriter,
            $this->eastDataMapper
        );
    }

    public function testBuildForm(): void
    {
        $this->userDataType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['environmentsList' => ['bar']],
        );
        $this->assertTrue(true);
    }

    /**
     * @return array<string, array<callable>>
     */
    private function captureListeners(UserDataType $type): array
    {
        $listeners = [];
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('addEventListener')
            ->willReturnCallback(
                function (string $event, callable $listener) use (&$listeners, $builder) {
                    $listeners[$event][] = $listener;

                    return $builder;
                }
            );

        $type->buildForm($builder, []);

        return $listeners;
    }

    private function buildForm(mixed $normData, bool $removePicture = false): FormInterface&Stub
    {
        $picture = $this->createStub(FormInterface::class);
        $remove = $this->createStub(FormInterface::class);
        $remove->method('getViewData')->willReturn($removePicture);

        $form = $this->createStub(FormInterface::class);
        $form->method('getNormData')->willReturn($normData);
        $form->method('get')
            ->willReturnCallback(
                fn (string $name): FormInterface => match ($name) {
                    'picture' => $picture,
                    'removePicture' => $remove,
                }
            );

        return $form;
    }

    public function testPreSubmitListener(): void
    {
        $listeners = $this->captureListeners($this->userDataType);
        $this->assertCount(1, $listeners[FormEvents::PRE_SUBMIT]);
        $listener = $listeners[FormEvents::PRE_SUBMIT][0];

        $userData = new UserData($this->createStub(User::class));
        $event = new FormEvent($this->buildForm($userData), ['picture' => []]);
        $listener($event);
        $this->assertInstanceOf(Media::class, $userData->getPicture());
        $this->assertSame('profile-picture', $event->getData()['picture']['name']);

        $media = new Media();
        $userData = new UserData($this->createStub(User::class), $media);
        $listener(new FormEvent($this->buildForm($userData), ['picture' => []]));
        $this->assertSame($media, $userData->getPicture());

        $listener(new FormEvent($this->buildForm(null), ['picture' => []]));
    }

    public function testPostSubmitListenerWithoutUserData(): void
    {
        $listeners = $this->captureListeners($this->userDataType);
        $this->assertCount(1, $listeners[FormEvents::POST_SUBMIT]);
        $listener = $listeners[FormEvents::POST_SUBMIT][0];

        $listener(new FormEvent($this->buildForm(null), []));
        $this->assertTrue(true);
    }

    public function testPostSubmitListenerRemovePicture(): void
    {
        $media = (new Media())->setId('media-id');
        $userData = new UserData($this->createStub(User::class), $media);

        $mediaWriter = $this->createMock(MediaWriter::class);
        $mediaWriter->expects($this->once())
            ->method('remove')
            ->with($media)
            ->willReturnSelf();

        $type = new UserDataType($mediaWriter, $this->eastDataMapper);
        $listener = $this->captureListeners($type)[FormEvents::POST_SUBMIT][0];

        $listener(new FormEvent($this->buildForm($userData, true), []));
        $this->assertNull($userData->getPicture());
    }

    public function testPostSubmitListenerWithEmptyMedia(): void
    {
        $userData = new UserData($this->createStub(User::class), new Media());

        $mediaWriter = $this->createMock(MediaWriter::class);
        $mediaWriter->expects($this->never())->method('remove');

        $type = new UserDataType($mediaWriter, $this->eastDataMapper);
        $listener = $this->captureListeners($type)[FormEvents::POST_SUBMIT][0];

        $listener(new FormEvent($this->buildForm($userData), []));
        $this->assertNull($userData->getPicture());
    }

    public function testPostSubmitListenerSaveMedia(): void
    {
        $media = (new Media())->setMetadata(new MediaMetadata('image/png', 'foo.png', 'foo', '/tmp/foo.png'));
        $userData = new UserData($this->createStub(User::class), $media);

        $savedMedia = new Media();
        $mediaWriter = $this->createMock(MediaWriter::class);
        $mediaWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function (Media $m, PromiseInterface $promise) use ($mediaWriter, $savedMedia): MediaWriter {
                    $promise->success($savedMedia);

                    return $mediaWriter;
                }
            );

        $type = new UserDataType($mediaWriter, $this->eastDataMapper);
        $listener = $this->captureListeners($type)[FormEvents::POST_SUBMIT][0];

        $listener(new FormEvent($this->buildForm($userData), []));
        $this->assertSame($savedMedia, $userData->getPicture());
    }

    public function testPostSubmitListenerSaveMediaFailed(): void
    {
        $media = (new Media())->setMetadata(new MediaMetadata('image/png', 'foo.png', 'foo', '/tmp/foo.png'));
        $userData = new UserData($this->createStub(User::class), $media);

        $mediaWriter = $this->createMock(MediaWriter::class);
        $mediaWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function (Media $m, PromiseInterface $promise) use ($mediaWriter): MediaWriter {
                    $promise->fail(new RuntimeException('failed'));

                    return $mediaWriter;
                }
            );

        $type = new UserDataType($mediaWriter, $this->eastDataMapper);
        $listener = $this->captureListeners($type)[FormEvents::POST_SUBMIT][0];

        $picture = $this->createStub(FormInterface::class);
        $remove = $this->createStub(FormInterface::class);
        $remove->method('getViewData')->willReturn(false);

        $form = $this->createMock(FormInterface::class);
        $form->method('getNormData')->willReturn($userData);
        $form->method('get')
            ->willReturnCallback(
                fn (string $name): FormInterface => match ($name) {
                    'picture' => $picture,
                    'removePicture' => $remove,
                }
            );
        $form->expects($this->once())->method('addError')->willReturnSelf();

        $listener(new FormEvent($form, []));
        $this->assertSame($media, $userData->getPicture());
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->userDataType->configureOptions($resolver);

        $this->assertSame(UserData::class, $resolver->resolve([])['data_class']);
    }
}
