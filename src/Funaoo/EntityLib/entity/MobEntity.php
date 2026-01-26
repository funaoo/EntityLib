<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

class MobEntity extends BaseEntity {

    private string $mobType;

    private const VALID_MOBS = [
        EntityLib::ZOMBIE,
        EntityLib::SKELETON,
        EntityLib::CREEPER
    ];

    public function __construct(Location $location, string $mobType, Skin $skin, ?CompoundTag $nbt = null) {
        if (!in_array($mobType, self::VALID_MOBS, true)) {
            throw new \InvalidArgumentException("Invalid mob type: {$mobType}");
        }

        $this->mobType = $mobType;

        parent::__construct($location, $skin, $nbt);
    }

    public static function getNetworkTypeId(): string {
        return EntityIds::ZOMBIE;
    }

    public function getActualNetworkTypeId(): string {
        return match($this->mobType) {
            EntityLib::ZOMBIE => EntityIds::ZOMBIE,
            EntityLib::SKELETON => EntityIds::SKELETON,
            EntityLib::CREEPER => EntityIds::CREEPER,
            default => EntityIds::ZOMBIE
        };
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setString("MobType", $this->mobType);
        return $nbt;
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        if ($nbt->getTag("MobType") !== null) {
            $this->mobType = $nbt->getString("MobType");
        }

        $this->setLookAtPlayers(false);
    }

    public function getType(): string {
        return $this->mobType;
    }

    public function getMobType(): string {
        return $this->mobType;
    }

    public function getName(): string {
        return match($this->mobType) {
            EntityLib::ZOMBIE => "Zombie",
            EntityLib::SKELETON => "Skeleton",
            EntityLib::CREEPER => "Creeper",
            default => "Mob"
        };
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function attack(\pocketmine\event\entity\EntityDamageEvent $source): void {
        $source->cancel();

        if ($source instanceof \pocketmine\event\entity\EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof \pocketmine\player\Player) {
                $this->handleInteraction($damager);
            }
        }
    }

    public function hasMovementUpdate(): bool {
        return false;
    }

    public function getDefaultScale(): float {
        return match($this->mobType) {
            EntityLib::ZOMBIE => 1.0,
            EntityLib::SKELETON => 1.0,
            EntityLib::CREEPER => 0.9,
            default => 1.0
        };
    }

    public function isDangerousLooking(): bool {
        return true;
    }

    public function toArray(): array {
        $data = parent::toArray();
        $data['mobType'] = $this->mobType;
        return $data;
    }
}