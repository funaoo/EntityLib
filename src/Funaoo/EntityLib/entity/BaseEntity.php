<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use Funaoo\EntityLib\EntityLib;
use Funaoo\EntityLib\storage\EntityData;

abstract class BaseEntity extends Human {

    private const NBT_LOOK_AT_PLAYERS = 'LookAtPlayers';
    private const NBT_CAN_COLLIDE     = 'CanCollide';
    private const NBT_SPAWN_X         = 'SpawnX';
    private const NBT_SPAWN_Y         = 'SpawnY';
    private const NBT_SPAWN_Z         = 'SpawnZ';
    private const NBT_METADATA        = 'EntityLibMeta';

    protected bool    $lookAtPlayers    = false;
    protected bool    $canCollide       = false;
    protected Vector3 $spawnPosition;
    protected array   $customMetadata   = [];
    private   array   $interactDebounce = [];

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
        parent::__construct($location, $skin, $nbt);
    }

    /**
     * Builds the initial NBT for spawning a new entity.
     * Encodes all NPC-specific fields so initEntity() can restore them cleanly on chunk load.
     */
    public static function createSpawnNBT(
        Location $location,
        string   $name,
        float    $scale,
        bool     $nameTagVisible,
        bool     $nameTagAlwaysVisible,
        bool     $lookAtPlayers,
        bool     $canCollide,
        array    $metadata = [],
    ): CompoundTag {
        $tag = CompoundTag::create()
            ->setFloat('Scale', $scale)
            ->setString('CustomName', $name)
            ->setByte('CustomNameVisible', $nameTagVisible ? 1 : 0)
            ->setByte('CustomNameAlwaysVisible', $nameTagAlwaysVisible ? 1 : 0)
            ->setByte(self::NBT_LOOK_AT_PLAYERS, $lookAtPlayers ? 1 : 0)
            ->setByte(self::NBT_CAN_COLLIDE, $canCollide ? 1 : 0)
            ->setFloat(self::NBT_SPAWN_X, $location->x)
            ->setFloat(self::NBT_SPAWN_Y, $location->y)
            ->setFloat(self::NBT_SPAWN_Z, $location->z);

        if ($metadata !== []) {
            $metaTag = CompoundTag::create();
            foreach ($metadata as $k => $v) {
                $metaTag->setTag((string)$k, match(true) {
                    is_int($v)   => new \pocketmine\nbt\tag\IntTag($v),
                    is_float($v) => new FloatTag($v),
                    is_bool($v)  => new ByteTag($v ? 1 : 0),
                    default      => new StringTag((string)$v),
                });
            }
            $tag->setTag(self::NBT_METADATA, $metaTag);
        }

        return $tag;
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        $this->lookAtPlayers = (bool)$nbt->getByte(self::NBT_LOOK_AT_PLAYERS, 0);
        $this->canCollide    = (bool)$nbt->getByte(self::NBT_CAN_COLLIDE, 0);

        $this->spawnPosition = new Vector3(
            $nbt->getFloat(self::NBT_SPAWN_X, $this->location->x),
            $nbt->getFloat(self::NBT_SPAWN_Y, $this->location->y),
            $nbt->getFloat(self::NBT_SPAWN_Z, $this->location->z),
        );

        $metaTag = $nbt->getCompoundTag(self::NBT_METADATA);
        if ($metaTag !== null) {
            foreach ($metaTag->getValue() as $k => $tag) {
                $this->customMetadata[(string)$k] = $tag->getValue();
            }
        }
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();

        $nbt->setByte(self::NBT_LOOK_AT_PLAYERS, $this->lookAtPlayers ? 1 : 0);
        $nbt->setByte(self::NBT_CAN_COLLIDE, $this->canCollide ? 1 : 0);
        $nbt->setFloat(self::NBT_SPAWN_X, $this->spawnPosition->x);
        $nbt->setFloat(self::NBT_SPAWN_Y, $this->spawnPosition->y);
        $nbt->setFloat(self::NBT_SPAWN_Z, $this->spawnPosition->z);

        if ($this->customMetadata !== []) {
            $metaTag = CompoundTag::create();
            foreach ($this->customMetadata as $k => $v) {
                $metaTag->setTag((string)$k, match(true) {
                    is_int($v)   => new \pocketmine\nbt\tag\IntTag($v),
                    is_float($v) => new FloatTag($v),
                    is_bool($v)  => new ByteTag($v ? 1 : 0),
                    default      => new StringTag((string)$v),
                });
            }
            $nbt->setTag(self::NBT_METADATA, $metaTag);
        }

        return $nbt;
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        $result = parent::entityBaseTick($tickDiff);

        if ($this->lookAtPlayers && ($this->ticksLived % 2) === 0) {
            $this->tickLookAtPlayers();
        }

        if (!$this->location->asVector3()->equals($this->spawnPosition)) {
            $this->teleport($this->spawnPosition);
        }

        return $result;
    }

    private function tickLookAtPlayers(): void {
        $nearest   = null;
        $threshold = 64.0;

        foreach ($this->getWorld()->getPlayers() as $player) {
            $dsq = $this->getPosition()->distanceSquared($player->getPosition());
            if ($dsq < $threshold) {
                $threshold = $dsq;
                $nearest   = $player;
            }
        }

        if ($nearest === null) return;

        $self  = $this->getPosition()->add(0.0, $this->getEyeHeight(), 0.0);
        $other = $nearest->getPosition()->add(0.0, $nearest->getEyeHeight(), 0.0);
        $dx    = $other->x - $self->x;
        $dy    = $other->y - $self->y;
        $dz    = $other->z - $self->z;

        $this->setRotation(
            (float)(atan2(-$dx, $dz) / M_PI * 180.0),
            (float)(-atan2($dy, sqrt($dx ** 2 + $dz ** 2)) / M_PI * 180.0)
        );
        $this->broadcastMovement(true);
    }

    public function move(float $dx, float $dy, float $dz): void {}

    public function attack(EntityDamageEvent $source): void {
        $source->cancel();
        if ($source instanceof EntityDamageByEntityEvent && $source->getDamager() instanceof Player) {
            $this->handleInteraction($source->getDamager());
        }
    }

    private function handleInteraction(Player $player): void {
        $name = $player->getName();
        $now  = microtime(true);
        if (isset($this->interactDebounce[$name]) && ($now - $this->interactDebounce[$name]) < 0.3) return;
        $this->interactDebounce[$name] = $now;
        EntityLib::getInteractionHandler()->handleInteraction($this->getId(), $player, $this);
    }

    public function setLookAtPlayers(bool $enable): void      { $this->lookAtPlayers = $enable; }
    public function isLookingAtPlayers(): bool                { return $this->lookAtPlayers; }
    public function setCanCollideWith(bool $canCollide): void { $this->canCollide = $canCollide; }
    public function canCollideWith(Entity $entity): bool      { return $this->canCollide; }

    final public function setCustomMetadata(string $key, mixed $value): void    { $this->customMetadata[$key] = $value; }
    final public function getCustomMetadata(string $key, mixed $default = null): mixed { return $this->customMetadata[$key] ?? $default; }
    final public function hasCustomMetadata(string $key): bool                  { return array_key_exists($key, $this->customMetadata); }
    final public function removeCustomMetadata(string $key): void               { unset($this->customMetadata[$key]); }
    final public function getAllCustomMetadata(): array                          { return $this->customMetadata; }
    final public function getSpawnPosition(): Vector3                           { return $this->spawnPosition; }

    public function canSaveWithChunk(): bool { return false; }

    protected function onDeath(): void {
        parent::onDeath();
        EntityLib::unregisterEntity($this->getId());
    }

    final public function toEntityData(): EntityData {
        $particles = array_map(
            static fn(array $slot) => array_merge($slot['effect']->toArray(), ['interval' => $slot['interval']]),
            EntityLib::getEffectManager()->getEffects($this->getId())
        );

        return new EntityData(
            type:                 $this->getType(),
            name:                 $this->getNameTag(),
            x:                    $this->getPosition()->x,
            y:                    $this->getPosition()->y,
            z:                    $this->getPosition()->z,
            world:                $this->getWorld()->getFolderName(),
            yaw:                  $this->getLocation()->yaw,
            pitch:                $this->getLocation()->pitch,
            scale:                $this->getScale(),
            nameTagVisible:       $this->isNameTagVisible(),
            nameTagAlwaysVisible: $this->isNameTagAlwaysVisible(),
            lookAtPlayers:        $this->lookAtPlayers,
            canCollide:           $this->canCollide,
            skinId:               $this->getSkin()->getSkinId(),
            skinDataBase64:       base64_encode($this->getSkin()->getSkinData()),
            metadata:             $this->customMetadata,
            particles:            $particles,
        );
    }

    abstract public function getType(): string;
}
