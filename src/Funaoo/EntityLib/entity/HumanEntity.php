<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

final class HumanEntity extends BaseEntity {

    public static function getNetworkTypeId(): string { return EntityIds::PLAYER; }
    public function getType(): string                 { return EntityLib::HUMAN; }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        $this->setNameTagVisible($nbt->getByte('CustomNameVisible', 1) === 1);
        $this->setNameTagAlwaysVisible((bool)$nbt->getByte('CustomNameAlwaysVisible', 0));
    }

    public function canBeRenamed(): bool { return false; }
}
