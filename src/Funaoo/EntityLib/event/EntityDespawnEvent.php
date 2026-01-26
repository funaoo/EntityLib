<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\event;

use pocketmine\event\Event;
use Funaoo\EntityLib\entity\BaseEntity;

/**
 * EntityDespawnEvent - Called when an EntityLib entity despawns
 *
 * This event is not cancellable since the entity is already being removed.
 */
class EntityDespawnEvent extends Event {

    private BaseEntity $entity;
    private string $reason;

    public function __construct(BaseEntity $entity, string $reason = "unknown") {
        $this->entity = $entity;
        $this->reason = $reason;
    }

    /**
     * Get the entity being despawned
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
     * Get despawn reason
     */
    public function getReason(): string {
        return $this->reason;
    }
}