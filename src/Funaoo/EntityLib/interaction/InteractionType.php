<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\interaction;

final class InteractionType {

    public const TAP         = 'tap';
    public const LEFT_CLICK  = 'left_click';
    public const SNEAK_TAP   = 'sneak_tap';

    public const ALL = [self::TAP, self::LEFT_CLICK, self::SNEAK_TAP];

    public static function isValid(string $type): bool {
        return in_array($type, self::ALL, true);
    }
}
