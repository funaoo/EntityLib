<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

class VillagerEntity extends BaseEntity {

    private int $profession = 0;

    public const PROFESSION_FARMER = 0;
    public const PROFESSION_LIBRARIAN = 1;
    public const PROFESSION_PRIEST = 2;
    public const PROFESSION_BLACKSMITH = 3;
    public const PROFESSION_BUTCHER = 4;
    public const PROFESSION_NITWIT = 5;

    public static function getNetworkTypeId(): string {
        return EntityIds::VILLAGER_V2;
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        if ($nbt->getTag("Profession") !== null) {
            $this->profession = $nbt->getInt("Profession");
        }

        $this->setLookAtPlayers(true);
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setInt("Profession", $this->profession);
        return $nbt;
    }

    public function getType(): string {
        return EntityLib::VILLAGER;
    }

    public function getName(): string {
        return "Villager";
    }

    public function setProfession(int $profession): void {
        if ($profession < 0 || $profession > 5) {
            throw new \InvalidArgumentException("Invalid profession ID: {$profession}");
        }

        $this->profession = $profession;
        $this->sendData($this->getViewers());
    }

    public function getProfession(): int {
        return $this->profession;
    }

    public function getProfessionName(): string {
        return match($this->profession) {
            self::PROFESSION_FARMER => "Farmer",
            self::PROFESSION_LIBRARIAN => "Librarian",
            self::PROFESSION_PRIEST => "Priest",
            self::PROFESSION_BLACKSMITH => "Blacksmith",
            self::PROFESSION_BUTCHER => "Butcher",
            self::PROFESSION_NITWIT => "Nitwit",
            default => "Villager"
        };
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function toArray(): array {
        $data = parent::toArray();
        $data['profession'] = $this->profession;
        $data['professionName'] = $this->getProfessionName();
        return $data;
    }
}