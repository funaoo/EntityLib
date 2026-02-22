<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use pocketmine\scheduler\Task;

final class NametagUpdater extends Task {

    public function __construct(private readonly NametagManager $manager) {}

    public function onRun(): void {
        $this->manager->updateAll();
    }
}
