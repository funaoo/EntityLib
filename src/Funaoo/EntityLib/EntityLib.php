<?php

declare(strict_types=1);

namespace Funaoo\EntityLib;

use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\World;
use Funaoo\EntityLib\effect\EffectManager;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\entity\EntityRegistry;
use Funaoo\EntityLib\interaction\InteractionHandler;
use Funaoo\EntityLib\listener\EntityListener;
use Funaoo\EntityLib\nametag\NametagManager;
use Funaoo\EntityLib\skin\SkinManager;
use Funaoo\EntityLib\storage\EntityStorage;

final class EntityLib {

    public const HUMAN         = 'human';
    public const FLOATING_TEXT = 'floating_text';
    public const PIG           = 'pig';
    public const COW           = 'cow';
    public const SHEEP         = 'sheep';
    public const CHICKEN       = 'chicken';
    public const ZOMBIE        = 'zombie';
    public const SKELETON      = 'skeleton';
    public const CREEPER       = 'creeper';
    public const VILLAGER      = 'villager';
    public const ARMOR_STAND   = 'armor_stand';

    private static bool $registered = false;
    private static Plugin $plugin;
    private static EntityRegistry $registry;
    private static EntityStorage $storage;
    private static EffectManager $effectManager;
    private static InteractionHandler $interactionHandler;
    private static SkinManager $skinManager;
    private static NametagManager $nametagManager;

    private static array $entities = [];

    public static function register(Plugin $plugin, bool $autoLoad = false, int $autoLoadDelayTicks = 20): void {
        if (self::$registered) {
            $plugin->getLogger()->warning('EntityLib is already registered.');
            return;
        }

        self::$plugin             = $plugin;
        self::$registry           = new EntityRegistry();
        self::$storage            = new EntityStorage($plugin);
        self::$effectManager      = new EffectManager();
        self::$interactionHandler = new InteractionHandler();
        self::$skinManager        = new SkinManager($plugin);
        self::$nametagManager     = new NametagManager();

        self::$registry->registerAll();

        Server::getInstance()->getPluginManager()->registerEvents(new EntityListener(), $plugin);

        if ($autoLoad) {
            $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(static function() use ($plugin): void {
                $n      = 0;
                $failed = 0;
                $data   = self::$storage;
                $total  = $data->count();
                if ($total === 0) {
                    return;
                }
                try {
                    $n = self::$storage->loadAll();
                } catch (\Throwable $e) {
                    $plugin->getLogger()->error('[EntityLib] autoload failed: ' . $e->getMessage());
                    return;
                }
                $failed = $total - $n;
                $plugin->getLogger()->info("[EntityLib] Autoloaded {$n}/{$total} entities." . ($failed > 0 ? " ({$failed} skipped — world not found or invalid data)" : ''));
            }), max(1, $autoLoadDelayTicks));
        }

        self::$registered = true;
        $plugin->getLogger()->info('EntityLib v' . self::VERSION . ' ready.');
    }

    public const VERSION = '1.2.0';

    public static function create(Vector3 $position, World $world): EntityBuilder {
        self::assertRegistered();
        return new EntityBuilder($position, $world);
    }

    public static function createFloatingText(Vector3 $position, World $world, string $text): BaseEntity {
        return self::create($position, $world)->floatingText()->setName($text)->spawn();
    }

    public static function createHuman(Vector3 $position, World $world, string $name, ?Skin $skin = null): EntityBuilder {
        $b = self::create($position, $world)->human()->setName($name);
        return $skin !== null ? $b->setSkin($skin) : $b;
    }

    public static function createHumanFromPlayer(Player $player, Vector3 $position, World $world, string $name): EntityBuilder {
        return self::create($position, $world)->human()->setName($name)->setSkin($player->getSkin());
    }

    public static function get(int $entityId): ?BaseEntity {
        return self::$entities[$entityId] ?? null;
    }

    public static function getAll(): array {
        return self::$entities;
    }

    public static function remove(int $entityId, bool $permanent = false): bool {
        $entity = self::$entities[$entityId] ?? null;
        if ($entity === null) {
            return false;
        }
        if (!$entity->isClosed()) {
            $entity->flagForDespawn();
        }
        self::$effectManager->remove($entityId);
        self::$interactionHandler->unregister($entityId);
        self::$nametagManager->unregister($entityId);
        if ($permanent) {
            self::$storage->delete($entityId);
        }
        unset(self::$entities[$entityId]);
        return true;
    }

    public static function removeAll(bool $permanent = false): void {
        foreach (array_keys(self::$entities) as $id) {
            self::remove($id, $permanent);
        }
    }

    public static function save(int $entityId): bool {
        $entity = self::$entities[$entityId] ?? null;
        return $entity !== null && self::$storage->save($entity);
    }

    public static function saveAll(): void {
        foreach (self::$entities as $entity) {
            self::$storage->save($entity);
        }
    }

    public static function loadAll(): int {
        self::assertRegistered();
        return self::$storage->loadAll();
    }

    public static function getEffectManager(): EffectManager {
        self::assertRegistered();
        return self::$effectManager;
    }

    public static function getInteractionHandler(): InteractionHandler {
        self::assertRegistered();
        return self::$interactionHandler;
    }

    public static function getSkinManager(): SkinManager {
        self::assertRegistered();
        return self::$skinManager;
    }

    public static function getNametagManager(): NametagManager {
        self::assertRegistered();
        return self::$nametagManager;
    }

    public static function getPlugin(): Plugin {
        self::assertRegistered();
        return self::$plugin;
    }

    public static function registerEntity(BaseEntity $entity): void {
        self::$entities[$entity->getId()] = $entity;
    }

    public static function unregisterEntity(int $entityId): void {
        unset(self::$entities[$entityId]);
    }

    private static function assertRegistered(): void {
        if (!self::$registered) {
            throw new \LogicException('EntityLib::register($this) must be called in onEnable() before using the library.');
        }
    }
}
