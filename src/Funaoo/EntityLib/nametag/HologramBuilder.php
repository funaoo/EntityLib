<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Closure;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use Funaoo\EntityLib\entity\BaseEntity;

final class HologramBuilder {


    private array   $lines        = [];
    private int     $updateRate   = 20;
    private float   $globalSpacing = Hologram::LINE_SPACING;
    private float   $headOffset   = Hologram::HEAD_OFFSET;
    private ?int    $anchorId     = null;
    private ?Vector3 $position    = null;
    private ?World  $world        = null;


    private ?float $pendingSpacing = null;

    private function __construct() {}



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





    public function line(string|Closure $text): self {
        $this->lines[] = ['text' => $text, 'spacing' => $this->pendingSpacing];
        $this->pendingSpacing = null;
        return $this;
    }


    public function lines(array $lines): self {
        foreach ($lines as $l) $this->line($l);
        return $this;
    }



    public function lineSpacing(float $spacing): self {
        $spacing = max(0.0, $spacing);

        if ($this->lines === []) {

            $this->globalSpacing = $spacing;
        } else {

            $last = array_key_last($this->lines);
            $this->lines[$last]['spacing'] = $spacing;
        }

        return $this;
    }




    public function updateEvery(int $ticks): self {
        $this->updateRate = max(1, $ticks);
        return $this;
    }



    public function headOffset(float $offset): self {
        $this->headOffset = max(0.0, $offset);
        return $this;
    }



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
