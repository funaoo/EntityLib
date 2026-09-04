<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\trait;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

trait RotationTrait {

    protected bool $shouldLookAtPlayers = false;

    public function setLookAtPlayers(bool $enable): void {
        $this->shouldLookAtPlayers = $enable;
    }

    public function shouldLookAtPlayers(): bool {
        return $this->shouldLookAtPlayers;
    }

    protected function updatePlayerRotation(): void {
        if (!$this->shouldLookAtPlayers) {
            return;
        }
        $target = $this->findNearestPlayer(8.0);
        if ($target !== null) {
            $this->lookAtEntity($target);
        }
    }

    protected function findNearestPlayer(float $maxDistance): ?Player {
        $nearest   = null;
        $threshold = $maxDistance ** 2;
        foreach ($this->getWorld()->getPlayers() as $player) {
            $dsq = $this->getPosition()->distanceSquared($player->getPosition());
            if ($dsq < $threshold) {
                $threshold = $dsq;
                $nearest   = $player;
            }
        }
        return $nearest;
    }

    public function lookAtEntity(Entity $target): void {
        $this->lookAtPosition($target->getPosition()->add(0.0, $target->getEyeHeight(), 0.0));
    }

    public function lookAtPosition(Vector3 $target): void {
        $self  = $this->getPosition()->add(0.0, $this->getEyeHeight(), 0.0);
        $dx    = $target->x - $self->x;
        $dy    = $target->y - $self->y;
        $dz    = $target->z - $self->z;
        $yaw   = (float)(atan2(-$dx, $dz) / M_PI * 180.0);
        $flat  = sqrt($dx ** 2 + $dz ** 2);
        $pitch = (float)(-atan2($dy, $flat) / M_PI * 180.0);
        $this->setRotation($yaw, $pitch);
        $this->broadcastMovement(true);
    }
}
