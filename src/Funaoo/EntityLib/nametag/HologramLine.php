<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Closure;

final class HologramLine {

    private string   $text;
    private ?Closure $resolver;



    private ?float $spacingAfter;

    public function __construct(string $text, ?Closure $resolver = null, ?float $spacingAfter = null) {
        $this->text         = $text;
        $this->resolver     = $resolver;
        $this->spacingAfter = $spacingAfter !== null ? max(0.0, $spacingAfter) : null;
    }

    public function resolve(): string {
        return $this->resolver !== null ? ($this->resolver)($this) : $this->text;
    }

    public function setText(string $text): void          { $this->text = $text; }
    public function setResolver(?Closure $fn): void      { $this->resolver = $fn; }
    public function getStaticText(): string              { return $this->text; }


    public function setSpacingAfter(?float $spacing): void {
        $this->spacingAfter = $spacing !== null ? max(0.0, $spacing) : null;
    }


    public function getSpacingAfter(): ?float { return $this->spacingAfter; }
}
