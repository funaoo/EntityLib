<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\trait;

use pocketmine\player\Player;

trait InteractableTrait {

    private array $lastInteraction  = [];
    private float $interactionCooldown = 0.5;

    protected function canInteract(Player $player): bool {
        $name = $player->getName();
        if (!isset($this->lastInteraction[$name])) {
            return true;
        }
        return (microtime(true) - $this->lastInteraction[$name]) >= $this->interactionCooldown;
    }

    protected function recordInteraction(Player $player): void {
        $this->lastInteraction[$player->getName()] = microtime(true);
    }

    public function setInteractionCooldown(float $seconds): void {
        $this->interactionCooldown = max(0.0, $seconds);
    }

    public function getInteractionCooldown(): float {
        return $this->interactionCooldown;
    }

    public function getRemainingCooldown(Player $player): float {
        $name = $player->getName();
        if (!isset($this->lastInteraction[$name])) {
            return 0.0;
        }
        return max(0.0, $this->interactionCooldown - (microtime(true) - $this->lastInteraction[$name]));
    }

    public function clearInteractionHistory(Player $player): void {
        unset($this->lastInteraction[$player->getName()]);
    }

    public function clearAllInteractionHistory(): void {
        $this->lastInteraction = [];
    }
}
