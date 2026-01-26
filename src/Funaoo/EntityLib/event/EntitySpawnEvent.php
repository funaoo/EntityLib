<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use Funaoo\EntityLib\entity\BaseEntity;

/**
 * EntitySpawnEvent - Called when an EntityLib entity spawns
 *
 * This event is cancellable - cancel it to prevent the entity from spawning.
 */
class EntitySpawnEvent extends Event implements Cancellable {
    use CancellableTrait;

    private BaseEntity $entity;

    public function __construct(BaseEntity $entity) {
        $this->entity = $entity;
    }

    /**
     * Get the entity being spawned
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
}