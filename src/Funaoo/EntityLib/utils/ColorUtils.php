<?php

/**
 * ColorUtils - Color code utilities for text formatting
 *
 * Working with Minecraft color codes can get messy. These utilities
 * make it easier to create fancy colored text, gradients, and more.
 *
 * Perfect for making NPCs with cool nametags.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\utils;

/**
 * Color and text formatting utilities
 *
 * Makes working with color codes easier and cleaner.
 */
class ColorUtils {

    /** Minecraft color codes */
    public const BLACK = "§0";
    public const DARK_BLUE = "§1";
    public const DARK_GREEN = "§2";
    public const DARK_AQUA = "§3";
    public const DARK_RED = "§4";
    public const DARK_PURPLE = "§5";
    public const GOLD = "§6";
    public const GRAY = "§7";
    public const DARK_GRAY = "§8";
    public const BLUE = "§9";
    public const GREEN = "§a";
    public const AQUA = "§b";
    public const RED = "§c";
    public const LIGHT_PURPLE = "§d";
    public const YELLOW = "§e";
    public const WHITE = "§f";

    /** Format codes */
    public const OBFUSCATED = "§k";
    public const BOLD = "§l";
    public const STRIKETHROUGH = "§m";
    public const UNDERLINE = "§n";
    public const ITALIC = "§o";
    public const RESET = "§r";

    /**
     * Strip all color codes from text
     *
     * @param string $text Text with color codes
     * @return string Clean text
     */
    public static function stripColors(string $text): string {
        return preg_replace('/§[0-9a-fk-or]/i', '', $text);
    }

    /**
     * Convert & color codes to § codes
     *
     * @param string $text Text with & codes
     * @return string Text with § codes
     */
    public static function translateAlternateColorCodes(string $text): string {
        return preg_replace('/&([0-9a-fk-or])/i', '§$1', $text);
    }

    /**
     * Create rainbow text
     *
     * @param string $text Text to rainbowify
     * @return string Rainbow colored text
     */
    public static function rainbow(string $text): string {
        $colors = [
            self::RED,
            self::GOLD,
            self::YELLOW,
            self::GREEN,
            self::AQUA,
            self::BLUE,
            self::LIGHT_PURPLE
        ];

        $result = "";
        $colorIndex = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];

            if ($char !== ' ') {
                $result .= $colors[$colorIndex % count($colors)] . $char;
                $colorIndex++;
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * Create gradient text between two colors
     *
     * @param string $text Text to gradient
     * @param string $startColor Starting color code
     * @param string $endColor Ending color code
     * @return string Gradient text
     */
    public static function gradient(string $text, string $startColor, string $endColor): string {
        // Simple gradient - just alternates between two colors
        // A true gradient would need RGB interpolation

        $result = "";
        $length = strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            if ($char === ' ') {
                $result .= $char;
                continue;
            }

            $progress = $i / max(1, $length - 1);
            $color = $progress < 0.5 ? $startColor : $endColor;

            $result .= $color . $char;
        }

        return $result;
    }

    /**
     * Create blinking text effect
     *
     * @param string $text Text to blink
     * @param string $color1 First color
     * @param string $color2 Second color
     * @param int $frame Current frame (for animation)
     * @return string Blinking text
     */
    public static function blink(string $text, string $color1, string $color2, int $frame): string {
        $color = ($frame % 2 === 0) ? $color1 : $color2;
        return $color . $text;
    }

    /**
     * Create centered text
     *
     * @param string $text Text to center
     * @param int $width Total width in characters
     * @return string Centered text with spaces
     */
    public static function center(string $text, int $width = 50): string {
        $cleanText = self::stripColors($text);
        $length = strlen($cleanText);

        if ($length >= $width) {
            return $text;
        }

        $padding = ($width - $length) / 2;
        $leftPadding = str_repeat(' ', (int)floor($padding));

        return $leftPadding . $text;
    }

    /**
     * Create a progress bar
     *
     * @param float $progress Progress (0.0 to 1.0)
     * @param int $length Bar length in characters
     * @param string $fillColor Color for filled part
     * @param string $emptyColor Color for empty part
     * @return string Progress bar string
     */
    public static function progressBar(
        float $progress,
        int $length = 20,
        string $fillColor = self::GREEN,
        string $emptyColor = self::GRAY
    ): string {
        $progress = max(0, min(1, $progress));
        $filled = (int)($progress * $length);
        $empty = $length - $filled;

        return $fillColor . str_repeat('|', $filled) .
            $emptyColor . str_repeat('|', $empty);
    }

    /**
     * Get color based on percentage
     *
     * Green = high, Yellow = medium, Red = low
     *
     * @param float $percentage Percentage (0-100)
     * @return string Color code
     */
    public static function getColorByPercentage(float $percentage): string {
        if ($percentage >= 75) {
            return self::GREEN;
        } elseif ($percentage >= 50) {
            return self::YELLOW;
        } elseif ($percentage >= 25) {
            return self::GOLD;
        } else {
            return self::RED;
        }
    }

    /**
     * Create fancy bordered text
     *
     * @param string $text Text to border
     * @param string $borderChar Border character
     * @param string $borderColor Border color
     * @return string Bordered text
     */
    public static function border(
        string $text,
        string $borderChar = '=',
        string $borderColor = self::GOLD
    ): string {
        $cleanText = self::stripColors($text);
        $length = strlen($cleanText);
        $border = $borderColor . str_repeat($borderChar, $length + 4);

        return $border . "\n" .
            $borderColor . $borderChar . " " . $text . " " . $borderChar . "\n" .
            $border;
    }

    /**
     * Format number with color based on value
     *
     * @param float $number The number
     * @param float $good "Good" threshold (green)
     * @param float $okay "Okay" threshold (yellow)
     * @return string Colored number
     */
    public static function colorNumber(float $number, float $good, float $okay): string {
        if ($number >= $good) {
            $color = self::GREEN;
        } elseif ($number >= $okay) {
            $color = self::YELLOW;
        } else {
            $color = self::RED;
        }

        return $color . number_format($number, 2);
    }

    /**
     * Create health bar
     *
     * @param int $current Current health
     * @param int $max Maximum health
     * @param int $hearts Number of hearts to display
     * @return string Health bar with hearts
     */
    public static function healthBar(int $current, int $max, int $hearts = 10): string {
        $percentage = $current / max(1, $max);
        $filledHearts = (int)($percentage * $hearts);
        $emptyHearts = $hearts - $filledHearts;

        return self::RED . str_repeat('❤', $filledHearts) .
            self::DARK_GRAY . str_repeat('❤', $emptyHearts);
    }
}