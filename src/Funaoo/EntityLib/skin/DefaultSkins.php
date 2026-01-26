<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\skin;

use pocketmine\entity\Skin;

/**
 * DefaultSkins - Provides default skins for entities
 *
 * These are blank/transparent skins used when no custom skin is provided.
 */
class DefaultSkins {

    private static ?Skin $blank = null;
    private static ?Skin $steve = null;
    private static ?Skin $alex = null;

    /**
     * Get blank/transparent skin
     */
    public static function blank(): Skin {
        if (self::$blank === null) {
            $skinData = str_repeat("\x00", 8192);
            self::$blank = new Skin("Standard_Custom", $skinData);
        }

        return self::$blank;
    }

    /**
     * Get Steve skin
     */
    public static function steve(): Skin {
        if (self::$steve === null) {
            self::$steve = self::blank();
        }

        return self::$steve;
    }

    /**
     * Get Alex skin
     */
    public static function alex(): Skin {
        if (self::$alex === null) {
            self::$alex = self::blank();
        }

        return self::$alex;
    }

    /**
     * Get default skin for entity type
     */
    public static function forType(string $type): Skin {
        return match($type) {
            'human' => self::steve(),
            'floating_text' => self::blank(),
            'animal', 'mob', 'villager' => self::blank(),
            default => self::blank()
        };
    }
}