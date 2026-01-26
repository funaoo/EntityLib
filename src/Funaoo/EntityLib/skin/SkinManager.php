<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\skin;

use pocketmine\entity\Skin;
use pocketmine\plugin\Plugin;

/**
 * SkinManager - Manages entity skins
 *
 * Handles loading, caching, and providing skins.
 */
class SkinManager {

    private Plugin $plugin;
    private array $skinCache = [];
    private array $defaultSkins = [];

    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
        $this->loadDefaultSkins();
    }

    /**
     * Load default skins
     */
    private function loadDefaultSkins(): void {
        $blankSkin = DefaultSkins::blank();

        $this->defaultSkins['blank'] = $blankSkin;
        $this->defaultSkins['human'] = $blankSkin;
        $this->defaultSkins['floating_text'] = $blankSkin;
        $this->defaultSkins['animal'] = $blankSkin;
        $this->defaultSkins['mob'] = $blankSkin;
        $this->defaultSkins['villager'] = $blankSkin;
        $this->defaultSkins['pig'] = $blankSkin;
        $this->defaultSkins['cow'] = $blankSkin;
        $this->defaultSkins['sheep'] = $blankSkin;
        $this->defaultSkins['chicken'] = $blankSkin;
        $this->defaultSkins['zombie'] = $blankSkin;
        $this->defaultSkins['skeleton'] = $blankSkin;
        $this->defaultSkins['creeper'] = $blankSkin;
    }

    /**
     * Get default skin for entity type
     */
    public function getDefaultSkin(string $entityType): Skin {
        return $this->defaultSkins[$entityType] ?? $this->defaultSkins['blank'];
    }

    /**
     * Load skin from PNG file
     */
    public function loadSkin(string $name, string $path): ?Skin {
        if (isset($this->skinCache[$name])) {
            return $this->skinCache[$name];
        }

        $fullPath = $this->plugin->getDataFolder() . $path;

        $skin = SkinLoader::fromPNG($fullPath);

        if ($skin !== null) {
            $this->skinCache[$name] = $skin;
        }

        return $skin;
    }

    /**
     * Create skin from raw data
     */
    public function createSkin(string $name, string $skinData, string $geometryData = ""): ?Skin {
        if (strlen($skinData) < 8192) {
            return null;
        }

        $skin = new Skin("entitylib_{$name}", $skinData, "", "geometry.humanoid.custom", $geometryData);
        $this->skinCache[$name] = $skin;

        return $skin;
    }

    /**
     * Get cached skin
     */
    public function getCachedSkin(string $name): ?Skin {
        return $this->skinCache[$name] ?? null;
    }

    /**
     * Check if skin is cached
     */
    public function isCached(string $name): bool {
        return isset($this->skinCache[$name]);
    }

    /**
     * Clear skin from cache
     */
    public function clearCache(string $name): void {
        unset($this->skinCache[$name]);
    }

    /**
     * Clear all cached skins
     */
    public function clearAllCache(): void {
        $this->skinCache = [];
    }

    /**
     * Get cache size
     */
    public function getCacheSize(): int {
        return count($this->skinCache);
    }

    /**
     * Load skin from base64
     */
    public function loadSkinFromBase64(string $name, string $base64Data): ?Skin {
        if (isset($this->skinCache[$name])) {
            return $this->skinCache[$name];
        }

        $skin = SkinLoader::fromBase64($base64Data, "entitylib_{$name}");

        if ($skin !== null) {
            $this->skinCache[$name] = $skin;
        }

        return $skin;
    }

    /**
     * Get plugin instance
     */
    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}