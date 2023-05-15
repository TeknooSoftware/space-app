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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Symfony\Form\DataMapper;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\JobVarsSet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\PersistedVariable;
use Traversable;

use function array_values;
use function iterator_to_array;
use function ksort;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractVarsMapper implements DataMapperInterface
{
    /**
     * @param SpaceAccount|SpaceProject|null $data
     * @param Traversable<string, FormInterface> $forms
     */
    public function mapDataToForms($data, $forms): self
    {
        if (!$data instanceof SpaceAccount && !$data instanceof SpaceProject) {
            return $this;
        }

        $environments = [];
        $varsSet = [];
        foreach ($data->variables as $variable) {
            $environmentName = $variable->getEnvironmentName();
            if (!isset($environments[$environmentName])) {
                $environments[$environmentName] = new JobVarsSet($environmentName);
            }

            $value = '';
            if (!($isSecret = $variable->isSecret())) {
                $value = $variable->getValue();
            }

            $vName = $variable->getName();
            $varsSet[$environmentName][$vName] = new JobVar(
                id: $variable->getId(),
                name: $vName,
                value: $value,
                persisted: true,
                secret: $isSecret,
                wasSecret: $isSecret,
                persistedVar: $variable,
            );
        }

        foreach ($varsSet as $envName => $vars) {
            ksort($vars);
            $environments[$envName]->variables = array_values($vars);
        }

        $formArray = iterator_to_array($forms);
        $formArray['sets']->setData($environments);

        return $this;
    }

    abstract protected function buildVariable(
        mixed $parent,
        ?string $id,
        string $name,
        ?string $value,
        string $environmentName,
        bool $secret,
    ): AccountPersistedVariable|PersistedVariable;

    /**
     * @param SpaceAccount|SpaceProject|null $data
     */
    public function mapFormsToData($forms, &$data): self
    {
        if (!$data instanceof SpaceAccount && !$data instanceof SpaceProject) {
            return $this;
        }

        $existentVariables = [];
        foreach ($data->variables as $variable) {
            $existentVariables[$variable->getId()] = $variable->getValue();
        }

        $variables = [];
        /** @var array{sets: FormInterface} $formArray */
        $formArray = iterator_to_array($forms);
        foreach ($formArray['sets']->getData() as $set) {
            /** @var JobVarsSet $set */
            $environmentName = $set->environmentName;
            foreach ($set->variables as $variable) {
                $value = $variable->value;
                if (
                    empty($value)
                    && (true === $variable->secret || true === $variable->wasSecret)
                    && !empty($variable->getId())
                    && isset($existentVariables[$variable->getId()])
                ) {
                    $value = $existentVariables[$variable->getId()];
                }

                $variables[] = $this->buildVariable(
                    parent: $data,
                    id: $variable->getId(),
                    name: $variable->name,
                    value: $value,
                    environmentName: $environmentName,
                    secret: $variable->secret,
                );

                if ($variable->secret) {
                    $variable->value = '';
                } else {
                    $variable->value = $value;
                }
            }
        }

        $data->variables = $variables;

        return $this;
    }
}
