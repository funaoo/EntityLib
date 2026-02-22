<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\listener;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\EntityLib;

final class EntityListener implements Listener {

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
        if ($damager instanceof Player) {
            $entity->attack($event);
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void {
        EntityLib::getInteractionHandler()->getCooldownManager()->clearPlayer($event->getPlayer());
    }
}
