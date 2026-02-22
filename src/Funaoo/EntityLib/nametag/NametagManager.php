<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Funaoo\EntityLib\EntityLib;

final class NametagManager {

    private array $nametags = [];
    private ?NametagUpdater $updater = null;

    public function register(int $entityId, DynamicNametag $nametag): void {
        $this->nametags[$entityId] = $nametag;
        $this->ensureRunning();
    }

    public function unregister(int $entityId): void {
        unset($this->nametags[$entityId]);
        if ($this->nametags === []) {
            $this->stopUpdater();
        }
    }

    public function updateAll(): void {
        foreach ($this->nametags as $entityId => $nametag) {
            $entity = EntityLib::get($entityId);
            if ($entity === null || $entity->isClosed()) {
                unset($this->nametags[$entityId]);
                continue;
            }
            $entity->setNameTag($nametag->getText($entity));
        }
        if ($this->nametags === []) {
            $this->stopUpdater();
        }
    }

    public function clearAll(): void {
        $this->nametags = [];
        $this->stopUpdater();
    }

    private function ensureRunning(): void {
        if ($this->updater === null) {
            $this->updater = new NametagUpdater($this);
            EntityLib::getPlugin()->getScheduler()->scheduleRepeatingTask($this->updater, 20);
        }
    }

    private function stopUpdater(): void {
        if ($this->updater !== null) {
            $this->updater->getHandler()?->cancel();
            $this->updater = null;
        }
    }
}
