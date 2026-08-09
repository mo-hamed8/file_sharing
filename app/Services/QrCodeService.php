<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Thin wrapper around endroid/qr-code so controllers never touch the
 * underlying library directly — keeps room for future changes (SVG
 * output, logo overlay) isolated to this one class.
 */
class QrCodeService
{
    public function generate(string $url): string
    {
        $result = (new Builder(
            writer: new PngWriter,
            data: $url,
            size: 320,
            margin: 12,
        ))->build();

        return $result->getString();
    }
}
