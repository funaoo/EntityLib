<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

final class HumanEntity extends BaseEntity {

    public static function getNetworkTypeId(): string {
        return EntityIds::PLAYER;
    }

    public function getType(): string {
        return EntityLib::HUMAN;
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(false);
    }

    public function canBeRenamed(): bool {
        return false;
    }
}
