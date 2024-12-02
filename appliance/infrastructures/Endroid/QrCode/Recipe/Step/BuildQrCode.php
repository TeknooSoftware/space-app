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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Endroid\QrCode\Recipe\Step;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Laminas\Diactoros\Response;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\CommonBundle\Contracts\Recipe\Step\BuildQrCodeInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author      Richard Déloge <richard@teknoo.software>
 */
class BuildQrCode implements BuildQrCodeInterface
{
    public function __construct(
        private PngWriter $pngWriter,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ClientInterface $client,
        string $qrCodeValue,
    ): BuildQrCodeInterface {
        $qrCode = new QrCode(
            data: $qrCodeValue,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 400,
            margin: 0,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            backgroundColor: new Color(255, 255, 255),
        );

        $result = $this->pngWriter->write(
            qrCode: $qrCode,
        );

        $client->acceptResponse(
            new Response(
                body: $this->streamFactory->createStream($result->getString()),
                status: 200,
                headers: ['Content-Type' => 'image/png']
            )
        );

        return $this;
    }
}
