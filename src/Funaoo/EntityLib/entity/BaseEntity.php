<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Human;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use Funaoo\EntityLib\EntityLib;

abstract class BaseEntity extends Human {

    protected bool $lookAtPlayers = false;
    protected bool $canCollide = false;
    protected Vector3 $spawnPosition;
    protected array $customMetadata = [];
    protected array $lastInteract = [];

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
        parent::__construct($location, $skin, $nbt);

        $this->spawnPosition = $location->asVector3();
        $this->setNameTagAlwaysVisible(true);
        $this->setScale(1.0);
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if ($this->lookAtPlayers && $this->ticksLived % 5 === 0) {
            $this->updateRotationToNearestPlayer();
        }

        if (!$this->getLocation()->asVector3()->equals($this->spawnPosition)) {
            $this->teleport($this->spawnPosition);
        }

        return $hasUpdate;
    }

    protected function updateRotationToNearestPlayer(): void {
        $nearestPlayer = null;
        $nearestDistance = 8.0;

        foreach ($this->getWorld()->getPlayers() as $player) {
            $distance = $this->getPosition()->distance($player->getPosition());

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestPlayer = $player;
            }
        }

        if ($nearestPlayer !== null) {
            $xDist = $nearestPlayer->getPosition()->x - $this->getPosition()->x;
            $zDist = $nearestPlayer->getPosition()->z - $this->getPosition()->z;
            $yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;

            $yDist = $nearestPlayer->getPosition()->y - $this->getPosition()->y + 1.62;
            $distance = sqrt($xDist * $xDist + $zDist * $zDist);
            $pitch = -atan2($yDist, $distance) / M_PI * 180;

            $this->setRotation($yaw, $pitch);
        }
    }

    public function move(float $dx, float $dy, float $dz): void {
        return;
    }

    public function attack(EntityDamageEvent $source): void {
        $source->cancel();

        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();

            if ($damager instanceof Player) {
                $this->handleInteraction($damager);
            }
        }
    }

    protected function handleInteraction(Player $player): void {
        $playerName = $player->getName();
        $now = time();

        if (isset($this->lastInteract[$playerName])) {
            if ($now - $this->lastInteract[$playerName] < 0.5) {
                return;
            }
        }

        $this->lastInteract[$playerName] = $now;

        $handler = EntityLib::getInteractionHandler();
        $handler->handleInteraction($this->getId(), $player, $this);
    }

    public function setLookAtPlayers(bool $enable): void {
        $this->lookAtPlayers = $enable;
    }

    public function shouldLookAtPlayers(): bool {
        return $this->lookAtPlayers;
    }

    public function setCanCollideWith(bool $canCollide): void {
        $this->canCollide = $canCollide;
    }

    public function canCollideWith(Entity $entity): bool {
        return $this->canCollide;
    }

    public function setMetadata(string $key, mixed $value): void {
        $this->customMetadata[$key] = $value;
    }

    public function getMetadata(string $key, mixed $default = null): mixed {
        return $this->customMetadata[$key] ?? $default;
    }

    public function hasMetadata(string $key): bool {
        return isset($this->customMetadata[$key]);
    }

    public function removeMetadata(string $key): void {
        unset($this->customMetadata[$key]);
    }

    public function getAllMetadata(): array {
        return $this->customMetadata;
    }

    public function getSpawnPosition(): Vector3 {
        return $this->spawnPosition;
    }

    public function canSaveWithChunk(): bool {
        return false;
    }

    protected function onDeath(): void {
        parent::onDeath();
        EntityLib::unregisterEntity($this->getId());
    }

    public function toArray(): array {
        return [
            'type' => $this->getType(),
            'name' => $this->getNameTag(),
            'position' => [
                'x' => $this->getPosition()->x,
                'y' => $this->getPosition()->y,
                'z' => $this->getPosition()->z,
                'world' => $this->getWorld()->getFolderName()
            ],
            'rotation' => [
                'yaw' => $this->getLocation()->yaw,
                'pitch' => $this->getLocation()->pitch
            ],
            'scale' => $this->getScale(),
            'nameTagVisible' => $this->isNameTagVisible(),
            'nameTagAlwaysVisible' => $this->isNameTagAlwaysVisible(),
            'lookAtPlayers' => $this->lookAtPlayers,
            'canCollide' => $this->canCollide,
            'skin' => [
                'name' => $this->getSkin()->getSkinId(),
                'data' => base64_encode($this->getSkin()->getSkinData())
            ],
            'metadata' => $this->customMetadata
        ];
    }

    abstract public function getType(): string;
}