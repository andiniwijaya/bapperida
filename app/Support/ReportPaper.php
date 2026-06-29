<?php

namespace App\Support;

/**
 * Paper size helpers for DomPDF (points: 1pt = 1/72 inch).
 */
final class ReportPaper
{
    /** F4 portrait: 210mm × 330mm */
    public const F4_PORTRAIT = [0, 0, 595.276, 935.433];

    /**
     * @return array{0: int, 1: int, 2: float, 3: float}
     */
    public static function f4Portrait(): array
    {
        return self::F4_PORTRAIT;
    }
}
