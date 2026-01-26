<?php

/**
 * InteractionType - Different ways players can interact with NPCs
 *
 * Originally I wanted to support different click types like left-click,
 * right-click, sneak-click, etc. But PM5's entity interaction system
 * is pretty limited, so this is more for future expansion.
 *
 * For now, we just detect basic taps/attacks on entities.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\interaction;

/**
 * Enum-like class for interaction types
 *
 * This helps categorize different ways players can interact with NPCs.
 * Currently PM5 only gives us basic attack events, but we can detect
 * some patterns.
 */
class InteractionType {

    /** Basic tap/click on entity */
    public const TAP = "tap";

    /** Long press (tap and hold) - future feature */
    public const LONG_PRESS = "long_press";

    /** Sneak + tap - future feature */
    public const SNEAK_TAP = "sneak_tap";

    /** Left click (attack) */
    public const LEFT_CLICK = "left_click";

    /** Right click (interact) - future feature */
    public const RIGHT_CLICK = "right_click";

    /**
     * All available interaction types
     */
    public const ALL = [
        self::TAP,
        self::LONG_PRESS,
        self::SNEAK_TAP,
        self::LEFT_CLICK,
        self::RIGHT_CLICK
    ];

    /**
     * Check if an interaction type is valid
     *
     * @param string $type The type to check
     * @return bool True if valid
     */
    public static function isValid(string $type): bool {
        return in_array($type, self::ALL, true);
    }

    /**
     * Get default interaction type
     *
     * @return string Default type
     */
    public static function getDefault(): string {
        return self::TAP;
    }

    /**
     * Get display name for interaction type
     *
     * @param string $type The interaction type
     * @return string Human-readable name
     */
    public static function getDisplayName(string $type): string {
        return match($type) {
            self::TAP => "Tap",
            self::LONG_PRESS => "Long Press",
            self::SNEAK_TAP => "Sneak + Tap",
            self::LEFT_CLICK => "Left Click",
            self::RIGHT_CLICK => "Right Click",
            default => "Unknown"
        };
    }
}