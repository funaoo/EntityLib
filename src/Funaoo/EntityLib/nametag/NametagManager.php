<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Funaoo\EntityLib\EntityLib;

final class NametagManager {


    private array $nametags = [];


    private array $holograms = [];

    private ?NametagUpdater $updater = null;



    public function register(int $entityId, DynamicNametag $nametag, int $updateRate = 20): void {
        $this->nametags[$entityId] = ['nametag' => $nametag, 'rate' => max(1, $updateRate), 'tick' => 0];
        $this->ensureRunning();
    }

    public function unregister(int $entityId): void {
        unset($this->nametags[$entityId]);
        $this->stopIfIdle();
    }



    public function registerHologram(Hologram $hologram): void {
        $this->holograms[spl_object_id($hologram)] = [
            'hologram' => $hologram,
            'rate'     => $hologram->getUpdateRate(),
            'tick'     => 0,
        ];
        $this->ensureRunning();
    }

    public function unregisterHologram(Hologram $hologram): void {
        unset($this->holograms[spl_object_id($hologram)]);
        $this->stopIfIdle();
    }



    public function updateAll(): void {
        foreach ($this->nametags as $entityId => &$entry) {
            $entity = EntityLib::get($entityId);
            if ($entity === null || $entity->isClosed()) {
                unset($this->nametags[$entityId]);
                continue;
            }
            if (++$entry['tick'] >= $entry['rate']) {
                $entity->setNameTag($entry['nametag']->getText($entity));
                $entry['tick'] = 0;
            }
        }
        unset($entry);

        foreach ($this->holograms as $key => &$entry) {
            if (!$entry['hologram']->isSpawned()) {
                unset($this->holograms[$key]);
                continue;
            }
            if (++$entry['tick'] >= $entry['rate']) {
                $entry['hologram']->tick();
                $entry['tick'] = 0;
            }
        }
        unset($entry);

        $this->stopIfIdle();
    }

    public function clearAll(): void {
        $this->nametags  = [];
        $this->holograms = [];
        $this->stopUpdater();
    }



    private function ensureRunning(): void {
        if ($this->updater === null) {
            $this->updater = new NametagUpdater($this);
            EntityLib::getPlugin()->getScheduler()->scheduleRepeatingTask($this->updater, 1);
        }
    }

    private function stopIfIdle(): void {
        if ($this->nametags === [] && $this->holograms === []) {
            $this->stopUpdater();
        }
    }

    private function stopUpdater(): void {
        if ($this->updater !== null) {
            $this->updater->getHandler()?->cancel();
            $this->updater = null;
        }
    }
}
