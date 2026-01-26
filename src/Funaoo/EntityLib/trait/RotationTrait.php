<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\trait;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

/**
 * RotationTrait - Smooth rotation towards players or positions
 *
 * Provides methods to make entities look at players naturally.
 */
trait RotationTrait {

    protected bool $shouldLookAtPlayers = false;
    protected float $rotationSpeed = 10.0;

    /**
     * Enable/disable looking at players
     */
    public function setLookAtPlayers(bool $enable): void {
        $this->shouldLookAtPlayers = $enable;
    }

    /**
     * Check if entity looks at players
     */
    public function shouldLookAtPlayers(): bool {
        return $this->shouldLookAtPlayers;
    }

    /**
     * Update rotation to look at nearest player
     */
    protected function updatePlayerRotation(): void {
        if (!$this->shouldLookAtPlayers) {
            return;
        }

        $nearestPlayer = $this->findNearestPlayer(8.0);

        if ($nearestPlayer !== null) {
            $this->lookAtEntity($nearestPlayer);
        }
    }

    /**
     * Find nearest player within range
     */
    protected function findNearestPlayer(float $maxDistance): ?Player {
        $nearest = null;
        $nearestDistSq = $maxDistance * $maxDistance;

        foreach ($this->getWorld()->getPlayers() as $player) {
            $distSq = $this->getPosition()->distanceSquared($player->getPosition());

            if ($distSq < $nearestDistSq) {
                $nearestDistSq = $distSq;
                $nearest = $player;
            }
        }

        return $nearest;
    }

    /**
     * Make entity look at another entity
     */
    public function lookAtEntity(Entity $target): void {
        $this->lookAtPosition($target->getPosition()->add(0, $target->getEyeHeight(), 0));
    }

    /**
     * Make entity look at a position
     */
    public function lookAtPosition(Vector3 $target): void {
        $entityPos = $this->getPosition();

        $xDist = $target->x - $entityPos->x;
        $yDist = $target->y - $entityPos->y;
        $zDist = $target->z - $entityPos->z;

        $yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;

        $distance = sqrt($xDist * $xDist + $zDist * $zDist);
        $pitch = -atan2($yDist, $distance) / M_PI * 180;

        $this->setRotation($yaw, $pitch);
    }

    /**
     * Smoothly rotate towards a yaw/pitch
     */
    protected function smoothRotate(float $targetYaw, float $targetPitch): void {
        $currentLoc = $this->getLocation();
        $currentYaw = $currentLoc->yaw;
        $currentPitch = $currentLoc->pitch;

        $yawDiff = $targetYaw - $currentYaw;
        $pitchDiff = $targetPitch - $currentPitch;

        while ($yawDiff > 180) $yawDiff -= 360;
        while ($yawDiff < -180) $yawDiff += 360;

        $newYaw = $currentYaw + ($yawDiff * 0.1);
        $newPitch = $currentPitch + ($pitchDiff * 0.1);

        $this->setRotation($newYaw, $newPitch);
    }
}