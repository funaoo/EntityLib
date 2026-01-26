<?php

declare(strict_types=1);

namespace Funaoo\EntityLib;

use Funaoo\EntityLib\effect\EffectManager;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\entity\EntityRegistry;
use Funaoo\EntityLib\interaction\InteractionHandler;
use Funaoo\EntityLib\listener\EntityListener;
use Funaoo\EntityLib\nametag\NametagManager;
use Funaoo\EntityLib\skin\SkinManager;
use Funaoo\EntityLib\storage\EntityStorage;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

final class EntityLib{

	public const HUMAN = "human";
	public const FLOATING_TEXT = "floating_text";
	public const PIG = "pig";
	public const COW = "cow";
	public const SHEEP = "sheep";
	public const CHICKEN = "chicken";
	public const ZOMBIE = "zombie";
	public const SKELETON = "skeleton";
	public const CREEPER = "creeper";
	public const VILLAGER = "villager";
	public const ARMOR_STAND = "armor_stand";

	private static ?Plugin $plugin = null;
	private static bool $registered = false;

	private static EntityRegistry $registry;
	private static EntityStorage $storage;
	private static EffectManager $effectManager;
	private static InteractionHandler $interactionHandler;
	private static SkinManager $skinManager;
	private static NametagManager $nametagManager;

	/** @var array<int, BaseEntity> */
	private static array $entities = [];

	public static function register(Plugin $plugin, bool $autoLoad = false) : void{
		if(self::$registered){
			$plugin->getLogger()->warning("EntityLib is already registered");
			return;
		}

		self::$plugin = $plugin;
		self::$registry = new EntityRegistry();
		self::$storage = new EntityStorage($plugin);
		self::$effectManager = new EffectManager();
		self::$interactionHandler = new InteractionHandler();
		self::$skinManager = new SkinManager($plugin);
		self::$nametagManager = new NametagManager();

		self::$registry->registerAll();

		Server::getInstance()->getPluginManager()->registerEvents(new EntityListener(), $plugin);

		if($autoLoad){
			$count = self::$storage->loadAll();
			if($count > 0){
				$plugin->getLogger()->info("Loaded {$count} saved entities");
			}
		}

		self::$registered = true;
		$plugin->getLogger()->info("EntityLib v" . self::getVersion() . " by " . self::getAuthor() . " initialized successfully");
	}

	public static function create(Vector3 $position, World $world) : EntityBuilder{
		self::checkRegistered();
		return new EntityBuilder($position, $world);
	}

	public static function createFloatingText(Vector3 $position, World $world, string $text) : BaseEntity{
		return self::create($position, $world)
			->floatingText()
			->setName($text)
			->setScale(0.01)
			->spawn();
	}

	public static function createHuman(Vector3 $position, World $world, string $name, ?Skin $skin = null) : EntityBuilder{
		$builder = self::create($position, $world)->human()->setName($name);

		return $skin !== null ? $builder->setSkin($skin) : $builder;
	}

	public static function createHumanFromPlayer(Player $player, Vector3 $position, World $world, string $name) : EntityBuilder{
		return self::create($position, $world)
			->human()
			->setName($name)
			->setSkin($player->getSkin());
	}

	public static function get(int $entityId) : ?BaseEntity{
		return self::$entities[$entityId] ?? null;
	}

	/** @return array<int, BaseEntity> */
	public static function getAll() : array{
		return self::$entities;
	}

	public static function remove(int $entityId, bool $permanent = false) : bool{
		$entity = self::$entities[$entityId] ?? null;
		if($entity === null){
			return false;
		}

		if(!$entity->isClosed()){
			$entity->flagForDespawn();
		}

		self::$effectManager->remove($entityId);
		self::$interactionHandler->unregister($entityId);
		self::$nametagManager->unregister($entityId);

		if($permanent){
			self::$storage->delete($entityId);
		}

		unset(self::$entities[$entityId]);
		return true;
	}

	public static function removeAll(bool $permanent = false) : void{
		if(self::$entities === []){
			return;
		}

		foreach(array_keys(self::$entities) as $id){
			self::remove($id, $permanent);
		}
	}

	public static function save(int $entityId) : bool{
		$entity = self::$entities[$entityId] ?? null;
		return $entity !== null && self::$storage->save($entity);
	}

	public static function saveAll() : void{
		if(self::$entities === []){
			return;
		}

		foreach(self::$entities as $entity){
			self::$storage->save($entity);
		}
	}

	public static function loadAll() : int{
		self::checkRegistered();
		return self::$storage->loadAll();
	}

	public static function getEffectManager() : EffectManager{
		self::checkRegistered();
		return self::$effectManager;
	}

	public static function getInteractionHandler() : InteractionHandler{
		self::checkRegistered();
		return self::$interactionHandler;
	}

	public static function getSkinManager() : SkinManager{
		self::checkRegistered();
		return self::$skinManager;
	}

	public static function getNametagManager() : NametagManager{
		self::checkRegistered();
		return self::$nametagManager;
	}

	public static function getPlugin() : Plugin{
		self::checkRegistered();
		return self::$plugin ?? throw new \RuntimeException("Plugin not set");
	}

	public static function registerEntity(BaseEntity $entity) : void{
		self::$entities[$entity->getId()] = $entity;
	}

	public static function unregisterEntity(int $entityId) : void{
		unset(self::$entities[$entityId]);
	}

	private static function checkRegistered() : void{
		if(!self::$registered){
			throw new \RuntimeException("EntityLib is not registered! Call EntityLib::register(\$this) in your plugin's onEnable() first.");
		}
	}

	public static function getVersion() : string{
		return "1.0.0";
	}

	public static function getAuthor() : string{
		return "Funaoo";
	}

	public static function getApiVersion() : string{
		return "5.39.2";
	}
}
