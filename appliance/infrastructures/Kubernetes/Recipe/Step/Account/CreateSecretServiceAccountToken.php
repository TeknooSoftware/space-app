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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account;

use DateTimeInterface;
use RuntimeException;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Kubernetes\Model\Model;
use Teknoo\Kubernetes\Model\Secret;
use Teknoo\Kubernetes\Repository\SecretRepository;
use Teknoo\Space\Object\Persisted\AccountHistory;

use function base64_decode;
use function usleep;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateSecretServiceAccountToken
{
    private const SECRET_SUFFIX = '-secret';

    public function __construct(
        private KubernetesClient $client,
        private DatesService $datesService,
        private int $secretWaitingTime = 1000,
        private bool $prefereRealDate = false,
    ) {
    }

    private function createSecret(string $name, string $namespace, string $serviceAccountName): Secret
    {
        return new Secret([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                ],
                'annotations' => [
                    'kubernetes.io/service-account.name' => $serviceAccountName,
                ],
            ],
            'type' => 'kubernetes.io/service-account-token',
        ]);
    }

    private function fetchSecret(SecretRepository $secretRepository, string $secretName): ?Model
    {
        return $secretRepository->setLabelSelector([
            'name' => $secretName,
        ])->first();
    }

    public function __invoke(
        ManagerInterface $manager,
        string $kubeNamespace,
        string $accountNamespace,
        string $serviceName,
        AccountHistory $accountHistory
    ): self {
        $this->client->setNamespace($kubeNamespace);

        $secretName = $accountNamespace . self::SECRET_SUFFIX;
        $secret = $this->createSecret($secretName, $kubeNamespace, $serviceName);
        $secretRepository = $this->client->secrets();
        if (!$secretRepository->exists((string) $secret->getMetadata('name'))) {
            $secretRepository->apply($secret);
        }

        $counter = 0;
        do {
            if ($counter < 50) {
                usleep($this->secretWaitingTime);
            }

            $secretFetched = $this->fetchSecret($secretRepository, (string) $secret->getMetadata('name'));
            $counter++;
        } while (
            $counter < 50
            && (
                !$secretFetched instanceof Secret
                || empty($secretFetched->toArray()['data'])
            )
        );

        if (!$secretFetched instanceof Secret) {
            $manager->error(new RuntimeException(
                message: 'teknoo.space.error.kubernetes.acccount.secret.not_available',
                code: 404
            ));

            return $this;
        }

        /** @var array{"data": array{"ca.crt":string, "token":string}} $attributes */
        $attributes = $secretFetched->toArray();
        $workPlan['token'] = base64_decode($attributes['data']['token']);
        $workPlan['caCertificate'] = base64_decode($attributes['data']['ca.crt']);

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.secret',
                    $dateTime
                );
            },
            $this->prefereRealDate,
        );

        $manager->updateWorkPlan($workPlan);

        return $this;
    }
}
