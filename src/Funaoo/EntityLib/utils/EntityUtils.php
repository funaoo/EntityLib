<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\utils;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\World;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\EntityLib;

final class EntityUtils {

    public static function inRadius(Vector3 $pos, World $world, float $radius, bool $libOnly = true): array {
        $r2 = $radius ** 2;
        return array_values(array_filter(
            $world->getEntities(),
            static fn(Entity $e) => (!$libOnly || $e instanceof BaseEntity)
                && $e->getPosition()->distanceSquared($pos) <= $r2
        ));
    }

    public static function nearest(Vector3 $pos, World $world, float $maxDist = 50.0, bool $libOnly = true): ?Entity {
        $nearest = null;
        $best    = $maxDist ** 2;
        foreach ($world->getEntities() as $e) {
            if ($libOnly && !$e instanceof BaseEntity) continue;
            $d = $e->getPosition()->distanceSquared($pos);
            if ($d < $best) { $best = $d; $nearest = $e; }
        }
        return $nearest;
    }

    public static function teleportWithEffect(BaseEntity $entity, Vector3 $dest, bool $effect = true): void {
        $world    = $entity->getWorld();
        $particle = new EndermanTeleportParticle();

        $scatter = static function(Vector3 $origin) use ($world, $particle): void {
            for ($i = 0; $i < 20; $i++) {
                $world->addParticle($origin->add(mt_rand(-10, 10) / 10, mt_rand(0, 20) / 10, mt_rand(-10, 10) / 10), $particle);
            }
        };

        if ($effect) $scatter($entity->getPosition());
        $entity->teleport($dest);
        if ($effect) $scatter($dest);
    }

    public static function countByType(): array {
        $counts = [];
        foreach (EntityLib::getAll() as $e) {
            $counts[$e->getType()] = ($counts[$e->getType()] ?? 0) + 1;
        }
        return $counts;
    }

    public static function inWorld(World $world): array {
        return array_values(array_filter($world->getEntities(), static fn(Entity $e) => $e instanceof BaseEntity));
    }

    public static function removeAllInWorld(World $world, bool $permanent = false): int {
        $n = 0;
        foreach (self::inWorld($world) as $e) {
            EntityLib::remove($e->getId(), $permanent);
            $n++;
        }
        return $n;
    }

    public static function isSafeSpawn(Vector3 $pos, World $world): bool {
        return !$world->getBlock($pos->subtract(0, 1, 0))->isTransparent()
            && $world->getBlock($pos)->isTransparent()
            && $world->getBlock($pos->add(0, 1, 0))->isTransparent();
    }

    public static function formatNametag(string $name, string $title = '', string $subtitle = ''): string {
        $lines = [];
        if ($title    !== '') $lines[] = "\u{00A7}7\u{00A7}o{$title}";
        $lines[] = "\u{00A7}f{$name}";
        if ($subtitle !== '') $lines[] = "\u{00A7}8{$subtitle}";
        return implode("\n", $lines);
    }
}
