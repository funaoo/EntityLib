<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\effect;

use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\particle\CriticalParticle;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\EnchantParticle;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\particle\PortalParticle;
use pocketmine\world\particle\RedstoneParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\World;

final class ParticleEffect {

    public const PATTERN_SINGLE   = 'single';
    public const PATTERN_CIRCLE   = 'circle';
    public const PATTERN_SPIRAL   = 'spiral';
    public const PATTERN_RAIN     = 'rain';
    public const PATTERN_FOUNTAIN = 'fountain';

    public function __construct(
        private string $type    = ParticleType::HEART,
        private string $pattern = self::PATTERN_CIRCLE,
        private int    $density = 5,
        private float  $radius  = 1.0,
        private float  $height  = 2.0,
    ) {
        $this->density = max(1, min(20, $density));
    }

    public function spawn(World $world, Vector3 $position, float $tickOffset = 0.0): void {
        $particle = $this->createParticle();
        if ($particle === null) {
            return;
        }
        match($this->pattern) {
            self::PATTERN_SINGLE   => $this->spawnSingle($world, $position, $particle),
            self::PATTERN_CIRCLE   => $this->spawnCircle($world, $position, $particle, $tickOffset),
            self::PATTERN_SPIRAL   => $this->spawnSpiral($world, $position, $particle, $tickOffset),
            self::PATTERN_RAIN     => $this->spawnRain($world, $position, $particle),
            self::PATTERN_FOUNTAIN => $this->spawnFountain($world, $position, $particle),
            default                => $this->spawnSingle($world, $position, $particle),
        };
    }

    private function createParticle(): ?Particle {
        return match($this->type) {
            ParticleType::HEART          => new HeartParticle(),
            ParticleType::FLAME          => new FlameParticle(),
            ParticleType::HAPPY_VILLAGER => new HappyVillagerParticle(),
            ParticleType::ANGRY_VILLAGER => new AngryVillagerParticle(),
            ParticleType::ENCHANT        => new EnchantParticle(new Color(0, 255, 0)),
            ParticleType::CRITICAL       => new CriticalParticle(),
            ParticleType::SMOKE          => new SmokeParticle(),
            ParticleType::EXPLODE        => new ExplodeParticle(),
            ParticleType::LAVA           => new LavaParticle(),
            ParticleType::REDSTONE       => new RedstoneParticle(),
            ParticleType::PORTAL         => new PortalParticle(),
            ParticleType::TELEPORT       => new EndermanTeleportParticle(),
            ParticleType::DUST_RED       => new DustParticle(new Color(255, 0, 0)),
            ParticleType::DUST_GREEN     => new DustParticle(new Color(0, 255, 0)),
            ParticleType::DUST_BLUE      => new DustParticle(new Color(0, 0, 255)),
            ParticleType::DUST_YELLOW    => new DustParticle(new Color(255, 255, 0)),
            ParticleType::DUST_PURPLE    => new DustParticle(new Color(128, 0, 128)),
            ParticleType::DUST_WHITE     => new DustParticle(new Color(255, 255, 255)),
            ParticleType::DUST_ORANGE    => new DustParticle(new Color(255, 128, 0)),
            default                      => null,
        };
    }

    private function spawnSingle(World $world, Vector3 $pos, Particle $p): void {
        $world->addParticle($pos->add(0, 1.0, 0), $p);
    }

    private function spawnCircle(World $world, Vector3 $pos, Particle $p, float $offset): void {
        $step = (2 * M_PI) / $this->density;
        for ($i = 0; $i < $this->density; $i++) {
            $a = $step * $i + $offset * 2 * M_PI;
            $world->addParticle(new Vector3(
                $pos->x + cos($a) * $this->radius,
                $pos->y + 1.0,
                $pos->z + sin($a) * $this->radius,
            ), $p);
        }
    }

    private function spawnSpiral(World $world, Vector3 $pos, Particle $p, float $offset): void {
        $hStep = $this->height / $this->density;
        $aStep = (4 * M_PI) / $this->density;
        for ($i = 0; $i < $this->density; $i++) {
            $a = $aStep * $i + $offset * 2 * M_PI;
            $world->addParticle(new Vector3(
                $pos->x + cos($a) * $this->radius,
                $pos->y + $hStep * $i,
                $pos->z + sin($a) * $this->radius,
            ), $p);
        }
    }

    private function spawnRain(World $world, Vector3 $pos, Particle $p): void {
        for ($i = 0; $i < $this->density; $i++) {
            $a = mt_rand(0, 360) * M_PI / 180;
            $d = mt_rand(0, 100) / 100 * $this->radius;
            $world->addParticle(new Vector3(
                $pos->x + cos($a) * $d,
                $pos->y + $this->height + mt_rand(0, 50) / 100,
                $pos->z + sin($a) * $d,
            ), $p);
        }
    }

    private function spawnFountain(World $world, Vector3 $pos, Particle $p): void {
        for ($i = 0; $i < $this->density; $i++) {
            $a = mt_rand(0, 360) * M_PI / 180;
            $d = mt_rand(30, 80) / 100 * $this->radius;
            $world->addParticle(new Vector3(
                $pos->x + cos($a) * $d,
                $pos->y + 0.5 + mt_rand(0, 30) / 100,
                $pos->z + sin($a) * $d,
            ), $p);
        }
    }

    public function getType(): string    { return $this->type; }
    public function getPattern(): string { return $this->pattern; }
    public function getDensity(): int    { return $this->density; }
    public function getRadius(): float   { return $this->radius; }
    public function getHeight(): float   { return $this->height; }

    public function toArray(): array {
        return [
            'type'     => $this->type,
            'pattern'  => $this->pattern,
            'density'  => $this->density,
            'radius'   => $this->radius,
            'height'   => $this->height,
        ];
    }
}
