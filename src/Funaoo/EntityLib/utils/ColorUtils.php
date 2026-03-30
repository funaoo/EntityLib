<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\utils;

final class ColorUtils {

    public const BLACK       = "\u{00A7}0";
    public const DARK_BLUE   = "\u{00A7}1";
    public const DARK_GREEN  = "\u{00A7}2";
    public const DARK_AQUA   = "\u{00A7}3";
    public const DARK_RED    = "\u{00A7}4";
    public const DARK_PURPLE = "\u{00A7}5";
    public const GOLD        = "\u{00A7}6";
    public const GRAY        = "\u{00A7}7";
    public const DARK_GRAY   = "\u{00A7}8";
    public const BLUE        = "\u{00A7}9";
    public const GREEN       = "\u{00A7}a";
    public const AQUA        = "\u{00A7}b";
    public const RED         = "\u{00A7}c";
    public const LIGHT_PURPLE = "\u{00A7}d";
    public const YELLOW      = "\u{00A7}e";
    public const WHITE       = "\u{00A7}f";
    public const BOLD        = "\u{00A7}l";
    public const ITALIC      = "\u{00A7}o";
    public const UNDERLINE   = "\u{00A7}n";
    public const RESET       = "\u{00A7}r";

    public static function strip(string $text): string {
        return (string)preg_replace('/\xc2\xa7[0-9a-fk-or]/i', '', $text);
    }

    public static function translate(string $text): string {
        return (string)preg_replace('/&([0-9a-fk-or])/i', "\xc2\xa7$1", $text);
    }

    public static function rainbow(string $text): string {
        $colors = [self::RED, self::GOLD, self::YELLOW, self::GREEN, self::AQUA, self::BLUE, self::LIGHT_PURPLE];
        $result = '';
        $i      = 0;
        foreach (str_split($text) as $char) {
            if ($char === ' ') {
                $result .= $char;
            } else {
                $result .= $colors[$i % count($colors)] . $char;
                $i++;
            }
        }
        return $result;
    }

    public static function progressBar(float $progress, int $length = 20, string $fill = self::GREEN, string $empty = self::GRAY): string {
        $progress = max(0.0, min(1.0, $progress));
        $filled   = (int)($progress * $length);
        return $fill . str_repeat('|', $filled) . $empty . str_repeat('|', $length - $filled);
    }

    public static function colorByPercent(float $pct): string {
        return match(true) {
            $pct >= 75 => self::GREEN,
            $pct >= 50 => self::YELLOW,
            $pct >= 25 => self::GOLD,
            default    => self::RED,
        };
    }
}
