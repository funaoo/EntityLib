<?php

/**
 * ParticleType - All available particle types in one place
 *
 * This is basically a reference/enum class that lists all the particle
 * types we support. Makes it easier to remember what's available.
 *
 * I organized them by category so it's easier to find what you need.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\effect;

/**
 * Particle type constants and utilities
 *
 * Use these constants when adding particles to entities.
 * Much cleaner than typing string literals everywhere.
 */
class ParticleType {

    // === POSITIVE EFFECTS ===

    /** Heart particles - love/healing effect */
    public const HEART = "heart";

    /** Green sparkles - happiness/success */
    public const HAPPY_VILLAGER = "happy_villager";

    /** Enchanting table effect - magic/power */
    public const ENCHANT = "enchant";

    /** Critical hit sparkles - strength/power */
    public const CRITICAL = "critical";


    // === NEGATIVE EFFECTS ===

    /** Red exclamation mark - anger/warning */
    public const ANGRY_VILLAGER = "angry_villager";

    /** Smoke - confusion/damage */
    public const SMOKE = "smoke";

    /** Explosion particles - danger */
    public const EXPLODE = "explode";


    // === ELEMENTAL EFFECTS ===

    /** Fire particles - heat/burning */
    public const FLAME = "flame";

    /** Lava drips - intense heat */
    public const LAVA = "lava";

    /** Water drips - water/cooling */
    public const DRIP_WATER = "drip_water";


    // === MAGICAL EFFECTS ===

    /** Portal swirls - mystical/teleportation */
    public const PORTAL = "portal";

    /** Enderman teleport - warping */
    public const TELEPORT = "teleport";

    /** Redstone dust - tech/circuits */
    public const REDSTONE = "redstone";


    // === COLORED DUST ===

    /** Red dust particles */
    public const DUST_RED = "dust_red";

    /** Green dust particles */
    public const DUST_GREEN = "dust_green";

    /** Blue dust particles */
    public const DUST_BLUE = "dust_blue";

    /** Yellow dust particles */
    public const DUST_YELLOW = "dust_yellow";

    /** Purple dust particles */
    public const DUST_PURPLE = "dust_purple";

    /** White dust particles */
    public const DUST_WHITE = "dust_white";

    /** Orange dust particles */
    public const DUST_ORANGE = "dust_orange";


    /**
     * All available particle types
     */
    public const ALL = [
        // Positive
        self::HEART,
        self::HAPPY_VILLAGER,
        self::ENCHANT,
        self::CRITICAL,

        // Negative
        self::ANGRY_VILLAGER,
        self::SMOKE,
        self::EXPLODE,

        // Elemental
        self::FLAME,
        self::LAVA,
        self::DRIP_WATER,

        // Magical
        self::PORTAL,
        self::TELEPORT,
        self::REDSTONE,

        // Colored
        self::DUST_RED,
        self::DUST_GREEN,
        self::DUST_BLUE,
        self::DUST_YELLOW,
        self::DUST_PURPLE,
        self::DUST_WHITE,
        self::DUST_ORANGE
    ];

    /**
     * Particle types organized by category
     */
    public const CATEGORIES = [
        'positive' => [
            self::HEART,
            self::HAPPY_VILLAGER,
            self::ENCHANT,
            self::CRITICAL
        ],
        'negative' => [
            self::ANGRY_VILLAGER,
            self::SMOKE,
            self::EXPLODE
        ],
        'elemental' => [
            self::FLAME,
            self::LAVA,
            self::DRIP_WATER
        ],
        'magical' => [
            self::PORTAL,
            self::TELEPORT,
            self::REDSTONE
        ],
        'colored' => [
            self::DUST_RED,
            self::DUST_GREEN,
            self::DUST_BLUE,
            self::DUST_YELLOW,
            self::DUST_PURPLE,
            self::DUST_WHITE,
            self::DUST_ORANGE
        ]
    ];

    /**
     * Check if a particle type is valid
     *
     * @param string $type Particle type to check
     * @return bool True if valid
     */
    public static function isValid(string $type): bool {
        return in_array($type, self::ALL, true);
    }

    /**
     * Get particles by category
     *
     * @param string $category Category name
     * @return string[] Array of particle types
     */
    public static function getByCategory(string $category): array {
        return self::CATEGORIES[$category] ?? [];
    }

    /**
     * Get all categories
     *
     * @return string[] Category names
     */
    public static function getCategories(): array {
        return array_keys(self::CATEGORIES);
    }

    /**
     * Get random particle type
     *
     * @param string|null $category Optional category to pick from
     * @return string Random particle type
     */
    public static function getRandom(?string $category = null): string {
        if ($category !== null && isset(self::CATEGORIES[$category])) {
            $particles = self::CATEGORIES[$category];
            return $particles[array_rand($particles)];
        }

        return self::ALL[array_rand(self::ALL)];
    }

    /**
     * Get particle display name
     *
     * @param string $type Particle type
     * @return string Human-readable name
     */
    public static function getDisplayName(string $type): string {
        return match($type) {
            self::HEART => "Heart",
            self::HAPPY_VILLAGER => "Happy Villager",
            self::ANGRY_VILLAGER => "Angry Villager",
            self::ENCHANT => "Enchantment",
            self::CRITICAL => "Critical Hit",
            self::SMOKE => "Smoke",
            self::EXPLODE => "Explosion",
            self::FLAME => "Flame",
            self::LAVA => "Lava",
            self::DRIP_WATER => "Water Drip",
            self::PORTAL => "Portal",
            self::TELEPORT => "Teleport",
            self::REDSTONE => "Redstone",
            self::DUST_RED => "Red Dust",
            self::DUST_GREEN => "Green Dust",
            self::DUST_BLUE => "Blue Dust",
            self::DUST_YELLOW => "Yellow Dust",
            self::DUST_PURPLE => "Purple Dust",
            self::DUST_WHITE => "White Dust",
            self::DUST_ORANGE => "Orange Dust",
            default => "Unknown"
        };
    }

    /**
     * Get suggested use cases for a particle
     *
     * @param string $type Particle type
     * @return string Description of use cases
     */
    public static function getSuggestedUse(string $type): string {
        return match($type) {
            self::HEART => "Love, healing, friendly NPCs",
            self::HAPPY_VILLAGER => "Shops, success, positive feedback",
            self::ANGRY_VILLAGER => "Guards, warnings, denied access",
            self::ENCHANT => "Magic shops, quest givers, mystical NPCs",
            self::CRITICAL => "Combat NPCs, training dummies",
            self::SMOKE => "Damaged NPCs, fire effects",
            self::EXPLODE => "Danger zones, explosive NPCs",
            self::FLAME => "Fire NPCs, hot zones, forges",
            self::LAVA => "Nether NPCs, extreme heat",
            self::DRIP_WATER => "Water zones, rain effects",
            self::PORTAL => "Teleporters, mystical portals",
            self::TELEPORT => "Teleportation NPCs, warpers",
            self::REDSTONE => "Tech NPCs, machinery",
            self::DUST_RED => "Ruby shops, blood effects, danger",
            self::DUST_GREEN => "Emerald shops, nature, poison",
            self::DUST_BLUE => "Diamond shops, ice, water",
            self::DUST_YELLOW => "Gold shops, light, electricity",
            self::DUST_PURPLE => "Amethyst, magic, mystery",
            self::DUST_WHITE => "Snow, purity, holiness",
            self::DUST_ORANGE => "Fire, energy, enthusiasm",
            default => "General decoration"
        };
    }
}