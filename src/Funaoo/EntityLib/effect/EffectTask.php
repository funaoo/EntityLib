<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\effect;

use pocketmine\scheduler\Task;

/**
 * Task that runs the effect updates
 *
 * This is internal - you don't need to use this directly.
 * The EffectManager creates and manages this task automatically.
 */
class EffectTask extends Task {

    private EffectManager $manager;

    public function __construct(EffectManager $manager) {
        $this->manager = $manager;
    }

    public function onRun(): void {
        $this->manager->tick();
    }
}