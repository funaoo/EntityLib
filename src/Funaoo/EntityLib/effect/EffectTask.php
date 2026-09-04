<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\effect;

use pocketmine\scheduler\Task;

final class EffectTask extends Task {

    public function __construct(private readonly EffectManager $manager) {}

    public function onRun(): void {
        $this->manager->tick();
    }
}
