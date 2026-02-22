<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

final class EntityRegistry {

    public function registerAll(): void {
        $factory = EntityFactory::getInstance();

        $factory->register(HumanEntity::class, static function(World $world, CompoundTag $nbt): HumanEntity {
            return new HumanEntity(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, ['HumanEntity', 'entitylib:human']);

        $factory->register(FloatingTextEntity::class, static function(World $world, CompoundTag $nbt): FloatingTextEntity {
            return new FloatingTextEntity(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, ['FloatingTextEntity', 'entitylib:floating_text']);

        $factory->register(AnimalEntity::class, static function(World $world, CompoundTag $nbt): AnimalEntity {
            return new AnimalEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt->getString('AnimalType', 'pig'), Human::parseSkinNBT($nbt), $nbt);
        }, ['AnimalEntity', 'entitylib:animal']);

        $factory->register(MobEntity::class, static function(World $world, CompoundTag $nbt): MobEntity {
            return new MobEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt->getString('MobType', 'zombie'), Human::parseSkinNBT($nbt), $nbt);
        }, ['MobEntity', 'entitylib:mob']);

        $factory->register(VillagerEntity::class, static function(World $world, CompoundTag $nbt): VillagerEntity {
            return new VillagerEntity(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, ['VillagerEntity', 'entitylib:villager']);
    }
}
