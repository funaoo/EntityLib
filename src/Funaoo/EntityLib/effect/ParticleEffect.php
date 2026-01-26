<?php

/**
 * ParticleEffect - Add visual particle effects to entities
 *
 * Particles make NPCs look way cooler. A shop NPC with emerald particles?
 * A quest giver with sparkles? A boss NPC with flames? This handles all that.
 *
 * I spent way too long tweaking particle positions to make them look good.
 * Trust me, the offset calculations here are perfect for most use cases.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\effect;

use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\particle\EnchantParticle;
use pocketmine\world\particle\CriticalParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\particle\RedstoneParticle;
use pocketmine\world\particle\PortalParticle;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\particle\Particle;
use pocketmine\color\Color;

/**
 * Manages particle effects around entities
 *
 * This class handles spawning particles in various patterns around
 * an entity. Supports single particles, circles, spirals, and more.
 */
class ParticleEffect {

    /** Available particle types */
    public const HEART = "heart";
    public const FLAME = "flame";
    public const HAPPY_VILLAGER = "happy_villager";
    public const ANGRY_VILLAGER = "angry_villager";
    public const ENCHANT = "enchant";
    public const CRITICAL = "critical";
    public const SMOKE = "smoke";
    public const EXPLODE = "explode";
    public const LAVA = "lava";
    public const REDSTONE = "redstone";
    public const PORTAL = "portal";
    public const TELEPORT = "teleport";
    public const DUST_RED = "dust_red";
    public const DUST_GREEN = "dust_green";
    public const DUST_BLUE = "dust_blue";
    public const DUST_YELLOW = "dust_yellow";
    public const DUST_PURPLE = "dust_purple";

    /** Particle display patterns */
    public const PATTERN_SINGLE = "single";
    public const PATTERN_CIRCLE = "circle";
    public const PATTERN_SPIRAL = "spiral";
    public const PATTERN_RAIN = "rain";
    public const PATTERN_FOUNTAIN = "fountain";

    private string $type;
    private string $pattern;
    private int $density;
    private float $radius;
    private float $height;

    /**
     * Create a new particle effect
     *
     * @param string $type Particle type (use constants)
     * @param string $pattern Display pattern (use PATTERN_* constants)
     * @param int $density Number of particles per spawn (1-10 recommended)
     * @param float $radius Radius for circular patterns (blocks)
     * @param float $height Height for vertical patterns (blocks)
     */
    public function __construct(
        string $type = self::HEART,
        string $pattern = self::PATTERN_CIRCLE,
        int $density = 5,
        float $radius = 1.0,
        float $height = 2.0
    ) {
        $this->type = $type;
        $this->pattern = $pattern;
        $this->density = max(1, min(10, $density));
        $this->radius = $radius;
        $this->height = $height;
    }

    /**
     * Spawn particles at a position
     *
     * @param World $world The world to spawn in
     * @param Vector3 $position Center position
     * @param float $tickOffset Optional offset for animated patterns (0-1)
     */
    public function spawn(World $world, Vector3 $position, float $tickOffset = 0.0): void {
        $particle = $this->createParticle();

        if ($particle === null) {
            return;
        }

        switch ($this->pattern) {
            case self::PATTERN_SINGLE:
                $this->spawnSingle($world, $position, $particle);
                break;

            case self::PATTERN_CIRCLE:
                $this->spawnCircle($world, $position, $particle, $tickOffset);
                break;

            case self::PATTERN_SPIRAL:
                $this->spawnSpiral($world, $position, $particle, $tickOffset);
                break;

            case self::PATTERN_RAIN:
                $this->spawnRain($world, $position, $particle);
                break;

            case self::PATTERN_FOUNTAIN:
                $this->spawnFountain($world, $position, $particle);
                break;
        }
    }

    /**
     * Create a particle instance based on type
     */
    private function createParticle(): ?Particle {
        return match($this->type) {
            self::HEART => new HeartParticle(),
            self::FLAME => new FlameParticle(),
            self::HAPPY_VILLAGER => new HappyVillagerParticle(),
            self::ANGRY_VILLAGER => new AngryVillagerParticle(),
            self::ENCHANT => new EnchantParticle(new Color(0, 255, 0)),
            self::CRITICAL => new CriticalParticle(),
            self::SMOKE => new SmokeParticle(),
            self::EXPLODE => new ExplodeParticle(),
            self::LAVA => new LavaParticle(),
            self::REDSTONE => new RedstoneParticle(),
            self::PORTAL => new PortalParticle(),
            self::TELEPORT => new EndermanTeleportParticle(),
            self::DUST_RED => new DustParticle(new Color(255, 0, 0)),
            self::DUST_GREEN => new DustParticle(new Color(0, 255, 0)),
            self::DUST_BLUE => new DustParticle(new Color(0, 0, 255)),
            self::DUST_YELLOW => new DustParticle(new Color(255, 255, 0)),
            self::DUST_PURPLE => new DustParticle(new Color(128, 0, 128)),
            default => null
        };
    }

    /**
     * Spawn a single particle at the position
     */
    private function spawnSingle(World $world, Vector3 $position, Particle $particle): void {
        $world->addParticle($position->add(0, 1, 0), $particle);
    }

    /**
     * Spawn particles in a circle around the position
     */
    private function spawnCircle(World $world, Vector3 $position, Particle $particle, float $offset): void {
        $angleStep = (2 * M_PI) / $this->density;

        for ($i = 0; $i < $this->density; $i++) {
            $angle = ($angleStep * $i) + ($offset * 2 * M_PI);

            $x = $position->x + (cos($angle) * $this->radius);
            $z = $position->z + (sin($angle) * $this->radius);
            $y = $position->y + 1.0;

            $world->addParticle(new Vector3($x, $y, $z), $particle);
        }
    }

    /**
     * Spawn particles in a spiral going upward
     */
    private function spawnSpiral(World $world, Vector3 $position, Particle $particle, float $offset): void {
        $heightStep = $this->height / $this->density;
        $angleStep = (4 * M_PI) / $this->density;

        for ($i = 0; $i < $this->density; $i++) {
            $angle = ($angleStep * $i) + ($offset * 2 * M_PI);
            $currentHeight = $heightStep * $i;

            $x = $position->x + (cos($angle) * $this->radius);
            $z = $position->z + (sin($angle) * $this->radius);
            $y = $position->y + $currentHeight;

            $world->addParticle(new Vector3($x, $y, $z), $particle);
        }
    }

    /**
     * Spawn particles falling from above (rain effect)
     */
    private function spawnRain(World $world, Vector3 $position, Particle $particle): void {
        for ($i = 0; $i < $this->density; $i++) {
            $angle = mt_rand(0, 360) * (M_PI / 180);
            $distance = (mt_rand(0, 100) / 100) * $this->radius;

            $x = $position->x + (cos($angle) * $distance);
            $z = $position->z + (sin($angle) * $distance);
            $y = $position->y + $this->height + (mt_rand(0, 50) / 100);

            $world->addParticle(new Vector3($x, $y, $z), $particle);
        }
    }

    /**
     * Spawn particles shooting upward (fountain effect)
     */
    private function spawnFountain(World $world, Vector3 $position, Particle $particle): void {
        for ($i = 0; $i < $this->density; $i++) {
            $angle = mt_rand(0, 360) * (M_PI / 180);
            $distance = (mt_rand(30, 80) / 100) * $this->radius;

            $x = $position->x + (cos($angle) * $distance);
            $z = $position->z + (sin($angle) * $distance);
            $y = $position->y + 0.5 + (mt_rand(0, 30) / 100);

            $world->addParticle(new Vector3($x, $y, $z), $particle);
        }
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getPattern(): string {
        return $this->pattern;
    }

    public function setPattern(string $pattern): void {
        $this->pattern = $pattern;
    }

    public function getDensity(): int {
        return $this->density;
    }

    public function setDensity(int $density): void {
        $this->density = max(1, min(10, $density));
    }

    public function getRadius(): float {
        return $this->radius;
    }

    public function setRadius(float $radius): void {
        $this->radius = $radius;
    }

    public function getHeight(): float {
        return $this->height;
    }

    public function setHeight(float $height): void {
        $this->height = $height;
    }
}