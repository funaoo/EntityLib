<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Closure;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use Funaoo\EntityLib\entity\BaseEntity;
use Funaoo\EntityLib\entity\FloatingTextEntity;
use Funaoo\EntityLib\EntityLib;
use Funaoo\EntityLib\skin\DefaultSkins;

final class Hologram {


    public const LINE_SPACING = 0.25;


    public const HEAD_OFFSET = 0.25;


    private array   $lines       = [];
    private int     $updateRate  = 20;
    private float   $lineSpacing = self::LINE_SPACING;
    private float   $headOffset  = self::HEAD_OFFSET;


    private ?int     $anchorEntityId = null;
    private ?Vector3 $fixedBase      = null;
    private ?World   $world          = null;


    private ?Vector3 $lastBase = null;


    private array $entities = [];
    private bool  $spawned  = false;



    public static function above(BaseEntity $entity): self {
        $h                 = new self();
        $h->anchorEntityId = $entity->getId();
        $h->world          = $entity->getWorld();
        return $h;
    }

    public static function at(Vector3 $position, World $world): self {
        $h            = new self();
        $h->fixedBase = $position->asVector3();
        $h->world     = $world;
        return $h;
    }





    public function line(string|Closure $text, ?float $spacing = null): self {
        $this->lines[] = $text instanceof Closure
            ? new HologramLine('', $text, $spacing)
            : new HologramLine($text, null, $spacing);
        return $this;
    }


    public function setLines(array $lines): self {
        $this->lines = [];
        foreach ($lines as $l) $this->line($l);
        return $this;
    }


    public function setUpdateRate(int $ticks): self {
        $this->updateRate = max(1, $ticks);
        return $this;
    }



    public function setLineSpacing(float $spacing): self {
        $this->lineSpacing = max(0.0, $spacing);
        return $this;
    }



    public function setHeadOffset(float $offset): self {
        $this->headOffset = max(0.0, $offset);
        return $this;
    }



    public function spawn(): self {
        if ($this->spawned || $this->lines === []) return $this;

        $base           = $this->computeBase();
        $this->lastBase = $base;
        $skin           = DefaultSkins::blank();

        foreach ($this->lines as $i => $line) {
            $pos      = new Vector3($base->x, $base->y + $this->yOffsetFor($i), $base->z);
            $location = Location::fromObject($pos, $this->world);

            $nbt = BaseEntity::createSpawnNBT(
                location:             $location,
                name:                 $line->resolve(),
                scale:                0.01,
                nameTagVisible:       true,
                nameTagAlwaysVisible: true,
                lookAtPlayers:        false,
                canCollide:           false,
            );

            $entity = new FloatingTextEntity($location, $skin, $nbt);
            $entity->spawnToAll();
            EntityLib::registerEntity($entity);
            $this->entities[$i] = $entity;
        }

        EntityLib::getNametagManager()->registerHologram($this);
        $this->spawned = true;
        return $this;
    }

    public function despawn(): void {
        foreach ($this->entities as $entity) {
            if (!$entity->isClosed()) EntityLib::remove($entity->getId());
        }
        $this->entities = [];
        $this->lastBase = null;
        $this->spawned  = false;
        EntityLib::getNametagManager()->unregisterHologram($this);
    }



    public function tick(): void {
        if (!$this->spawned) return;

        $base  = $this->computeBase();
        $moved = $this->lastBase === null || !$this->baseEquals($this->lastBase, $base);

        foreach ($this->entities as $i => $entity) {
            if ($entity->isClosed()) continue;

            $entity->updateText($this->lines[$i]->resolve());

            if ($moved) {
                $target = new Vector3($base->x, $base->y + $this->yOffsetFor($i), $base->z);
                $entity->teleport($target);
            }
        }

        if ($moved) $this->lastBase = $base;
    }





    public function updateLine(int $index, string|Closure $text): void {
        if (!isset($this->lines[$index])) return;

        $line = $this->lines[$index];
        if ($text instanceof Closure) {
            $line->setResolver($text);
        } else {
            $line->setText($text);
            $line->setResolver(null);
        }

        if (isset($this->entities[$index]) && !$this->entities[$index]->isClosed()) {
            $this->entities[$index]->updateText($line->resolve());
        }
    }



    public function getUpdateRate(): int    { return $this->updateRate; }
    public function getLineSpacing(): float { return $this->lineSpacing; }
    public function getHeadOffset(): float  { return $this->headOffset; }
    public function isSpawned(): bool       { return $this->spawned; }
    public function getAnchorId(): ?int     { return $this->anchorEntityId; }
    public function isAnchored(): bool      { return $this->anchorEntityId !== null; }

    public function getEntities(): array    { return $this->entities; }

    public function getLines(): array       { return $this->lines; }





    private function yOffsetFor(int $i): float {
        $count  = count($this->lines);
        $offset = 0.0;



        for ($j = $count - 1; $j > $i; $j--) {
            $gap     = $this->lines[$j - 1]->getSpacingAfter() ?? $this->lineSpacing;
            $offset += $gap;
        }

        return $offset;
    }

    private function computeBase(): Vector3 {
        if ($this->anchorEntityId !== null) {
            $npc = EntityLib::get($this->anchorEntityId);
            if ($npc !== null && !$npc->isClosed()) {
                $pos = $npc->getPosition();
                return new Vector3(
                    $pos->x,
                    $pos->y + $npc->getEyeHeight() + $this->headOffset,
                    $pos->z,
                );
            }
        }
        return $this->fixedBase ?? new Vector3(0, 64, 0);
    }

    private function baseEquals(Vector3 $a, Vector3 $b): bool {
        return abs($a->x - $b->x) < 0.005
            && abs($a->y - $b->y) < 0.005
            && abs($a->z - $b->z) < 0.005;
    }
}
