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

namespace Teknoo\Space\Infrastructures\Twig\Extension;

use Symfony\Component\Form\FormError as SfFormError;
use Symfony\Component\Form\FormView;
use Twig\Attribute\AsTwigFunction;

use function array_pop;
use function array_reverse;
use function implode;
use function is_iterable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FormError
{
    /**
     * @return iterable<string, string>
     */
    #[AsTwigFunction('space_form_errors')]
    public function getFieldErrors(FormView $view): iterable
    {
        if (empty($view->vars['errors']) || !is_iterable($view->vars['errors'])) {
            return [];
        }

        /** @var SfFormError $error */
        foreach ($view->vars['errors'] as $error) {
            $path = '.';
            $elements = [];
            $form = $error->getOrigin();
            while (null !== $form) {
                $elements[] = (string) $form->getPropertyPath();
                $form = $form->getParent();
            }

            array_pop($elements);
            $elements = array_reverse($elements);
            $path .= implode('.', $elements);

            yield $path => $error->getMessage();
        }
    }
}
