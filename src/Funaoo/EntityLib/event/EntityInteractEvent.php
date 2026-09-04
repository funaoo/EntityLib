<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\interaction\InteractionType;

final class EntityInteractEvent extends Event implements Cancellable {
    use CancellableTrait;

    private string $interactionType;

    public function __construct(
        private readonly Player     $player,
        private readonly BaseEntity $entity,
        string $interactionType = InteractionType::TAP,
    ) {
        $this->interactionType = $interactionType;
    }

    public function getPlayer(): Player        { return $this->player; }
    public function getEntity(): BaseEntity    { return $this->entity; }
    public function getEntityType(): string    { return $this->entity->getType(); }
    public function getInteractionType(): string { return $this->interactionType; }

    public function setInteractionType(string $type): void {
        $this->interactionType = $type;
    }
}
