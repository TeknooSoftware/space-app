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

namespace App\Config;

use Teknoo\East\Paas\Infrastructures\Kubernetes\Transcriber\IngressTranscriber as BaseIngressTranscriber;
use Teknoo\Space\Infrastructures\Kubernetes\Transcriber\IngressTranscriber;

use function DI\env;
use function sys_get_temp_dir;

return [
    //App variables
    'app.hostname' => env('SPACE_HOSTNAME', 'localhost'),
    'app.job_root' => env('SPACE_JOB_ROOT', sys_get_temp_dir()),

    BaseIngressTranscriber::class . ':class' => IngressTranscriber::class,
];
