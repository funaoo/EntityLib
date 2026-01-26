<?php

/**
 * CooldownManager - Prevents spam clicking on NPCs
 *
 * Nobody likes spam clickers. This manager tracks when players last
 * interacted with entities and enforces cooldowns.
 *
 * I've seen servers lag because players spam-click NPCs. This prevents
 * that by simply ignoring clicks that come too fast.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\interaction;

use pocketmine\player\Player;

/**
 * Manages cooldowns for entity interactions
 *
 * Uses microtime for precision - we want accurate cooldowns down to
 * the millisecond. No one likes waiting an extra second because of
 * rounding errors.
 */
class CooldownManager {

    /**
     * Cooldown storage
     * Format: [entityId][playerName] = expireTime
     */
    private array $cooldowns = [];

    /**
     * Set a cooldown for a player on an entity
     *
     * @param int $entityId The entity ID
     * @param Player $player The player
     * @param float $seconds Cooldown duration in seconds
     */
    public function setCooldown(int $entityId, Player $player, float $seconds): void {
        $playerName = strtolower($player->getName());
        $expireTime = microtime(true) + $seconds;

        if (!isset($this->cooldowns[$entityId])) {
            $this->cooldowns[$entityId] = [];
        }

        $this->cooldowns[$entityId][$playerName] = $expireTime;
    }

    /**
     * Check if a player is on cooldown for an entity
     *
     * @param int $entityId The entity ID
     * @param Player $player The player to check
     * @return bool True if on cooldown, false if ready
     */
    public function isOnCooldown(int $entityId, Player $player): bool {
        $playerName = strtolower($player->getName());

        if (!isset($this->cooldowns[$entityId][$playerName])) {
            return false; // No cooldown = ready to interact
        }

        $expireTime = $this->cooldowns[$entityId][$playerName];
        $now = microtime(true);

        if ($now >= $expireTime) {
            // Cooldown expired, clean it up
            unset($this->cooldowns[$entityId][$playerName]);
            return false;
        }

        return true; // Still on cooldown
    }

    /**
     * Get remaining cooldown time for a player
     *
     * @param int $entityId The entity ID
     * @param Player $player The player
     * @return float Seconds remaining, 0 if no cooldown
     */
    public function getRemainingTime(int $entityId, Player $player): float {
        $playerName = strtolower($player->getName());

        if (!isset($this->cooldowns[$entityId][$playerName])) {
            return 0.0;
        }

        $expireTime = $this->cooldowns[$entityId][$playerName];
        $remaining = $expireTime - microtime(true);

        return max(0.0, $remaining);
    }

    /**
     * Clear cooldown for a specific player and entity
     *
     * @param int $entityId The entity ID
     * @param Player $player The player
     */
    public function clearCooldown(int $entityId, Player $player): void {
        $playerName = strtolower($player->getName());
        unset($this->cooldowns[$entityId][$playerName]);
    }

    /**
     * Clear all cooldowns for an entity
     *
     * Useful when removing an entity
     *
     * @param int $entityId The entity ID
     */
    public function clearEntity(int $entityId): void {
        unset($this->cooldowns[$entityId]);
    }

    /**
     * Clear all cooldowns for a player
     *
     * Good to call when a player leaves the server
     *
     * @param Player $player The player
     */
    public function clearPlayer(Player $player): void {
        $playerName = strtolower($player->getName());

        foreach ($this->cooldowns as $entityId => $players) {
            unset($this->cooldowns[$entityId][$playerName]);

            // Clean up empty arrays
            if (empty($this->cooldowns[$entityId])) {
                unset($this->cooldowns[$entityId]);
            }
        }
    }

    /**
     * Clear all cooldowns
     *
     * Nuclear option - use when reloading or shutting down
     */
    public function clearAll(): void {
        $this->cooldowns = [];
    }

    /**
     * Clean up expired cooldowns
     *
     * Call this periodically (like every 30 seconds) to prevent
     * the cooldown array from growing forever. Memory management!
     */
    public function cleanupExpired(): void {
        $now = microtime(true);

        foreach ($this->cooldowns as $entityId => $players) {
            foreach ($players as $playerName => $expireTime) {
                if ($now >= $expireTime) {
                    unset($this->cooldowns[$entityId][$playerName]);
                }
            }

            // Remove empty entity arrays
            if (empty($this->cooldowns[$entityId])) {
                unset($this->cooldowns[$entityId]);
            }
        }
    }

    /**
     * Get all active cooldowns for debugging
     *
     * Returns: [entityId => [playerName => timeRemaining]]
     *
     * @return array Active cooldowns
     */
    public function getActiveCooldowns(): array {
        $now = microtime(true);
        $active = [];

        foreach ($this->cooldowns as $entityId => $players) {
            foreach ($players as $playerName => $expireTime) {
                $remaining = $expireTime - $now;

                if ($remaining > 0) {
                    if (!isset($active[$entityId])) {
                        $active[$entityId] = [];
                    }
                    $active[$entityId][$playerName] = round($remaining, 2);
                }
            }
        }

        return $active;
    }

    /**
     * Get cooldown count for statistics
     *
     * @return int Number of active cooldowns
     */
    public function getCount(): int {
        $count = 0;

        foreach ($this->cooldowns as $players) {
            $count += count($players);
        }

        return $count;
    }
}