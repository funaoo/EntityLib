<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\skin;

use pocketmine\entity\Skin;

final class DefaultSkins {

    private static ?Skin $blank = null;

    public static function blank(): Skin {
        if (self::$blank === null) {
            self::$blank = new Skin('Standard_Custom', str_repeat("\x00", 8192));
        }
        return self::$blank;
    }

    public static function forType(string $type): Skin {
        return self::blank();
    }
}
