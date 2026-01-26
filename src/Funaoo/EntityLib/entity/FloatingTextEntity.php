<?php

/**
 * FloatingTextEntity - Holographic text displays
 *
 * You know those floating text displays in Among Us lobbies? That's what
 * this is for. Pure text floating in the air - no visible entity body.
 *
 * Technically it's still a human entity, just scaled down to be nearly
 * invisible. The nametag is all you see.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Funaoo\EntityLib\EntityLib;

/**
 * Floating text entity - for holograms and text displays
 *
 * These are perfect for:
 * - Server information displays
 * - Leaderboards
 * - Shop categories
 * - Quest markers
 * - Anything where you just need text floating in space
 */
class FloatingTextEntity extends BaseEntity {

    /**
     * Network type - still a player/human entity
     */
    public static function getNetworkTypeId(): string {
        return EntityIds::PLAYER;
    }

    /**
     * Get entity type identifier
     */
    public function getType(): string {
        return EntityLib::FLOATING_TEXT;
    }

    /**
     * Get entity name
     */
    public function getName(): string {
        return "Floating Text";
    }

    /**
     * Initialize floating text
     *
     * We set some specific defaults here to make it work well as
     * a text display.
     */
    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        // Make the entity basically invisible
        $this->setScale(0.01);

        // But the nametag should always be visible
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);

        // No collision - you should be able to walk through text
        $this->setCanCollideWith(false);

        // Text doesn't need to look at players
        $this->setLookAtPlayers(false);
    }

    /**
     * Update text content
     *
     * Helper method to change the text after spawning.
     * Useful for dynamic displays like player counts or timers.
     */
    public function updateText(string $text): void {
        $this->setNameTag($text);
    }

    /**
     * Floating text should never be collideable
     *
     * Even if someone tries to enable collision, we override it.
     */
    public function setCanCollideWith(bool $canCollide): void {
        parent::setCanCollideWith(false);
    }

    /**
     * Floating text entities don't need to tick as often
     *
     * They're static displays, so we can save performance by
     * skipping some tick operations.
     */
    protected function entityBaseTick(int $tickDiff = 1): bool {
        // We don't need the rotation updates that BaseEntity does
        // Just keep the entity at its spawn position
        if (!$this->getLocation()->asVector3()->equals($this->spawnPosition)) {
            $this->teleport($this->spawnPosition);
        }

        return parent::entityBaseTick($tickDiff);
    }

    /**
     * Floating text shouldn't rotate
     */
    public function setLookAtPlayers(bool $enable): void {
        // Ignore - floating text never rotates
        parent::setLookAtPlayers(false);
    }

    /**
     * Serialize with text-specific settings
     */
    public function toArray(): array {
        $data = parent::toArray();

        // Floating text always has these properties
        $data['alwaysFloating'] = true;
        $data['noCollision'] = true;

        return $data;
    }
}