<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

final class AnimalEntity extends BaseEntity {

    private const NBT_ANIMAL_TYPE = 'AnimalType';
    private const VALID = [EntityLib::PIG, EntityLib::COW, EntityLib::SHEEP, EntityLib::CHICKEN];

    private string $animalType;

    public function __construct(Location $location, string $animalType, Skin $skin, ?CompoundTag $nbt = null) {
        if (!in_array($animalType, self::VALID, true)) {
            throw new \InvalidArgumentException("Invalid animal type: {$animalType}");
        }
        $this->animalType = $animalType;
        parent::__construct($location, $skin, $nbt);
    }

    public static function getNetworkTypeId(): string { return EntityIds::PIG; }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        $saved = $nbt->getString(self::NBT_ANIMAL_TYPE, '');
        if ($saved !== '' && in_array($saved, self::VALID, true)) {
            $this->animalType = $saved;
        }
    }

    public function saveNBT(): CompoundTag {
        return parent::saveNBT()->setString(self::NBT_ANIMAL_TYPE, $this->animalType);
    }

    public function getType(): string      { return $this->animalType; }
    public function getAnimalType(): string { return $this->animalType; }
}
