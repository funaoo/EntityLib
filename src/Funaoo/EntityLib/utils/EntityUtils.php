<?php

/**
 * EntityUtils - Helpful utility functions
 *
 * Every project needs a utils class. This is where I throw all the
 * helper functions that don't belong anywhere else but are super useful.
 *
 * These are all static methods, so you can use them anywhere without
 * creating an instance.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\utils;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\EntityLib;

/**
 * Utility functions for entities
 *
 * Static helper methods that make working with entities easier.
 */
class EntityUtils {

    /**
     * Get all entities within a radius
     *
     * Useful for things like "click this NPC to see nearby shops" or
     * area-based interactions.
     *
     * @param Vector3 $position Center position
     * @param World $world The world
     * @param float $radius Search radius in blocks
     * @param bool $onlyLibraryEntities Only return EntityLib entities?
     * @return Entity[] Array of entities
     */
    public static function getEntitiesInRadius(
        Vector3 $position,
        World $world,
        float $radius,
        bool $onlyLibraryEntities = true
    ): array {
        $entities = [];
        $radiusSquared = $radius * $radius;

        foreach ($world->getEntities() as $entity) {
            if ($onlyLibraryEntities && !$entity instanceof BaseEntity) {
                continue;
            }

            if ($entity->getPosition()->distanceSquared($position) <= $radiusSquared) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    /**
     * Get nearest entity to a position
     *
     * @param Vector3 $position Center position
     * @param World $world The world
     * @param float $maxDistance Maximum search distance
     * @param bool $onlyLibraryEntities Only search EntityLib entities?
     * @return Entity|null The nearest entity or null
     */
    public static function getNearestEntity(
        Vector3 $position,
        World $world,
        float $maxDistance = 50.0,
        bool $onlyLibraryEntities = true
    ): ?Entity {
        $nearest = null;
        $nearestDistance = $maxDistance * $maxDistance;

        foreach ($world->getEntities() as $entity) {
            if ($onlyLibraryEntities && !$entity instanceof BaseEntity) {
                continue;
            }

            $distance = $entity->getPosition()->distanceSquared($position);

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $entity;
            }
        }

        return $nearest;
    }

    /**
     * Get entity player is looking at
     *
     * Performs a raycast to see what entity the player is looking at.
     * Super useful for custom interaction systems.
     *
     * @param Player $player The player
     * @param float $maxDistance Maximum raycast distance
     * @param bool $onlyLibraryEntities Only detect EntityLib entities?
     * @return Entity|null The entity being looked at, or null
     */
    public static function getEntityLookingAt(
        Player $player,
        float $maxDistance = 5.0,
        bool $onlyLibraryEntities = true
    ): ?Entity {
        $direction = $player->getDirectionVector();
        $position = $player->getEyePos();

        // Check in small steps along the line of sight
        $steps = (int)($maxDistance * 10); // Check every 0.1 blocks

        for ($i = 0; $i < $steps; $i++) {
            $checkPos = $position->addVector($direction->multiply($i * 0.1));

            // Find entities near this point
            foreach ($player->getWorld()->getNearbyEntities($checkPos->expandedCopy(0.5, 0.5, 0.5)) as $entity) {
                if ($entity === $player) {
                    continue;
                }

                if ($onlyLibraryEntities && !$entity instanceof BaseEntity) {
                    continue;
                }

                // Check if this entity's hitbox intersects the raycast
                if ($entity->getBoundingBox()->isVectorInside($checkPos)) {
                    return $entity;
                }
            }
        }

        return null;
    }

    /**
     * Teleport entity smoothly (with particles)
     *
     * Adds a nice teleport effect when moving entities around.
     *
     * @param BaseEntity $entity The entity to teleport
     * @param Vector3 $destination Where to teleport to
     * @param bool $withEffect Show particle effect?
     */
    public static function teleportWithEffect(
        BaseEntity $entity,
        Vector3 $destination,
        bool $withEffect = true
    ): void {
        $world = $entity->getWorld();
        $startPos = $entity->getPosition();

        if ($withEffect) {
            // Teleport particles at start position
            for ($i = 0; $i < 20; $i++) {
                $world->addParticle(
                    $startPos->add(
                        (mt_rand(-10, 10) / 10),
                        mt_rand(0, 20) / 10,
                        (mt_rand(-10, 10) / 10)
                    ),
                    new \pocketmine\world\particle\EndermanTeleportParticle()
                );
            }
        }

        // Do the actual teleport
        $entity->teleport($destination);

        if ($withEffect) {
            // Particles at destination
            for ($i = 0; $i < 20; $i++) {
                $world->addParticle(
                    $destination->add(
                        (mt_rand(-10, 10) / 10),
                        mt_rand(0, 20) / 10,
                        (mt_rand(-10, 10) / 10)
                    ),
                    new \pocketmine\world\particle\EndermanTeleportParticle()
                );
            }
        }
    }

    /**
     * Make entity face another entity
     *
     * @param BaseEntity $entity Entity to rotate
     * @param Entity $target Entity to face
     */
    public static function faceEntity(BaseEntity $entity, Entity $target): void {
        $entityPos = $entity->getPosition();
        $targetPos = $target->getPosition();

        $xDist = $targetPos->x - $entityPos->x;
        $zDist = $targetPos->z - $entityPos->z;

        $yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;

        $yDist = $targetPos->y - $entityPos->y;
        $distance = sqrt($xDist * $xDist + $zDist * $zDist);
        $pitch = -atan2($yDist, $distance) / M_PI * 180;

        $entity->setRotation($yaw, $pitch);
    }

    /**
     * Make entity face a position
     *
     * @param BaseEntity $entity Entity to rotate
     * @param Vector3 $position Position to face
     */
    public static function facePosition(BaseEntity $entity, Vector3 $position): void {
        $entityPos = $entity->getPosition();

        $xDist = $position->x - $entityPos->x;
        $zDist = $position->z - $entityPos->z;

        $yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;

        $yDist = $position->y - $entityPos->y;
        $distance = sqrt($xDist * $xDist + $zDist * $zDist);
        $pitch = -atan2($yDist, $distance) / M_PI * 180;

        $entity->setRotation($yaw, $pitch);
    }

    /**
     * Get all EntityLib entities in a world
     *
     * @param World $world The world
     * @return BaseEntity[] Array of entities
     */
    public static function getEntitiesInWorld(World $world): array {
        $entities = [];

        foreach ($world->getEntities() as $entity) {
            if ($entity instanceof BaseEntity) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    /**
     * Count entities by type
     *
     * Returns an array like: ['human' => 5, 'villager' => 3]
     * Useful for stats or debugging.
     *
     * @return array<string, int> Entity counts by type
     */
    public static function countByType(): array {
        $counts = [];

        foreach (EntityLib::getAll() as $entity) {
            $type = $entity->getType();
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Remove all entities in a world
     *
     * Nuclear option - removes all EntityLib entities from a world.
     *
     * @param World $world The world to clean
     * @param bool $permanent Also delete from storage?
     * @return int Number of entities removed
     */
    public static function removeAllInWorld(World $world, bool $permanent = false): int {
        $removed = 0;

        foreach (self::getEntitiesInWorld($world) as $entity) {
            EntityLib::remove($entity->getId(), $permanent);
            $removed++;
        }

        return $removed;
    }

    /**
     * Format a fancy nametag with colors
     *
     * Helper function for creating nice-looking nametags.
     *
     * @param string $name The name
     * @param string $title Optional title above name
     * @param string $subtitle Optional subtitle below name
     * @return string Formatted nametag with colors
     */
    public static function formatNametag(
        string $name,
        string $title = "",
        string $subtitle = ""
    ): string {
        $lines = [];

        if ($title !== "") {
            $lines[] = "§7§o{$title}";
        }

        $lines[] = "§f{$name}";

        if ($subtitle !== "") {
            $lines[] = "§8{$subtitle}";
        }

        return implode("\n", $lines);
    }

    /**
     * Check if position is safe for spawning
     *
     * Makes sure the position has solid ground and isn't inside a block.
     *
     * @param Vector3 $position Position to check
     * @param World $world The world
     * @return bool True if safe
     */
    public static function isSafeSpawnPosition(Vector3 $position, World $world): bool {
        // Check if there's a solid block below
        $blockBelow = $world->getBlock($position->subtract(0, 1, 0));
        if (!$blockBelow->isSolid()) {
            return false;
        }

        // Check if spawn position is not inside a solid block
        $blockAt = $world->getBlock($position);
        if ($blockAt->isSolid()) {
            return false;
        }

        // Check head space
        $blockAbove = $world->getBlock($position->add(0, 1, 0));
        if ($blockAbove->isSolid()) {
            return false;
        }

        return true;
    }
}