<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\effect;

use pocketmine\scheduler\Task;
use Funaoo\EntityLib\EntityLib;
use Funaoo\EntityLib\entity\BaseEntity;

/**
 * EffectManager - Manages all particle effects for entities
 *
 * This runs a repeating task that spawns particles around entities.
 * It's optimized to handle hundreds of entities without lag.
 */
class EffectManager {

    private array $effects = [];
    private ?EffectTask $task = null;
    private int $tickCounter = 0;

    public function addParticle(
        int $entityId,
        string $particleType,
        int $interval = 20,
        string $pattern = "circle",
        int $density = 5,
        float $radius = 1.0,
        float $height = 2.0
    ): void {
        $effect = new ParticleEffect($particleType, $pattern, $density, $radius, $height);

        if (!isset($this->effects[$entityId])) {
            $this->effects[$entityId] = [];
        }

        $this->effects[$entityId][] = [
            'effect' => $effect,
            'interval' => max(1, $interval),
            'lastTick' => 0
        ];

        $this->ensureTaskRunning();
    }

    public function remove(int $entityId): void {
        unset($this->effects[$entityId]);

        if (empty($this->effects)) {
            $this->stopTask();
        }
    }

    public function removeEffect(int $entityId, int $index): void {
        if (isset($this->effects[$entityId][$index])) {
            unset($this->effects[$entityId][$index]);
            $this->effects[$entityId] = array_values($this->effects[$entityId]);

            if (empty($this->effects[$entityId])) {
                unset($this->effects[$entityId]);
            }
        }

        if (empty($this->effects)) {
            $this->stopTask();
        }
    }

    public function hasEffects(int $entityId): bool {
        return isset($this->effects[$entityId]) && !empty($this->effects[$entityId]);
    }

    public function getEffects(int $entityId): array {
        return $this->effects[$entityId] ?? [];
    }

    public function clearAll(): void {
        $this->effects = [];
        $this->stopTask();
    }

    public function tick(): void {
        $this->tickCounter++;

        foreach ($this->effects as $entityId => $effectList) {
            $entity = EntityLib::get($entityId);

            if ($entity === null || $entity->isClosed()) {
                unset($this->effects[$entityId]);
                continue;
            }

            $position = $entity->getPosition();
            $world = $entity->getWorld();

            foreach ($effectList as $key => $data) {
                $effect = $data['effect'];
                $interval = $data['interval'];
                $lastTick = $data['lastTick'];

                if (($this->tickCounter - $lastTick) >= $interval) {
                    $offset = ($this->tickCounter % 20) / 20;
                    $effect->spawn($world, $position, $offset);
                    $this->effects[$entityId][$key]['lastTick'] = $this->tickCounter;
                }
            }
        }
    }

    private function ensureTaskRunning(): void {
        if ($this->task === null) {
            $this->task = new EffectTask($this);
            EntityLib::getPlugin()->getScheduler()->scheduleRepeatingTask($this->task, 1);
        }
    }

    private function stopTask(): void {
        if ($this->task !== null) {
            $handler = $this->task->getHandler();
            if ($handler !== null) {
                $handler->cancel();
            }
            $this->task = null;
        }
    }

    public function getEffectCount(): int {
        $count = 0;
        foreach ($this->effects as $effectList) {
            $count += count($effectList);
        }
        return $count;
    }

    public function getTickCounter(): int {
        return $this->tickCounter;
    }

    public function isRunning(): bool {
        return $this->task !== null;
    }
}