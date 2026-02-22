<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\interaction;

use pocketmine\player\Player;

final class CooldownManager {

    private array $cooldowns = [];

    public function set(int $entityId, Player $player, float $seconds): void {
        $this->cooldowns[$entityId][strtolower($player->getName())] = microtime(true) + $seconds;
    }

    public function isOnCooldown(int $entityId, Player $player): bool {
        $key = strtolower($player->getName());
        if (!isset($this->cooldowns[$entityId][$key])) {
            return false;
        }
        if (microtime(true) >= $this->cooldowns[$entityId][$key]) {
            unset($this->cooldowns[$entityId][$key]);
            return false;
        }
        return true;
    }

    public function getRemaining(int $entityId, Player $player): float {
        $key = strtolower($player->getName());
        if (!isset($this->cooldowns[$entityId][$key])) {
            return 0.0;
        }
        return max(0.0, $this->cooldowns[$entityId][$key] - microtime(true));
    }

    public function clearEntity(int $entityId): void {
        unset($this->cooldowns[$entityId]);
    }

    public function clearPlayer(Player $player): void {
        $key = strtolower($player->getName());
        foreach ($this->cooldowns as $eid => &$players) {
            unset($players[$key]);
            if ($players === []) {
                unset($this->cooldowns[$eid]);
            }
        }
        unset($players);
    }

    public function clearAll(): void {
        $this->cooldowns = [];
    }

    public function purgeExpired(): void {
        $now = microtime(true);
        foreach ($this->cooldowns as $eid => &$players) {
            foreach ($players as $name => $expire) {
                if ($now >= $expire) {
                    unset($players[$name]);
                }
            }
            if ($players === []) {
                unset($this->cooldowns[$eid]);
            }
        }
        unset($players);
    }
}
