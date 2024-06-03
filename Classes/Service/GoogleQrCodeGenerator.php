<?php
declare(strict_types=1);

/**
 * Class GoogleQrImageGenerator
 *
 * @author        Robin 'codeFareith' von den Bergen <robinvonberg@gmx.de>
 * @copyright (c) 2018-2019 by Robin von den Bergen
 * @license       http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace Causal\MfaFrontend\Service;

use Causal\MfaFrontend\Domain\Immutable\TotpSecret;

/**
 * QR code image generator
 *
 * This class uses Scrippter chart API to generate a QR code image.
 * This QR code can then be captured with the phone camera to
 * automatically set up the service in the Google Authenticator app.
 */
class GoogleQrCodeGenerator
{
    public const
        CORRECTION_L = 'L',     // recover  7% data loss
        CORRECTION_M = 'M',     // recover 15% data loss
        CORRECTION_Q = 'Q',     // recover 25% data loss
        CORRECTION_H = 'H'      // recover 30% data loss
    ;

    protected const BASE_URL = 'https://chart.scrippter.com/';

    protected int $width;

    protected int $height;

    protected string $correction;

    protected int $margin;

    public function __construct(
        ?int $width = null,
        ?int $height = null,
        ?string $correction = null,
        ?int $margin = null
    )
    {
        $this->width = $width ?? 200;
        $this->height = $height ?? 200;
        $this->correction = $correction ?? static::CORRECTION_L;
        $this->margin = $margin ?? 4;
    }

    public function generateUri(TotpSecret $secretImmutable): string
    {
        $data = [
            'chs' => '%sx%s',
            'chld' => '%s|%s',
            'cht' => '%s',
            'chl' => '%s',
        ];
        $query = http_build_query($data);
        $queryDecoded = rawurldecode($query);
        $uriEncoded = rawurlencode($secretImmutable->getUri());

        return vsprintf(
            static::BASE_URL . 'chart?' . $queryDecoded,
            [
                $this->width,
                $this->height,
                $this->correction,
                $this->margin,
                'qr',
                $uriEncoded,
            ]
        );
    }
}
