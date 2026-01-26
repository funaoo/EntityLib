<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;
use Funaoo\EntityLib\entity\BaseEntity;

/**
 * EntityInteractEvent - Called when a player interacts with an EntityLib entity
 *
 * This event is cancellable - cancel it to prevent the interaction callback.
 */
class EntityInteractEvent extends Event implements Cancellable {
    use CancellableTrait;

    private Player $player;
    private BaseEntity $entity;
    private string $interactionType;

    public function __construct(Player $player, BaseEntity $entity, string $interactionType = "tap") {
        $this->player = $player;
        $this->entity = $entity;
        $this->interactionType = $interactionType;
    }

    /**
     * Get the player who interacted
     */
    public function getPlayer(): Player {
        return $this->player;
    }

    /**
     * Get the entity that was interacted with
     */
    public function getEntity(): BaseEntity {
        return $this->entity;
    }

    /**
     * Get entity type
     */
    public function getEntityType(): string {
        return $this->entity->getType();
    }

    /**
     * Get interaction type
     */
    public function getInteractionType(): string {
        return $this->interactionType;
    }

    /**
     * Set interaction type
     */
    public function setInteractionType(string $type): void {
        $this->interactionType = $type;
    }
}