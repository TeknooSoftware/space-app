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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Twig\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormError as SfFormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\Space\Infrastructures\Twig\Extension\ApiRenderingObjectWithForm;
use Teknoo\Space\Infrastructures\Twig\Extension\FormError;
use Teknoo\Space\Infrastructures\Twig\Extension\ObjectSerializing;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[CoversClass(ApiRenderingObjectWithForm::class)]
class ApiRenderingObjectWithFormTest extends TestCase
{
    private ObjectSerializing $objectSerializing;
    private FormError $formError;
    private ApiRenderingObjectWithForm $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectSerializing = $this->createMock(ObjectSerializing::class);
        $this->formError = $this->createMock(FormError::class);
        $this->extension = new ApiRenderingObjectWithForm(
            objectSerializing: $this->objectSerializing,
            formError: $this->formError,
        );
    }

    public function testRenderingWithErrors(): void
    {
        $formView = new FormView();
        $formInterface = $this->createStub(FormInterface::class);
        $formView->vars['errors'] = new FormErrorIterator($formInterface, [new SfFormError('err')]);

        $this->formError
            ->expects($this->once())
            ->method('getFieldErrors')
            ->with($formView)
            ->willReturn(['.' => 'err']);

        $this->objectSerializing
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->equalTo(['.' => 'err']),
                $this->equalTo([]),
                $this->equalTo('json'),
                $this->equalTo(['errors' => true]),
            )
            ->willReturn('serialized-errors');

        $result = $this->extension->rendering(object: ['foo' => 'bar'], formView: $formView);
        $this->assertSame('serialized-errors', $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRenderingWithoutErrors(): void
    {
        $formView = new FormView();
        // No 'errors' key -> passthrough

        $object = (object)['foo' => 'bar'];
        $context = ['groups' => ['a']];
        $format = 'jsonld';
        $meta = ['custom' => true];
        $parent = $this->createStub(IdentifiedObjectInterface::class);

        $this->objectSerializing
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->identicalTo($object),
                $this->equalTo($context),
                $this->equalTo($format),
                $this->equalTo($meta),
                $this->identicalTo($parent),
            )
            ->willReturn('serialized-object');

        $result = $this->extension->rendering(
            object: $object,
            formView: $formView,
            context: $context,
            format: $format,
            meta: $meta,
            parentObject: $parent,
        );

        $this->assertSame('serialized-object', $result);
    }
}
