<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\trait;

use pocketmine\math\Vector3;

/**
 * ImmobilityTrait - Makes entities completely immobile
 *
 * PM5 removed the setImmobile() method, so we need to handle it ourselves.
 * This trait provides all the functionality needed to keep an entity in place.
 */
trait ImmobilityTrait {

    protected Vector3 $immobilePosition;
    protected bool $isImmobile = true;

    /**
     * Initialize immobility - call this in your entity's constructor
     */
    protected function initImmobility(Vector3 $position): void {
        $this->immobilePosition = $position->asVector3();
    }

    /**
     * Override move to prevent any movement
     */
    public function move(float $dx, float $dy, float $dz): void {
        if ($this->isImmobile) {
            return;
        }
        parent::move($dx, $dy, $dz);
    }

    /**
     * Keep entity at spawn position
     */
    protected function enforceImmobility(): void {
        if (!$this->isImmobile) {
            return;
        }

        $currentPos = $this->getLocation()->asVector3();

        if (!$currentPos->equals($this->immobilePosition)) {
            $this->teleport($this->immobilePosition);
        }
    }

    /**
     * Set if entity is immobile
     */
    public function setImmobile(bool $immobile): void {
        $this->isImmobile = $immobile;
    }

    /**
     * Check if entity is immobile
     */
    public function isImmobile(): bool {
        return $this->isImmobile;
    }

    /**
     * Get the locked position
     */
    public function getImmobilePosition(): Vector3 {
        return $this->immobilePosition;
    }
}