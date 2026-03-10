<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Closure;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use Funaoo\EntityLib\entity\BaseEntity;

/**
 * Fluent builder for Holograms.
 *
 * ┌─ QUICK USAGE ──────────────────────────────────────────────────────────────┐
 * │                                                                             │
 * │  HologramBuilder::above($npc)                                              │
 * │      ->line('§c§lHCF MAP 1')                                               │
 * │      ->lineSpacing(0.10)          // gap above the NEXT line only          │
 * │      ->line('§71.21.100.1')                                                │
 * │      ->lineSpacing(0.30)                                                   │
 * │      ->line('§aEstado: §2Online')                                          │
 * │      ->lineSpacing(0.25)                                                   │
 * │      ->line(fn() => '§eJugadores: §6' . count($server->getOnlinePlayers()))│
 * │      ->lineSpacing(0.25)                                                   │
 * │      ->line('§aClic para ingresar')   // anchor — always closest to head   │
 * │      ->headOffset(0.25)           // NPC head → anchor gap                 │
 * │      ->updateEvery(20)                                                      │
 * │      ->spawn();                                                             │
 * │                                                                             │
 * │  Rules:                                                                     │
 * │    ->lineSpacing(X) after ->line() sets the gap ABOVE THAT line.           │
 * │    A ->lineSpacing(X) without a preceding ->line() sets the GLOBAL default.│
 * └─────────────────────────────────────────────────────────────────────────────┘
 */
final class HologramBuilder {

    /** @var array{text: string|Closure, spacing: float|null}[] */
    private array   $lines        = [];
    private int     $updateRate   = 20;
    private float   $globalSpacing = Hologram::LINE_SPACING;
    private float   $headOffset   = Hologram::HEAD_OFFSET;
    private ?int    $anchorId     = null;
    private ?Vector3 $position    = null;
    private ?World  $world        = null;

    /** Pending per-line spacing set by ->lineSpacing() before the next ->line(). */
    private ?float $pendingSpacing = null;

    private function __construct() {}

    // ── Factories ─────────────────────────────────────────────────────────────

    public static function above(BaseEntity $entity): self {
        $b           = new self();
        $b->anchorId = $entity->getId();
        $b->world    = $entity->getWorld();
        return $b;
    }

    public static function at(Vector3 $position, World $world): self {
        $b           = new self();
        $b->position = $position;
        $b->world    = $world;
        return $b;
    }

    // ── Line API ──────────────────────────────────────────────────────────────

    /**
     * Adds a line (top → bottom).
     * Any ->lineSpacing(X) BEFORE this call becomes this line's per-line spacing.
     */
    public function line(string|Closure $text): self {
        $this->lines[] = ['text' => $text, 'spacing' => $this->pendingSpacing];
        $this->pendingSpacing = null;
        return $this;
    }

    /** Set all lines at once (static strings only). */
    public function lines(array $lines): self {
        foreach ($lines as $l) $this->line($l);
        return $this;
    }

    /**
     * Sets the vertical gap for the NEXT line added (per-line override).
     * If called BEFORE any ->line(), it sets the global default spacing instead.
     *
     * Chain pattern:
     *   ->line('Title')
     *   ->lineSpacing(0.15)   // gap above the NEXT line (tight after title)
     *   ->line('Subtitle')
     *   ->lineSpacing(0.30)   // gap above the NEXT line
     *   ->line('Body')
     */
    public function lineSpacing(float $spacing): self {
        $spacing = max(0.0, $spacing);

        if ($this->lines === []) {
            // No lines added yet → treat as global default
            $this->globalSpacing = $spacing;
        } else {
            // Apply retroactively to the LAST added line
            $last = array_key_last($this->lines);
            $this->lines[$last]['spacing'] = $spacing;
        }

        return $this;
    }

    // ── Global options ────────────────────────────────────────────────────────

    /** How often dynamic lines refresh in ticks (default 20 = 1 s). */
    public function updateEvery(int $ticks): self {
        $this->updateRate = max(1, $ticks);
        return $this;
    }

    /**
     * Gap between the last line and the NPC's head in blocks.
     * Only applies when using HologramBuilder::above().
     */
    public function headOffset(float $offset): self {
        $this->headOffset = max(0.0, $offset);
        return $this;
    }

    // ── Build ─────────────────────────────────────────────────────────────────

    public function spawn(): Hologram {
        $hologram = $this->anchorId !== null
            ? $this->buildAbove()
            : Hologram::at($this->position, $this->world);

        $hologram->setUpdateRate($this->updateRate)
                 ->setLineSpacing($this->globalSpacing)
                 ->setHeadOffset($this->headOffset);

        foreach ($this->lines as $entry) {
            $hologram->line($entry['text'], $entry['spacing']);
        }

        return $hologram->spawn();
    }

    private function buildAbove(): Hologram {
        $npc = \Funaoo\EntityLib\EntityLib::get($this->anchorId);
        if ($npc === null) {
            throw new \RuntimeException("Entity #{$this->anchorId} not found; cannot anchor hologram.");
        }
        return Hologram::above($npc);
    }
}
