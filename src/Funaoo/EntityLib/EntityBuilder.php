<?php

declare(strict_types=1);

namespace Funaoo\EntityLib;

use Closure;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use Funaoo\EntityLib\entity\AnimalEntity;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\entity\FloatingTextEntity;
use Funaoo\EntityLib\entity\HumanEntity;
use Funaoo\EntityLib\entity\MobEntity;
use Funaoo\EntityLib\entity\VillagerEntity;
use Funaoo\EntityLib\nametag\Hologram;

final class EntityBuilder {

    private const ANIMALS = [EntityLib::PIG, EntityLib::COW, EntityLib::SHEEP, EntityLib::CHICKEN];
    private const MOBS    = [EntityLib::ZOMBIE, EntityLib::SKELETON, EntityLib::CREEPER];

    private string   $type                 = EntityLib::HUMAN;
    private string   $name                 = '';
    private ?Skin    $skin                 = null;
    private float    $scale                = 1.0;
    private float    $yaw                  = 0.0;
    private float    $pitch                = 0.0;
    private bool     $nameTagVisible       = true;
    private bool     $nameTagAlwaysVisible = false;
    private bool     $lookAtPlayers        = false;
    private bool     $canCollide           = false;
    private ?Closure $interactCallback     = null;
    private array    $particles            = [];
    private array    $metadata             = [];
    private bool     $persistent           = false;

    /** @var array<string|Closure> hologram lines (top → bottom order) */
    private array  $hologramLines       = [];
    private int    $hologramUpdateRate  = 20;
    private float  $hologramLineSpacing = Hologram::LINE_SPACING;
    private float  $hologramHeadOffset  = Hologram::HEAD_OFFSET;

    public function __construct(
        private readonly Vector3 $position,
        private readonly World   $world,
    ) {}

    //Type

    public function human(): self            { $this->type = EntityLib::HUMAN;    return $this; }
    public function villager(): self         { $this->type = EntityLib::VILLAGER; return $this; }
    public function setType(string $t): self { $this->type = $t;                  return $this; }

    public function floatingText(): self {
        $this->type                 = EntityLib::FLOATING_TEXT;
        $this->scale                = 0.01;
        $this->nameTagAlwaysVisible = true;
        return $this;
    }

    public function animal(string $animal): self {
        if (!in_array($animal, self::ANIMALS, true)) {
            throw new \InvalidArgumentException("Invalid animal type: {$animal}");
        }
        $this->type = $animal;
        return $this;
    }

    public function mob(string $mob): self {
        if (!in_array($mob, self::MOBS, true)) {
            throw new \InvalidArgumentException("Invalid mob type: {$mob}");
        }
        $this->type = $mob;
        return $this;
    }

    // ── Properties

    public function setName(string $name): self                  { $this->name = $name;               return $this; }
    public function setSkin(Skin $skin): self                    { $this->skin = $skin;                return $this; }
    public function setScale(float $scale): self                 { $this->scale = max(0.001, $scale);  return $this; }
    public function setNameTagVisible(bool $v): self             { $this->nameTagVisible = $v;         return $this; }
    public function setNameTagAlwaysVisible(bool $v): self       { $this->nameTagAlwaysVisible = $v;   return $this; }
    public function lookAtPlayers(bool $enable = true): self     { $this->lookAtPlayers = $enable;     return $this; }
    public function setCanCollide(bool $collide): self           { $this->canCollide = $collide;       return $this; }
    public function onInteract(Closure $cb): self                { $this->interactCallback = $cb;      return $this; }
    public function setMetadata(string $key, mixed $v): self     { $this->metadata[$key] = $v;         return $this; }
    public function persistent(bool $save = true): self          { $this->persistent = $save;          return $this; }

    public function setRotation(float $yaw, float $pitch = 0.0): self {
        $this->yaw   = $yaw;
        $this->pitch = $pitch;
        return $this;
    }

    public function addParticles(
        string $type,
        int    $interval = 20,
        string $pattern  = 'circle',
        int    $density  = 5,
        float  $radius   = 1.0,
        float  $height   = 2.0,
    ): self {
        $this->particles[] = compact('type', 'interval', 'pattern', 'density', 'radius', 'height');
        return $this;
    }

    // ── Hologram

    /**
     * Adds a hologram line above the NPC (top → bottom order).
     *
     * The last line added will sit HEAD_OFFSET (0.25) blocks above the NPC head.
     * Each previous line is stacked LINE_SPACING (0.25) higher.
     *
     * Supports static text or a dynamic Closure:
     *   ->hologramLine('§c§lHCF MAP 1')
     *   ->hologramLine(fn() => '§eJugadores: §6' . count($server->getOnlinePlayers()))
     */
    public function hologramLine(string|Closure $text): self {
        $this->hologramLines[] = $text;
        return $this;
    }

    /**
     * Convenience: set all hologram lines at once.
     *
     * Example:
     *   ->hologram([
     *       '§c§lHCF MAP 1',
     *       '§71.21.100',
     *       '§aEstado: §2Online',
     *       fn() => '§eJugadores: §6' . count($server->getOnlinePlayers()),
     *       '§aClic para ingresar',
     *   ])
     */
    public function hologram(array $lines): self {
        foreach ($lines as $line) $this->hologramLine($line);
        return $this;
    }

    /** How often (in ticks) dynamic hologram lines refresh. Default: 20 (1 second). */
    public function setHologramUpdateRate(int $ticks): self {
        $this->hologramUpdateRate = max(1, $ticks);
        return $this;
    }

    /**
     * Vertical gap between hologram lines in blocks.
     * Default: 0.25 — compact. Use 0.5 for more space, 0.1 for very tight.
     */
    public function setHologramLineSpacing(float $spacing): self {
        $this->hologramLineSpacing = max(0.0, $spacing);
        return $this;
    }

    /**
     * Gap between the last hologram line and the NPC's head in blocks.
     * Default: 0.25 — just above head.
     */
    public function setHologramHeadOffset(float $offset): self {
        $this->hologramHeadOffset = max(0.0, $offset);
        return $this;
    }

    // ── Spawn 

    public function spawn(): BaseEntity {
        $location = Location::fromObject($this->position, $this->world, $this->yaw, $this->pitch);
        $skin     = $this->skin ?? EntityLib::getSkinManager()->getDefaultSkin($this->type);
        $type     = $this->type;

        $nbt = BaseEntity::createSpawnNBT(
            location:             $location,
            name:                 $this->name,
            scale:                $this->scale,
            nameTagVisible:       $this->nameTagVisible,
            nameTagAlwaysVisible: $this->nameTagAlwaysVisible,
            lookAtPlayers:        $this->lookAtPlayers,
            canCollide:           $this->canCollide,
            metadata:             $this->metadata,
        );

        $entity = match(true) {
            $type === EntityLib::HUMAN           => new HumanEntity($location, $skin, $nbt),
            $type === EntityLib::FLOATING_TEXT   => new FloatingTextEntity($location, $skin, $nbt),
            in_array($type, self::ANIMALS, true) => (static function() use ($location, $type, $skin, $nbt): AnimalEntity {
                $nbt->setString('AnimalType', $type);
                return new AnimalEntity($location, $type, $skin, $nbt);
            })(),
            in_array($type, self::MOBS, true)    => (static function() use ($location, $type, $skin, $nbt): MobEntity {
                $nbt->setString('MobType', $type);
                return new MobEntity($location, $type, $skin, $nbt);
            })(),
            $type === EntityLib::VILLAGER        => new VillagerEntity($location, $skin, $nbt),
            default                              => new HumanEntity($location, $skin, $nbt),
        };

        $entity->spawnToAll();
        EntityLib::registerEntity($entity);

        $id = $entity->getId();

        if ($this->interactCallback !== null) {
            EntityLib::getInteractionHandler()->register($id, $this->interactCallback);
        }

        $em = EntityLib::getEffectManager();
        foreach ($this->particles as $p) {
            $em->addParticle($id, $p['type'], $p['interval'], $p['pattern'], $p['density'], $p['radius'], $p['height']);
        }

        if ($this->hologramLines !== []) {
            $hologram = Hologram::above($entity)
                ->setUpdateRate($this->hologramUpdateRate)
                ->setLineSpacing($this->hologramLineSpacing)
                ->setHeadOffset($this->hologramHeadOffset);
            foreach ($this->hologramLines as $line) {
                $hologram->line($line);
            }
            $hologram->spawn();
        }

        if ($this->persistent) {
            EntityLib::save($id);
        }

        return $entity;
    }
}
