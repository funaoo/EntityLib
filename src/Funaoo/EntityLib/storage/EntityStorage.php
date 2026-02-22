<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\storage;

use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\EntityLib;

final class EntityStorage {

    private JsonProvider $provider;

    public function __construct(private readonly Plugin $plugin) {
        $this->provider = new JsonProvider($plugin->getDataFolder() . 'entities.json');
    }

    public function save(BaseEntity $entity): bool {
        $data = $this->provider->load();
        $data[(string)$entity->getId()] = $entity->toEntityData()->toArray();
        return $this->provider->save($data);
    }

    public function delete(int $entityId): bool {
        $data = $this->provider->load();
        if (!isset($data[(string)$entityId])) {
            return false;
        }
        unset($data[(string)$entityId]);
        return $this->provider->save($data);
    }

    public function loadAll(): int {
        $loaded = 0;
        foreach ($this->provider->load() as $row) {
            if (!is_array($row)) {
                continue;
            }
            try {
                $ed = EntityData::fromArray($row);
                if ($this->spawnFromData($ed)) {
                    $loaded++;
                }
            } catch (\Throwable $e) {
                $this->plugin->getLogger()->error('[EntityLib] load error: ' . $e->getMessage());
            }
        }
        return $loaded;
    }

    public function clearAll(): bool {
        return $this->provider->delete();
    }

    public function backup(): bool {
        return $this->provider->backup();
    }

    public function count(): int {
        return count($this->provider->load());
    }

    private function spawnFromData(EntityData $ed): bool {
        $wm = Server::getInstance()->getWorldManager();

        if (!$wm->isWorldLoaded($ed->world)) {
            if (!$wm->loadWorld($ed->world)) {
                $this->plugin->getLogger()->warning("[EntityLib] World '{$ed->world}' not found — skipping entity '{$ed->name}'.");
                return false;
            }
        }

        $world = $wm->getWorldByName($ed->world);
        if ($world === null) {
            $this->plugin->getLogger()->warning("[EntityLib] Could not get world '{$ed->world}' — skipping entity '{$ed->name}'.");
            return false;
        }

        $skinBytes = base64_decode($ed->skinDataBase64, true);
        if ($skinBytes === false || strlen($skinBytes) < 8192) {
            $skinBytes = str_repeat("\x00", 8192);
        }
        $skin = new Skin($ed->skinId !== '' ? $ed->skinId : 'Standard_Custom', $skinBytes);

        $builder = EntityLib::create(new Vector3($ed->x, $ed->y, $ed->z), $world)
            ->setType($ed->type)
            ->setName($ed->name)
            ->setSkin($skin)
            ->setScale($ed->scale)
            ->setRotation($ed->yaw, $ed->pitch)
            ->setNameTagVisible($ed->nameTagVisible)
            ->setNameTagAlwaysVisible($ed->nameTagAlwaysVisible)
            ->lookAtPlayers($ed->lookAtPlayers)
            ->setCanCollide($ed->canCollide);

        foreach ($ed->metadata as $key => $value) {
            $builder->setMetadata((string)$key, $value);
        }

        foreach ($ed->particles as $p) {
            if (!is_array($p) || !isset($p['type'])) {
                continue;
            }
            $builder->addParticles(
                type:     (string)$p['type'],
                interval: (int)($p['interval'] ?? 20),
                pattern:  (string)($p['pattern'] ?? 'circle'),
                density:  (int)($p['density']   ?? 5),
                radius:   (float)($p['radius']  ?? 1.0),
                height:   (float)($p['height']  ?? 2.0),
            );
        }

        $entity = $builder->spawn();

        if (isset($ed->metadata['__interactionCommand']) && is_string($ed->metadata['__interactionCommand'])) {
            $cmd      = ltrim((string)$ed->metadata['__interactionCommand'], '/');
            $entityId = $entity->getId();
            EntityLib::getInteractionHandler()->register($entityId, static function(\pocketmine\player\Player $player) use ($cmd): void {
                $player->getServer()->dispatchCommand($player, $cmd);
            });
        }

        return true;
    }
}
