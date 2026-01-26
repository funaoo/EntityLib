<?php

/**
 * HumanEntity - Human/Player-like NPCs
 *
 * This is probably what you'll use most. Creates NPCs that look like
 * players - perfect for shop NPCs, quest givers, guards, etc.
 *
 * The cool thing about human entities is they support custom skins,
 * so you can make each NPC look unique.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

/**
 * Human NPC entity
 *
 * These are the most versatile NPCs. They can:
 * - Wear custom skins
 * - Hold items (future feature)
 * - Wear armor (future feature)
 * - Look like any player
 */
class HumanEntity extends BaseEntity {

    /**
     * Get the network type ID
     *
     * This tells the client what kind of entity to render.
     * We're using the human/player entity type.
     */
    public static function getNetworkTypeId(): string {
        return EntityIds::PLAYER;
    }

    /**
     * Get entity type identifier
     */
    public function getType(): string {
        return EntityLib::HUMAN;
    }

    /**
     * Get entity name for display
     */
    public function getName(): string {
        return "Human NPC";
    }

    /**
     * Initialize entity
     *
     * Called when the entity is first created. Good place to set
     * default properties specific to human NPCs.
     */
    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        // Human NPCs should be visible and interactable by default
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(false); // Don't show through walls by default
    }

    /**
     * Custom tick behavior for humans
     *
     * You could add stuff like:
     * - Blinking animations
     * - Idle movements
     * - Breathing effect
     *
     * For now we just use the base behavior.
     */
    protected function entityBaseTick(int $tickDiff = 1): bool {
        return parent::entityBaseTick($tickDiff);
    }

    /**
     * Check if this entity can be renamed with a nametag
     *
     * We don't want players renaming our NPCs, so return false.
     */
    public function canBeRenamed(): bool {
        return false;
    }

    /**
     * Serialize to array with human-specific data
     */
    public function toArray(): array {
        $data = parent::toArray();

        // Add human-specific data here if needed
        // For example: held item, armor, pose, etc.

        return $data;
    }
}