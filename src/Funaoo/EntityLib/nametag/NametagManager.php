<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\EntityLib;

/**
 * NametagManager - Manages dynamic nametags
 *
 * Handles nametags that update automatically (like showing player names,
 * stats, time, etc.)
 */
class NametagManager {

    private array $dynamicNametags = [];
    private ?NametagUpdater $updater = null;

    /**
     * Register a dynamic nametag
     */
    public function register(int $entityId, DynamicNametag $nametag): void {
        $this->dynamicNametags[$entityId] = $nametag;
        $this->ensureUpdaterRunning();
    }

    /**
     * Unregister a dynamic nametag
     */
    public function unregister(int $entityId): void {
        unset($this->dynamicNametags[$entityId]);

        if (empty($this->dynamicNametags)) {
            $this->stopUpdater();
        }
    }

    /**
     * Update all dynamic nametags
     */
    public function updateAll(): void {
        foreach ($this->dynamicNametags as $entityId => $nametag) {
            $entity = EntityLib::get($entityId);

            if ($entity === null || $entity->isClosed()) {
                unset($this->dynamicNametags[$entityId]);
                continue;
            }

            $text = $nametag->getText($entity);
            $entity->setNameTag($text);
        }
    }

    /**
     * Clear all dynamic nametags
     */
    public function clearAll(): void {
        $this->dynamicNametags = [];
        $this->stopUpdater();
    }

    /**
     * Ensure updater task is running
     */
    private function ensureUpdaterRunning(): void {
        if ($this->updater === null) {
            $this->updater = new NametagUpdater($this);
            EntityLib::getPlugin()->getScheduler()->scheduleRepeatingTask($this->updater, 20);
        }
    }

    /**
     * Stop updater task
     */
    private function stopUpdater(): void {
        if ($this->updater !== null) {
            $handler = $this->updater->getHandler();
            if ($handler !== null) {
                $handler->cancel();
            }
            $this->updater = null;
        }
    }
}