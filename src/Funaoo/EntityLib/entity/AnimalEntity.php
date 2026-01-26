<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

class AnimalEntity extends BaseEntity {

    private string $animalType;

    private const VALID_ANIMALS = [
        EntityLib::PIG,
        EntityLib::COW,
        EntityLib::SHEEP,
        EntityLib::CHICKEN
    ];

    public function __construct(Location $location, string $animalType, Skin $skin, ?CompoundTag $nbt = null) {
        if (!in_array($animalType, self::VALID_ANIMALS, true)) {
            throw new \InvalidArgumentException("Invalid animal type: {$animalType}");
        }

        $this->animalType = $animalType;

        parent::__construct($location, $skin, $nbt);
    }

    public static function getNetworkTypeId(): string {
        return EntityIds::PIG;
    }

    public function getActualNetworkTypeId(): string {
        return match($this->animalType) {
            EntityLib::PIG => EntityIds::PIG,
            EntityLib::COW => EntityIds::COW,
            EntityLib::SHEEP => EntityIds::SHEEP,
            EntityLib::CHICKEN => EntityIds::CHICKEN,
            default => EntityIds::PIG
        };
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setString("AnimalType", $this->animalType);
        return $nbt;
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        if ($nbt->getTag("AnimalType") !== null) {
            $this->animalType = $nbt->getString("AnimalType");
        }
    }

    public function getType(): string {
        return $this->animalType;
    }

    public function getAnimalType(): string {
        return $this->animalType;
    }

    public function getName(): string {
        return match($this->animalType) {
            EntityLib::PIG => "Pig",
            EntityLib::COW => "Cow",
            EntityLib::SHEEP => "Sheep",
            EntityLib::CHICKEN => "Chicken",
            default => "Animal"
        };
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function getDefaultScale(): float {
        return match($this->animalType) {
            EntityLib::PIG => 1.0,
            EntityLib::COW => 1.2,
            EntityLib::SHEEP => 0.9,
            EntityLib::CHICKEN => 0.7,
            default => 1.0
        };
    }

    public function toArray(): array {
        $data = parent::toArray();
        $data['animalType'] = $this->animalType;
        return $data;
    }
}