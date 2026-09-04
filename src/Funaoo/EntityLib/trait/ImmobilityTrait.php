<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\trait;

use pocketmine\math\Vector3;

trait ImmobilityTrait {

    protected bool $isImmobile = true;
    protected Vector3 $immobilePosition;

    protected function initImmobility(Vector3 $position): void {
        $this->immobilePosition = $position->asVector3();
    }

    public function move(float $dx, float $dy, float $dz): void {
        if ($this->isImmobile) {
            return;
        }
        parent::move($dx, $dy, $dz);
    }

    protected function enforceImmobility(): void {
        if ($this->isImmobile && !$this->getLocation()->asVector3()->equals($this->immobilePosition)) {
            $this->teleport($this->immobilePosition);
        }
    }

    public function setImmobile(bool $immobile): void {
        $this->isImmobile = $immobile;
    }

    public function isImmobile(): bool {
        return $this->isImmobile;
    }

    public function getImmobilePosition(): Vector3 {
        return $this->immobilePosition;
    }
}
