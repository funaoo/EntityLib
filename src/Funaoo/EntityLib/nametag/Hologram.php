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

/**
 * Multi-line hologram. Two modes:
 *
 *  ┌─ ANCHORED (above NPC) ──────────────────────────────────────────────────┐
 *  │  Hologram::above($npc)                                                  │
 *  │  The last line sits HEAD_OFFSET (0.25) above the NPC head at all times. │
 *  │  The hologram follows the NPC every tick automatically.                 │
 *  └─────────────────────────────────────────────────────────────────────────┘
 *
 *  ┌─ FREE-FLOATING (fixed position, no NPC needed) ─────────────────────────┐
 *  │  Hologram::at(new Vector3(x, y, z), $world)                             │
 *  │  Stays at the given position. The last line is placed at that Y.        │
 *  └─────────────────────────────────────────────────────────────────────────┘
 *
 *  ANCHOR RULE (both modes):
 *    lines[count-1] = anchor (lowest, never moves down)
 *    lines[0]       = top    (grows upward as you add lines)
 *
 *  PER-LINE SPACING:
 *    Each line can override the global lineSpacing for the gap ABOVE itself.
 *    Via HologramBuilder: ->line('text')->lineSpacing(0.4)
 *    Via Hologram directly: ->line('text', spacing: 0.4)
 *
 *  Example:
 *    Hologram::above($npc)
 *        ->line('§c§lHCF MAP 1')
 *        ->line('§71.21.100',        spacing: 0.15)   // tighter under title
 *        ->line('§aEstado: §2Online', spacing: 0.30)
 *        ->line(fn() => '§eJugadores: §6' . count($server->getOnlinePlayers()), spacing: 0.25)
 *        ->line('§aClic para ingresar')               // anchor
 *        ->spawn();
 */
final class Hologram {

    /** Default vertical gap between consecutive lines in blocks. */
    public const LINE_SPACING = 0.25;

    /** Default gap between the anchor line and the NPC's head. */
    public const HEAD_OFFSET = 0.25;

    /** @var HologramLine[] index 0 = top, index count-1 = anchor */
    private array   $lines       = [];
    private int     $updateRate  = 20;
    private float   $lineSpacing = self::LINE_SPACING;
    private float   $headOffset  = self::HEAD_OFFSET;

    // ── Anchor ────────────────────────────────────────────────────────────────
    private ?int     $anchorEntityId = null;
    private ?Vector3 $fixedBase      = null;
    private ?World   $world          = null;

    // ── Runtime ───────────────────────────────────────────────────────────────
    private ?Vector3 $lastBase = null;

    /** @var FloatingTextEntity[] same indexing as $lines */
    private array $entities = [];
    private bool  $spawned  = false;

    // ── Factories ─────────────────────────────────────────────────────────────

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

    // ── Builder ───────────────────────────────────────────────────────────────

    /**
     * Appends a line (top → bottom order).
     * The LAST line added is always the anchor — lowest point, never moves.
     *
     * @param string|Closure $text    Static string or fn(HologramLine): string
     * @param float|null     $spacing Per-line vertical gap ABOVE this line.
     *                                null = use global lineSpacing.
     */
    public function line(string|Closure $text, ?float $spacing = null): self {
        $this->lines[] = $text instanceof Closure
            ? new HologramLine('', $text, $spacing)
            : new HologramLine($text, null, $spacing);
        return $this;
    }

    /** Replace all lines at once (clears existing). */
    public function setLines(array $lines): self {
        $this->lines = [];
        foreach ($lines as $l) $this->line($l);
        return $this;
    }

    /** Ticks between text refresh for dynamic lines. Default: 20 (1 second). */
    public function setUpdateRate(int $ticks): self {
        $this->updateRate = max(1, $ticks);
        return $this;
    }

    /**
     * Global vertical gap between consecutive lines in blocks.
     * Default: 0.25 — individual lines can override this via line(..., spacing: X).
     */
    public function setLineSpacing(float $spacing): self {
        $this->lineSpacing = max(0.0, $spacing);
        return $this;
    }

    /**
     * Gap between the anchor line (last line) and the top of the NPC's head.
     * Default: 0.25. Only applies when anchored to an NPC.
     */
    public function setHeadOffset(float $offset): self {
        $this->headOffset = max(0.0, $offset);
        return $this;
    }

    // ── Spawn / Despawn ───────────────────────────────────────────────────────

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

    // ── Tick (called by NametagManager) ───────────────────────────────────────

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

    // ── Live mutations ────────────────────────────────────────────────────────

    /**
     * Updates a single line's text or resolver without re-spawning.
     * Index 0 = top line, index count-1 = anchor.
     */
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

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getUpdateRate(): int    { return $this->updateRate; }
    public function getLineSpacing(): float { return $this->lineSpacing; }
    public function getHeadOffset(): float  { return $this->headOffset; }
    public function isSpawned(): bool       { return $this->spawned; }
    public function getAnchorId(): ?int     { return $this->anchorEntityId; }
    public function isAnchored(): bool      { return $this->anchorEntityId !== null; }
    /** @return FloatingTextEntity[] */
    public function getEntities(): array    { return $this->entities; }
    /** @return HologramLine[] */
    public function getLines(): array       { return $this->lines; }

    // ── Internal ──────────────────────────────────────────────────────────────

    /**
     * Y offset of line $i relative to the anchor (base).
     *
     * Spacing between line[$i] and line[$i+1] is resolved as:
     *   line[$i+1]->spacingAfter  ?? $this->lineSpacing   (per-line override first)
     *
     * Stack grows upward: lines[count-1] = 0 (anchor), lines[0] = highest.
     */
    private function yOffsetFor(int $i): float {
        $count  = count($this->lines);
        $offset = 0.0;

        // Accumulate spacing from anchor (count-1) up to line $i
        // Each pair (j, j-1): gap above line[j-1] = line[j-1]->spacingAfter ?? global
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
