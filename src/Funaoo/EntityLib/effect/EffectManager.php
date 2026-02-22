<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\effect;

use Funaoo\EntityLib\EntityLib;

final class EffectManager {

    private array $effects = [];
    private ?EffectTask $task = null;
    private int $tick = 0;

    public function addParticle(
        int    $entityId,
        string $type,
        int    $interval = 20,
        string $pattern  = ParticleEffect::PATTERN_CIRCLE,
        int    $density  = 5,
        float  $radius   = 1.0,
        float  $height   = 2.0,
    ): void {
        $this->effects[$entityId][] = [
            'effect'   => new ParticleEffect($type, $pattern, $density, $radius, $height),
            'interval' => max(1, $interval),
            'lastTick' => 0,
        ];
        $this->ensureRunning();
    }

    public function remove(int $entityId): void {
        unset($this->effects[$entityId]);
        if ($this->effects === []) {
            $this->stopTask();
        }
    }

    public function removeEffect(int $entityId, int $index): void {
        unset($this->effects[$entityId][$index]);
        $this->effects[$entityId] = array_values($this->effects[$entityId] ?? []);
        if ($this->effects[$entityId] === []) {
            unset($this->effects[$entityId]);
        }
        if ($this->effects === []) {
            $this->stopTask();
        }
    }

    public function hasEffects(int $entityId): bool {
        return isset($this->effects[$entityId]) && $this->effects[$entityId] !== [];
    }

    public function getEffects(int $entityId): array {
        return $this->effects[$entityId] ?? [];
    }

    public function clearAll(): void {
        $this->effects = [];
        $this->stopTask();
    }

    public function tick(): void {
        $this->tick++;
        foreach ($this->effects as $entityId => &$effectList) {
            $entity = EntityLib::get($entityId);
            if ($entity === null || $entity->isClosed()) {
                unset($this->effects[$entityId]);
                continue;
            }
            $pos   = $entity->getPosition();
            $world = $entity->getWorld();
            foreach ($effectList as &$data) {
                if (($this->tick - $data['lastTick']) >= $data['interval']) {
                    $offset = ($this->tick % 20) / 20;
                    $data['effect']->spawn($world, $pos, $offset);
                    $data['lastTick'] = $this->tick;
                }
            }
            unset($data);
        }
        unset($effectList);
    }

    public function isRunning(): bool {
        return $this->task !== null;
    }

    private function ensureRunning(): void {
        if ($this->task === null) {
            $this->task = new EffectTask($this);
            EntityLib::getPlugin()->getScheduler()->scheduleRepeatingTask($this->task, 1);
        }
    }

    private function stopTask(): void {
        if ($this->task !== null) {
            $this->task->getHandler()?->cancel();
            $this->task = null;
        }
    }
}
