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

namespace Teknoo\Space\Tests\Behat\Traits;

use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Job;
use Teknoo\East\Paas\Object\Job as JobOrigin;
use Teknoo\Space\Service\PersistedVariableEncryption;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait WorkerTrait
{
    /**
     * @Then Space executes the job
     */
    public function spaceExecutesTheJob(): void
    {
        if ($this->clearJobMemory) {
            unset($this->workMemory[JobOrigin::class]);
            unset($this->workMemory[Job::class]);
        }

        $service = $this->sfContainer->get(PersistedVariableEncryption::class);
        $service->setAgentMode(true);

        $newJobTransport = $this->testTransport->get('new_job');
        $executeJobTransport = $this->testTransport->get('execute_job');
        $historySentTransport = $this->testTransport->get('history_sent');
        $jobDoneTransport = $this->testTransport->get('job_done');

        $newJobTransport->process();
        $executeJobTransport->process();
        $historySentTransport->process();
        $jobDoneTransport->process();

        $service->setAgentMode(false);
    }
}
