<?php

declare(strict_types=1);

namespace Funaoo\EntityLib;

use Closure;
use Funaoo\EntityLib\entity\AnimalEntity;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\entity\FloatingTextEntity;
use Funaoo\EntityLib\entity\HumanEntity;
use Funaoo\EntityLib\entity\MobEntity;
use Funaoo\EntityLib\entity\VillagerEntity;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;

class EntityBuilder{

	private const ANIMALS = [
		EntityLib::PIG => true,
		EntityLib::COW => true,
		EntityLib::SHEEP => true,
		EntityLib::CHICKEN => true,
	];

	private const MOBS = [
		EntityLib::ZOMBIE => true,
		EntityLib::SKELETON => true,
		EntityLib::CREEPER => true,
	];

	private Vector3 $position;
	private World $world;
	private string $type = EntityLib::HUMAN;
	private string $name = "";
	private ?Skin $skin = null;
	private float $scale = 1.0;
	private float $yaw = 0.0;
	private float $pitch = 0.0;
	private bool $nameTagVisible = true;
	private bool $nameTagAlwaysVisible = true;
	private bool $lookAtPlayers = false;
	private bool $canCollide = false;
	private ?Closure $interactCallback = null;
	private array $particles = [];
	private array $metadata = [];
	private bool $saveToStorage = false;

	public function __construct(Vector3 $position, World $world){
		$this->position = $position;
		$this->world = $world;
	}

	public function human() : self{
		$this->type = EntityLib::HUMAN;
		return $this;
	}

	public function floatingText() : self{
		$this->type = EntityLib::FLOATING_TEXT;
		$this->scale = 0.01;
		$this->nameTagAlwaysVisible = true;
		return $this;
	}

	public function animal(string $animal) : self{
		if(!isset(self::ANIMALS[$animal])){
			throw new \InvalidArgumentException("Invalid animal type: {$animal}");
		}
		$this->type = $animal;
		return $this;
	}

	public function mob(string $mob) : self{
		if(!isset(self::MOBS[$mob])){
			throw new \InvalidArgumentException("Invalid mob type: {$mob}");
		}
		$this->type = $mob;
		return $this;
	}

	public function villager() : self{
		$this->type = EntityLib::VILLAGER;
		return $this;
	}

	public function setName(string $name) : self{
		$this->name = $name;
		return $this;
	}

	public function setSkin(Skin $skin) : self{
		$this->skin = $skin;
		return $this;
	}

	public function setScale(float $scale) : self{
		$this->scale = $scale;
		return $this;
	}

	public function setRotation(float $yaw, float $pitch = 0.0) : self{
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		return $this;
	}

	public function setNameTagVisible(bool $visible) : self{
		$this->nameTagVisible = $visible;
		return $this;
	}

	public function setNameTagAlwaysVisible(bool $alwaysVisible) : self{
		$this->nameTagAlwaysVisible = $alwaysVisible;
		return $this;
	}

	public function lookAtPlayers(bool $enable = true) : self{
		$this->lookAtPlayers = $enable;
		return $this;
	}

	public function setCanCollide(bool $canCollide) : self{
		$this->canCollide = $canCollide;
		return $this;
	}

	public function onInteract(Closure $callback) : self{
		$this->interactCallback = $callback;
		return $this;
	}

	public function addParticles(string $particleType, int $interval = 20) : self{
		$this->particles[] = [
			'type' => $particleType,
			'interval' => $interval
		];
		return $this;
	}

	public function setMetadata(string $key, mixed $value) : self{
		$this->metadata[$key] = $value;
		return $this;
	}

	public function persistent(bool $save = true) : self{
		$this->saveToStorage = $save;
		return $this;
	}

	public function spawn() : BaseEntity{
		$location = Location::fromObject($this->position, $this->world, $this->yaw, $this->pitch);

		$type = $this->type;
		$skin = $this->skin ?? EntityLib::getSkinManager()->getDefaultSkin($type);

		$entity = match(true){
			$type === EntityLib::HUMAN => new HumanEntity($location, $skin),
			$type === EntityLib::FLOATING_TEXT => new FloatingTextEntity($location, $skin),
			isset(self::ANIMALS[$type]) => new AnimalEntity($location, $type, $skin),
			isset(self::MOBS[$type]) => new MobEntity($location, $type, $skin),
			$type === EntityLib::VILLAGER => new VillagerEntity($location, $skin),
			default => new HumanEntity($location, $skin)
		};

		$entity->setNameTag($this->name);
		$entity->setNameTagVisible($this->nameTagVisible);
		$entity->setNameTagAlwaysVisible($this->nameTagAlwaysVisible);
		$entity->setScale($this->scale);
		$entity->setLookAtPlayers($this->lookAtPlayers);
		$entity->setCanCollideWith($this->canCollide);

		if($this->metadata !== []){
			foreach($this->metadata as $key => $value){
				$entity->setMetadata($key, $value);
			}
		}

		$entity->spawnToAll();

		EntityLib::registerEntity($entity);

		$entityId = $entity->getId();

		if($this->interactCallback !== null){
			EntityLib::getInteractionHandler()->register($entityId, $this->interactCallback);
		}

		if($this->particles !== []){
			$effectManager = EntityLib::getEffectManager();
			foreach($this->particles as $particle){
				$effectManager->addParticle($entityId, $particle['type'], $particle['interval']);
			}
		}

		if($this->saveToStorage){
			EntityLib::save($entityId);
		}

		return $entity;
	}
}
