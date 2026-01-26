<?php

/**
 * InteractionHandler - Manages player clicks on entities
 *
 * This is the system that makes NPCs actually do something when you click them.
 * It handles callbacks, cooldowns, and different interaction types.
 *
 * I wanted to keep this simple - just register a callback and it works.
 * No need to mess with complex event systems or packet listeners.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\interaction;

use pocketmine\player\Player;
use Funaoo\EntityLib\entity\BaseEntity;
use Closure;

/**
 * Handles all entity interactions
 *
 * When a player clicks an entity, this class:
 * 1. Checks if there's a callback registered
 * 2. Checks if the player is on cooldown
 * 3. Executes the callback
 * 4. Starts a new cooldown
 */
class InteractionHandler {

    /** Registered callbacks - entityId => Closure */
    private array $callbacks = [];

    /** Cooldown manager */
    private CooldownManager $cooldownManager;

    /** Default cooldown in seconds */
    private float $defaultCooldown = 0.5;

    public function __construct() {
        $this->cooldownManager = new CooldownManager();
    }

    /**
     * Register a callback for an entity
     *
     * @param int $entityId The entity ID
     * @param Closure $callback function(Player $player, BaseEntity $entity): void
     * @param float|null $cooldown Custom cooldown time (seconds), null = use default
     */
    public function register(int $entityId, Closure $callback, ?float $cooldown = null): void {
        $this->callbacks[$entityId] = [
            'callback' => $callback,
            'cooldown' => $cooldown ?? $this->defaultCooldown
        ];
    }

    /**
     * Unregister a callback
     *
     * @param int $entityId The entity ID
     */
    public function unregister(int $entityId): void {
        unset($this->callbacks[$entityId]);
        $this->cooldownManager->clearEntity($entityId);
    }

    /**
     * Check if an entity has a callback registered
     *
     * @param int $entityId The entity ID
     * @return bool True if callback exists
     */
    public function hasCallback(int $entityId): bool {
        return isset($this->callbacks[$entityId]);
    }

    /**
     * Handle an interaction
     *
     * Called when a player clicks an entity. Checks cooldown and
     * executes the callback if everything is good.
     *
     * @param int $entityId The entity that was clicked
     * @param Player $player The player who clicked
     * @param BaseEntity $entity The entity object
     */
    public function handleInteraction(int $entityId, Player $player, BaseEntity $entity): void {
        // Check if callback exists
        if (!isset($this->callbacks[$entityId])) {
            return;
        }

        $data = $this->callbacks[$entityId];
        $callback = $data['callback'];
        $cooldown = $data['cooldown'];

        // Check cooldown
        if ($this->cooldownManager->isOnCooldown($entityId, $player)) {
            // Player clicked too fast, ignore
            return;
        }

        // Execute callback
        try {
            $callback($player, $entity);
        } catch (\Throwable $e) {
            // Log error but don't crash the server
            \GlobalLogger::get()->logException($e);
        }

        // Start cooldown
        $this->cooldownManager->setCooldown($entityId, $player, $cooldown);
    }

    /**
     * Set the default cooldown time
     *
     * @param float $seconds Cooldown in seconds
     */
    public function setDefaultCooldown(float $seconds): void {
        $this->defaultCooldown = $seconds;
    }

    /**
     * Get the default cooldown time
     *
     * @return float Cooldown in seconds
     */
    public function getDefaultCooldown(): float {
        return $this->defaultCooldown;
    }

    /**
     * Get the cooldown manager
     *
     * @return CooldownManager
     */
    public function getCooldownManager(): CooldownManager {
        return $this->cooldownManager;
    }

    /**
     * Clear all callbacks
     *
     * Useful for cleanup or reloading
     */
    public function clearAll(): void {
        $this->callbacks = [];
        $this->cooldownManager->clearAll();
    }

    /**
     * Get all registered entity IDs
     *
     * @return int[] Array of entity IDs with callbacks
     */
    public function getRegisteredEntities(): array {
        return array_keys($this->callbacks);
    }
}