<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

final class MobEntity extends BaseEntity {

    private const NBT_MOB_TYPE = 'MobType';
    private const VALID = [EntityLib::ZOMBIE, EntityLib::SKELETON, EntityLib::CREEPER];

    private string $mobType;

    public function __construct(Location $location, string $mobType, Skin $skin, ?CompoundTag $nbt = null) {
        if (!in_array($mobType, self::VALID, true)) {
            throw new \InvalidArgumentException("Invalid mob type: {$mobType}");
        }
        $this->mobType = $mobType;
        parent::__construct($location, $skin, $nbt);
    }

    public static function getNetworkTypeId(): string { return EntityIds::ZOMBIE; }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        $saved = $nbt->getString(self::NBT_MOB_TYPE, '');
        if ($saved !== '' && in_array($saved, self::VALID, true)) {
            $this->mobType = $saved;
        }
    }

    public function saveNBT(): CompoundTag {
        return parent::saveNBT()->setString(self::NBT_MOB_TYPE, $this->mobType);
    }

    public function getType(): string   { return $this->mobType; }
    public function getMobType(): string { return $this->mobType; }
}
