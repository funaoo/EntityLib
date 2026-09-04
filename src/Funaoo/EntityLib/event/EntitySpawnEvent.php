<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use Funaoo\EntityLib\entity\BaseEntity;

final class EntitySpawnEvent extends Event implements Cancellable {
    use CancellableTrait;

    public function __construct(private readonly BaseEntity $entity) {}

    public function getEntity(): BaseEntity {
        return $this->entity;
    }

    public function getEntityType(): string {
        return $this->entity->getType();
    }
}
