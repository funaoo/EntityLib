<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

final class FloatingTextEntity extends BaseEntity {

    public static function getNetworkTypeId(): string {
        return EntityIds::PLAYER;
    }

    public function getType(): string {
        return EntityLib::FLOATING_TEXT;
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        $this->setScale(0.01);
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);
        $this->setCanCollideWith(false);
        $this->setLookAtPlayers(false);
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        if (!$this->getLocation()->asVector3()->equals($this->spawnPosition)) {
            $this->teleport($this->spawnPosition);
        }
        return parent::entityBaseTick($tickDiff);
    }

    public function setLookAtPlayers(bool $enable): void {
        parent::setLookAtPlayers(false);
    }

    public function setCanCollideWith(bool $canCollide): void {
        parent::setCanCollideWith(false);
    }

    public function updateText(string $text): void {
        $this->setNameTag($text);
    }

    public function canBeRenamed(): bool {
        return false;
    }
}
