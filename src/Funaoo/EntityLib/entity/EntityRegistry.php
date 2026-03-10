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

        $factory->register(
            HumanEntity::class,
            static fn(World $w, CompoundTag $nbt): HumanEntity =>
                new HumanEntity(EntityDataHelper::parseLocation($nbt, $w), Human::parseSkinNBT($nbt), $nbt),
            ['HumanEntity', 'entitylib:human']
        );

        $factory->register(
            FloatingTextEntity::class,
            static fn(World $w, CompoundTag $nbt): FloatingTextEntity =>
                new FloatingTextEntity(EntityDataHelper::parseLocation($nbt, $w), Human::parseSkinNBT($nbt), $nbt),
            ['FloatingTextEntity', 'entitylib:floating_text']
        );

        $factory->register(
            AnimalEntity::class,
            static fn(World $w, CompoundTag $nbt): AnimalEntity =>
                new AnimalEntity(EntityDataHelper::parseLocation($nbt, $w), $nbt->getString('AnimalType', 'pig'), Human::parseSkinNBT($nbt), $nbt),
            ['AnimalEntity', 'entitylib:animal']
        );

        $factory->register(
            MobEntity::class,
            static fn(World $w, CompoundTag $nbt): MobEntity =>
                new MobEntity(EntityDataHelper::parseLocation($nbt, $w), $nbt->getString('MobType', 'zombie'), Human::parseSkinNBT($nbt), $nbt),
            ['MobEntity', 'entitylib:mob']
        );

        $factory->register(
            VillagerEntity::class,
            static fn(World $w, CompoundTag $nbt): VillagerEntity =>
                new VillagerEntity(EntityDataHelper::parseLocation($nbt, $w), Human::parseSkinNBT($nbt), $nbt),
            ['VillagerEntity', 'entitylib:villager']
        );
    }
}
