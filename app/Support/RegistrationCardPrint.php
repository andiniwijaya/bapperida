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

    /** Landscape card width/height in millimetres. */
    public const CARD_WIDTH_MM = 163;

    public const CARD_HEIGHT_MM = 103;

    /** Landscape card 163mm × 103mm in PDF points (1pt = 1/72 inch). */
    public const CARD_PAPER = [0, 0, 462.047, 292.064];

    /** Printed content inset from paper edge (matches physical card). */
    public const CARD_MARGIN_MM = 4;

    public const CARD_SIDEBAR_WIDTH_MM = 13;

    public const CARD_SIDEBAR_GAP_MM = 2;

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

    public static function cardInnerWidthMm(): int
    {
        return self::CARD_WIDTH_MM - (2 * self::CARD_MARGIN_MM);
    }

    public static function cardInnerHeightMm(): int
    {
        return self::CARD_HEIGHT_MM - (2 * self::CARD_MARGIN_MM);
    }

    public static function cardGridWidthMm(bool $isTemplate): int
    {
        if (! $isTemplate) {
            return self::cardInnerWidthMm();
        }

        return self::cardInnerWidthMm() - self::CARD_SIDEBAR_WIDTH_MM - self::CARD_SIDEBAR_GAP_MM;
    }
}
