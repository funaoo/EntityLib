<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\trait;

use pocketmine\player\Player;

/**
 * InteractableTrait - Handles player interactions
 *
 * Provides cooldown management and interaction tracking per player.
 */
trait InteractableTrait {

    protected array $lastInteraction = [];
    protected float $interactionCooldown = 0.5;

    /**
     * Check if player can interact (respects cooldown)
     */
    protected function canInteract(Player $player): bool {
        $name = $player->getName();
        $now = microtime(true);

        if (!isset($this->lastInteraction[$name])) {
            return true;
        }

        return ($now - $this->lastInteraction[$name]) >= $this->interactionCooldown;
    }

    /**
     * Record an interaction
     */
    protected function recordInteraction(Player $player): void {
        $this->lastInteraction[$player->getName()] = microtime(true);
    }

    /**
     * Set interaction cooldown in seconds
     */
    public function setInteractionCooldown(float $seconds): void {
        $this->interactionCooldown = max(0.0, $seconds);
    }

    /**
     * Get interaction cooldown
     */
    public function getInteractionCooldown(): float {
        return $this->interactionCooldown;
    }

    /**
     * Get remaining cooldown for a player
     */
    public function getRemainingCooldown(Player $player): float {
        $name = $player->getName();

        if (!isset($this->lastInteraction[$name])) {
            return 0.0;
        }

        $elapsed = microtime(true) - $this->lastInteraction[$name];
        $remaining = $this->interactionCooldown - $elapsed;

        return max(0.0, $remaining);
    }

    /**
     * Clear interaction history for a player
     */
    public function clearInteractionHistory(Player $player): void {
        unset($this->lastInteraction[$player->getName()]);
    }

    /**
     * Clear all interaction history
     */
    public function clearAllInteractionHistory(): void {
        $this->lastInteraction = [];
    }
}