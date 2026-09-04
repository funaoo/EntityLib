<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\event;

use pocketmine\event\Event;
use Funaoo\EntityLib\entity\BaseEntity;

final class EntityDespawnEvent extends Event {

    public function __construct(
        private readonly BaseEntity $entity,
        private readonly string $reason = 'unknown',
    ) {}

    public function getEntity(): BaseEntity {
        return $this->entity;
    }

    public function getEntityType(): string {
        return $this->entity->getType();
    }

    public function getReason(): string {
        return $this->reason;
    }
}
