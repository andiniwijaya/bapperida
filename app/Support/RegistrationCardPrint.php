<?php

namespace App\Support;

/**
 * Options for Kartu Surat Keluar print/PDF output.
 */
final class RegistrationCardPrint
{
    public const LAYOUT_TEMPLATE = 'template';

    public const LAYOUT_DATA = 'data';

    public const BACKGROUND_WHITE = 'white';

    public const BACKGROUND_YELLOW = 'yellow';

    public const BACKGROUND_PINK = 'pink';

    /** Landscape card: 163mm × 103mm in points. */
    public const CARD_WIDTH_MM = 163;

    public const CARD_HEIGHT_MM = 103;

    public const CARD_PAPER = [0, 0, 461.386, 291.969];

    /**
     * @return array<string, string>
     */
    public static function layoutOptions(): array
    {
        return [
            self::LAYOUT_TEMPLATE => 'Dengan Template',
            self::LAYOUT_DATA => 'Isi Saja',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function backgroundOptions(): array
    {
        return [
            self::BACKGROUND_WHITE => 'Putih',
            self::BACKGROUND_YELLOW => 'Kuning Soft',
            self::BACKGROUND_PINK => 'Soft Pink',
        ];
    }

    public static function resolveLayout(?string $layout): string
    {
        return array_key_exists($layout ?? '', self::layoutOptions())
            ? $layout
            : self::LAYOUT_TEMPLATE;
    }

    public static function resolveBackground(?string $background): string
    {
        return array_key_exists($background ?? '', self::backgroundOptions())
            ? $background
            : self::BACKGROUND_YELLOW;
    }

    public static function isTemplateLayout(string $layout): bool
    {
        return $layout === self::LAYOUT_TEMPLATE;
    }

    public static function backgroundColor(string $background, string $layout): string
    {
        if (! self::isTemplateLayout($layout)) {
            return '#ffffff';
        }

        return match ($background) {
            self::BACKGROUND_YELLOW => '#fff9db',
            self::BACKGROUND_PINK => '#fce7f3',
            default => '#ffffff',
        };
    }

    /**
     * @return array{0: int, 1: int, 2: float, 3: float}
     */
    public static function cardPaperSize(): array
    {
        return self::CARD_PAPER;
    }
}
