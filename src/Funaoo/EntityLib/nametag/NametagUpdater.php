<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use pocketmine\scheduler\Task;

/**
 * NametagUpdater - Task that updates dynamic nametags
 *
 * Runs every second to update all dynamic nametags.
 */
class NametagUpdater extends Task {

    private NametagManager $manager;

    public function __construct(NametagManager $manager) {
        $this->manager = $manager;
    }

    public function onRun(): void {
        $this->manager->updateAll();
    }
}