<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\skin;

use pocketmine\entity\Skin;
use pocketmine\plugin\Plugin;

final class SkinManager {

    private array $cache = [];

    public function __construct(private readonly Plugin $plugin) {}

    public function getDefaultSkin(string $type): Skin { return DefaultSkins::forType($type); }
    public function get(string $name): ?Skin            { return $this->cache[$name] ?? null; }
    public function has(string $name): bool             { return isset($this->cache[$name]); }
    public function forget(string $name): void          { unset($this->cache[$name]); }
    public function flush(): void                       { $this->cache = []; }

    public function loadFromFile(string $name, string $relativePath): ?Skin {
        if (isset($this->cache[$name])) return $this->cache[$name];
        $skin = SkinLoader::fromPNG($this->plugin->getDataFolder() . $relativePath);
        return $skin !== null ? ($this->cache[$name] = $skin) : null;
    }

    public function loadFromBase64(string $name, string $b64): ?Skin {
        if (isset($this->cache[$name])) return $this->cache[$name];
        $skin = SkinLoader::fromBase64($b64, 'entitylib_' . $name);
        return $skin !== null ? ($this->cache[$name] = $skin) : null;
    }
}
