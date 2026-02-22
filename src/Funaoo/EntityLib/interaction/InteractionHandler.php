<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\interaction;

use Closure;
use pocketmine\player\Player;
use Funaoo\EntityLib\entity\BaseEntity;

final class InteractionHandler {

    private array $callbacks = [];
    private float $defaultCooldown = 0.5;
    private CooldownManager $cooldowns;

    public function __construct() {
        $this->cooldowns = new CooldownManager();
    }

    public function register(int $entityId, Closure $callback, ?float $cooldown = null): void {
        $this->callbacks[$entityId] = [
            'callback' => $callback,
            'cooldown' => $cooldown ?? $this->defaultCooldown,
        ];
    }

    public function unregister(int $entityId): void {
        unset($this->callbacks[$entityId]);
        $this->cooldowns->clearEntity($entityId);
    }

    public function hasCallback(int $entityId): bool {
        return isset($this->callbacks[$entityId]);
    }

    public function handleInteraction(int $entityId, Player $player, BaseEntity $entity): void {
        if (!isset($this->callbacks[$entityId])) {
            return;
        }
        if ($this->cooldowns->isOnCooldown($entityId, $player)) {
            return;
        }
        $data = $this->callbacks[$entityId];
        try {
            ($data['callback'])($player, $entity);
        } catch (\Throwable $e) {
            \GlobalLogger::get()->logException($e);
        }
        $this->cooldowns->set($entityId, $player, $data['cooldown']);
    }

    public function setDefaultCooldown(float $seconds): void {
        $this->defaultCooldown = max(0.0, $seconds);
    }

    public function getDefaultCooldown(): float {
        return $this->defaultCooldown;
    }

    public function getCooldownManager(): CooldownManager {
        return $this->cooldowns;
    }

    public function clearAll(): void {
        $this->callbacks = [];
        $this->cooldowns->clearAll();
    }

    public function getRegisteredEntityIds(): array {
        return array_keys($this->callbacks);
    }
}
