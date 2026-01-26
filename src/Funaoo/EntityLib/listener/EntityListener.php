<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\listener;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use Funaoo\EntityLib\EntityLib;
use Funaoo\EntityLib\entity\BaseEntity;

class EntityListener implements Listener {

    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();

        if (!$entity instanceof BaseEntity) {
            return;
        }

        $event->cancel();

        if (!$event instanceof EntityDamageByEntityEvent) {
            return;
        }

        $damager = $event->getDamager();

        if (!$damager instanceof Player) {
            return;
        }

        $entity->attack($event);
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        EntityLib::getInteractionHandler()
            ->getCooldownManager()
            ->clearPlayer($player);
    }

    public function cleanupCooldowns(): void {
        EntityLib::getInteractionHandler()
            ->getCooldownManager()
            ->cleanupExpired();
    }
}