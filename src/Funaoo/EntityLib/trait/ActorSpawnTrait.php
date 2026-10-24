<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\trait;

use pocketmine\entity\Attribute;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;

/**
 * Makes a Human-based entity appear to clients as a regular actor (pig, zombie, villager...)
 * instead of a player. Human::sendSpawnPacket() always sends AddPlayerPacket, so we replace it
 * with the same AddActorPacket that Entity::sendSpawnPacket() uses.
 *
 * Used only by classes that extend Human.
 */
trait ActorSpawnTrait {

    /** Network actor id sent to the client, e.g. EntityIds::PIG. */
    abstract protected function getActorTypeId(): string;

    protected function sendSpawnPacket(Player $player): void {
        $player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
            $this->getId(),
            $this->getId(),
            $this->getActorTypeId(),
            $this->location->asVector3(),
            $this->getMotion(),
            $this->location->pitch,
            $this->location->yaw,
            $this->location->yaw,
            $this->location->yaw,
            array_map(
                static fn(Attribute $attr): NetworkAttribute =>
                    new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue()),
                $this->attributeMap->getAll()
            ),
            $this->getAllNetworkData(),
            new PropertySyncData([], []),
            []
        ));
    }

    /** Human adds +1.621 (eye height) because players are positioned at the eyes; actors are not. */
    public function getOffsetPosition(Vector3 $vector3): Vector3 {
        return $vector3;
    }
}
