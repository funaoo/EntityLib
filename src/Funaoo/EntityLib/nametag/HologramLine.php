<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Closure;

/**
 * A single line of a Hologram.
 *
 * Text supports §-color codes and dynamic placeholders via a Closure:
 *   fn(HologramLine $line): string => "Players: " . count($server->getOnlinePlayers())
 *
 * Each line carries its own optional spacing override.
 * If $spacingAfter is null the Hologram's global lineSpacing is used instead.
 */
final class HologramLine {

    private string   $text;
    private ?Closure $resolver;

    /**
     * Vertical gap ABOVE this line in blocks (distance to the line above it).
     * null = use Hologram::$lineSpacing (global default).
     */
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

    /** Override vertical gap above this line. Pass null to fall back to global spacing. */
    public function setSpacingAfter(?float $spacing): void {
        $this->spacingAfter = $spacing !== null ? max(0.0, $spacing) : null;
    }

    /** Returns the per-line spacing, or null if using global default. */
    public function getSpacingAfter(): ?float { return $this->spacingAfter; }
}
