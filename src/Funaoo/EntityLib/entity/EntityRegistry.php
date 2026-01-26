<?php

/**
 * EntityRegistry - Registers all entity types with PocketMine
 *
 * PocketMine needs to know about our custom entities before we can spawn them.
 * This class handles all that registration stuff automatically.
 *
 * I tried to make this as simple as possible - just call registerAll() and
 * everything gets set up. No need to manually register each entity type.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use Funaoo\EntityLib\entity\HumanEntity;
use Funaoo\EntityLib\entity\FloatingTextEntity;
use Funaoo\EntityLib\entity\AnimalEntity;
use Funaoo\EntityLib\entity\MobEntity;
use Funaoo\EntityLib\entity\VillagerEntity;

/**
 * Handles entity registration with PocketMine's EntityFactory
 *
 * This is internal stuff that happens automatically when you call
 * EntityLib::register(). You shouldn't need to touch this directly.
 */
class EntityRegistry {

    /**
     * Register all entity types
     *
     * This registers our custom entities with PM5's EntityFactory so they
     * can be spawned and saved properly.
     */
    public function registerAll(): void {
        $factory = EntityFactory::getInstance();

        // Register HumanEntity
        $factory->register(HumanEntity::class, function(World $world, CompoundTag $nbt): HumanEntity {
            return new HumanEntity(
                EntityDataHelper::parseLocation($nbt, $world),
                Human::parseSkinNBT($nbt),
                $nbt
            );
        }, ['HumanEntity', 'entitylib:human']);

        // Register FloatingTextEntity
        $factory->register(FloatingTextEntity::class, function(World $world, CompoundTag $nbt): FloatingTextEntity {
            return new FloatingTextEntity(
                EntityDataHelper::parseLocation($nbt, $world),
                Human::parseSkinNBT($nbt),
                $nbt
            );
        }, ['FloatingTextEntity', 'entitylib:floating_text']);

        // Register AnimalEntity
        $factory->register(AnimalEntity::class, function(World $world, CompoundTag $nbt): AnimalEntity {
            $animalType = $nbt->getString("AnimalType", "pig");
            return new AnimalEntity(
                EntityDataHelper::parseLocation($nbt, $world),
                $animalType,
                Human::parseSkinNBT($nbt),
                $nbt
            );
        }, ['AnimalEntity', 'entitylib:animal']);

        // Register MobEntity
        $factory->register(MobEntity::class, function(World $world, CompoundTag $nbt): MobEntity {
            $mobType = $nbt->getString("MobType", "zombie");
            return new MobEntity(
                EntityDataHelper::parseLocation($nbt, $world),
                $mobType,
                Human::parseSkinNBT($nbt),
                $nbt
            );
        }, ['MobEntity', 'entitylib:mob']);

        // Register VillagerEntity
        $factory->register(VillagerEntity::class, function(World $world, CompoundTag $nbt): VillagerEntity {
            return new VillagerEntity(
                EntityDataHelper::parseLocation($nbt, $world),
                Human::parseSkinNBT($nbt),
                $nbt
            );
        }, ['VillagerEntity', 'entitylib:villager']);
    }

    /**
     * Check if an entity type is registered
     *
     * @param string $className Fully qualified class name
     * @return bool True if registered
     */
    public function isRegistered(string $className): bool {
        try {
            EntityFactory::getInstance()->get($className, null);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}