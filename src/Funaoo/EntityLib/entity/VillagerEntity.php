<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

final class VillagerEntity extends BaseEntity {

    public const PROFESSION_FARMER     = 0;
    public const PROFESSION_LIBRARIAN  = 1;
    public const PROFESSION_PRIEST     = 2;
    public const PROFESSION_BLACKSMITH = 3;
    public const PROFESSION_BUTCHER    = 4;
    public const PROFESSION_NITWIT     = 5;

    private int $profession = self::PROFESSION_FARMER;

    public static function getNetworkTypeId(): string {
        return EntityIds::VILLAGER_V2;
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        $this->profession = $nbt->getInt('Profession', self::PROFESSION_FARMER);
        $this->setLookAtPlayers(true);
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setInt('Profession', $this->profession);
        return $nbt;
    }

    public function getType(): string {
        return EntityLib::VILLAGER;
    }

    public function setProfession(int $profession): void {
        if ($profession < 0 || $profession > 5) {
            throw new \InvalidArgumentException("Invalid profession: {$profession}");
        }
        $this->profession = $profession;
        $this->sendData($this->getViewers());
    }

    public function getProfession(): int {
        return $this->profession;
    }
}
