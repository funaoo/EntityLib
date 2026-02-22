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

final class EntityBuilder {

    private const ANIMALS = [EntityLib::PIG, EntityLib::COW, EntityLib::SHEEP, EntityLib::CHICKEN];
    private const MOBS    = [EntityLib::ZOMBIE, EntityLib::SKELETON, EntityLib::CREEPER];

    private string   $type                = EntityLib::HUMAN;
    private string   $name                = '';
    private ?Skin    $skin                = null;
    private float    $scale               = 1.0;
    private float    $yaw                 = 0.0;
    private float    $pitch               = 0.0;
    private bool     $nameTagVisible      = true;
    private bool     $nameTagAlwaysVisible = false;
    private bool     $lookAtPlayers       = false;
    private bool     $canCollide          = false;
    private ?Closure $interactCallback    = null;
    private array    $particles           = [];
    private array    $metadata            = [];
    private bool     $persistent          = false;

    public function __construct(
        private readonly Vector3 $position,
        private readonly World   $world,
    ) {}

    public function human(): self {
        $this->type = EntityLib::HUMAN;
        return $this;
    }

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

    public function villager(): self {
        $this->type = EntityLib::VILLAGER;
        return $this;
    }

    public function setType(string $type): self {
        $this->type = $type;
        return $this;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function setSkin(Skin $skin): self {
        $this->skin = $skin;
        return $this;
    }

    public function setScale(float $scale): self {
        $this->scale = max(0.001, $scale);
        return $this;
    }

    public function setRotation(float $yaw, float $pitch = 0.0): self {
        $this->yaw   = $yaw;
        $this->pitch = $pitch;
        return $this;
    }

    public function setNameTagVisible(bool $visible): self {
        $this->nameTagVisible = $visible;
        return $this;
    }

    public function setNameTagAlwaysVisible(bool $always): self {
        $this->nameTagAlwaysVisible = $always;
        return $this;
    }

    public function lookAtPlayers(bool $enable = true): self {
        $this->lookAtPlayers = $enable;
        return $this;
    }

    public function setCanCollide(bool $collide): self {
        $this->canCollide = $collide;
        return $this;
    }

    public function onInteract(Closure $callback): self {
        $this->interactCallback = $callback;
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

    public function setMetadata(string $key, mixed $value): self {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function persistent(bool $save = true): self {
        $this->persistent = $save;
        return $this;
    }

    public function spawn(): BaseEntity {
        $location = Location::fromObject($this->position, $this->world, $this->yaw, $this->pitch);
        $skin     = $this->skin ?? EntityLib::getSkinManager()->getDefaultSkin($this->type);
        $type     = $this->type;

        $entity = match(true) {
            $type === EntityLib::HUMAN         => new HumanEntity($location, $skin),
            $type === EntityLib::FLOATING_TEXT => new FloatingTextEntity($location, $skin),
            in_array($type, ['pig', 'cow', 'sheep', 'chicken'], true) => new AnimalEntity($location, $type, $skin),
            in_array($type, ['zombie', 'skeleton', 'creeper'], true)  => new MobEntity($location, $type, $skin),
            $type === EntityLib::VILLAGER      => new VillagerEntity($location, $skin),
            default                            => new HumanEntity($location, $skin),
        };

        $entity->setNameTag($this->name);
        $entity->setNameTagVisible($this->nameTagVisible);
        $entity->setNameTagAlwaysVisible($this->nameTagAlwaysVisible);
        $entity->setScale($this->scale);
        $entity->setLookAtPlayers($this->lookAtPlayers);
        $entity->setCanCollideWith($this->canCollide);

        foreach ($this->metadata as $key => $value) {
            $entity->setCustomMetadata($key, $value);
        }

        $entity->spawnToAll();
        EntityLib::registerEntity($entity);

        $id = $entity->getId();

        if ($this->interactCallback !== null) {
            EntityLib::getInteractionHandler()->register($id, $this->interactCallback);
        }

        $effectManager = EntityLib::getEffectManager();
        foreach ($this->particles as $p) {
            $effectManager->addParticle($id, $p['type'], $p['interval'], $p['pattern'], $p['density'], $p['radius'], $p['height']);
        }

        if ($this->persistent) {
            EntityLib::save($id);
        }

        return $entity;
    }
}
