<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\effect;

final class ParticleType {

    public const HEART          = 'heart';
    public const FLAME          = 'flame';
    public const HAPPY_VILLAGER = 'happy_villager';
    public const ANGRY_VILLAGER = 'angry_villager';
    public const ENCHANT        = 'enchant';
    public const CRITICAL       = 'critical';
    public const SMOKE          = 'smoke';
    public const EXPLODE        = 'explode';
    public const LAVA           = 'lava';
    public const REDSTONE       = 'redstone';
    public const PORTAL         = 'portal';
    public const TELEPORT       = 'teleport';
    public const DUST_RED       = 'dust_red';
    public const DUST_GREEN     = 'dust_green';
    public const DUST_BLUE      = 'dust_blue';
    public const DUST_YELLOW    = 'dust_yellow';
    public const DUST_PURPLE    = 'dust_purple';
    public const DUST_WHITE     = 'dust_white';
    public const DUST_ORANGE    = 'dust_orange';

    public const ALL = [
        self::HEART,
        self::FLAME,
        self::HAPPY_VILLAGER,
        self::ANGRY_VILLAGER,
        self::ENCHANT,
        self::CRITICAL,
        self::SMOKE,
        self::EXPLODE,
        self::LAVA,
        self::REDSTONE,
        self::PORTAL,
        self::TELEPORT,
        self::DUST_RED,
        self::DUST_GREEN,
        self::DUST_BLUE,
        self::DUST_YELLOW,
        self::DUST_PURPLE,
        self::DUST_WHITE,
        self::DUST_ORANGE,
    ];

    public static function isValid(string $type): bool {
        return in_array($type, self::ALL, true);
    }
}
