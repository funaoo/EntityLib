<?php

/**
 * EntityStorage - Save and load entities from disk
 *
 * Nobody wants to respawn all their NPCs every time the server restarts.
 * This saves entity data to JSON files so they persist.
 *
 * I went with JSON instead of SQLite because it's simpler and easier to
 * edit manually if needed. Plus, most servers won't have enough NPCs for
 * performance to matter.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\storage;

use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\entity\Skin;
use pocketmine\entity\Location;
use Funaoo\EntityLib\EntityLib;
use Funaoo\EntityLib\entity\BaseEntity;

/**
 * Handles saving and loading entities
 *
 * Entities are saved to: plugin_data/EntityLib/entities.json
 * The format is simple JSON that you can edit by hand if needed.
 */
class EntityStorage {

    private Plugin $plugin;
    private string $storagePath;

    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
        $this->storagePath = $plugin->getDataFolder() . "entities.json";

        // Create data folder if it doesn't exist
        if (!is_dir($plugin->getDataFolder())) {
            @mkdir($plugin->getDataFolder(), 0777, true);
        }
    }

    /**
     * Save an entity to storage
     *
     * @param BaseEntity $entity The entity to save
     * @return bool True on success, false on failure
     */
    public function save(BaseEntity $entity): bool {
        try {
            // Load existing data
            $data = $this->loadData();

            // Convert entity to array
            $entityData = $entity->toArray();
            $entityData['id'] = $entity->getId();

            // Store by entity ID
            $data[$entity->getId()] = $entityData;

            // Save back to file
            return $this->saveData($data);

        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Failed to save entity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete an entity from storage
     *
     * @param int $entityId The entity ID
     * @return bool True if deleted, false if not found
     */
    public function delete(int $entityId): bool {
        try {
            $data = $this->loadData();

            if (!isset($data[$entityId])) {
                return false;
            }

            unset($data[$entityId]);
            return $this->saveData($data);

        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Failed to delete entity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load all saved entities
     *
     * This spawns all saved entities into their respective worlds.
     * Call this on server startup if you want entities to persist.
     *
     * @return int Number of entities loaded
     */
    public function loadAll(): int {
        try {
            $data = $this->loadData();
            $loaded = 0;

            foreach ($data as $entityData) {
                if ($this->loadEntity($entityData)) {
                    $loaded++;
                }
            }

            return $loaded;

        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Failed to load entities: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Load a single entity from array data
     *
     * @param array $data Entity data array
     * @return bool True if loaded successfully
     */
    private function loadEntity(array $data): bool {
        try {
            // Get world
            $worldName = $data['position']['world'] ?? null;
            if ($worldName === null) {
                return false;
            }

            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
            if ($world === null) {
                $this->plugin->getLogger()->warning("World '{$worldName}' not found for entity");
                return false;
            }

            // Build position
            $position = new Vector3(
                $data['position']['x'],
                $data['position']['y'],
                $data['position']['z']
            );

            // Build rotation
            $yaw = $data['rotation']['yaw'] ?? 0;
            $pitch = $data['rotation']['pitch'] ?? 0;

            // Load skin
            $skinData = base64_decode($data['skin']['data'] ?? '');
            if (strlen($skinData) < 8192) {
                $skinData = str_repeat("\x00", 8192); // Default blank skin
            }
            $skin = new Skin($data['skin']['name'] ?? "Standard_Custom", $skinData);

            // Create entity using builder
            $builder = EntityLib::create($position, $world)
                ->setType($data['type'])
                ->setName($data['name'] ?? "")
                ->setSkin($skin)
                ->setScale($data['scale'] ?? 1.0)
                ->setRotation($yaw, $pitch)
                ->setNameTagVisible($data['nameTagVisible'] ?? true)
                ->setNameTagAlwaysVisible($data['nameTagAlwaysVisible'] ?? true)
                ->lookAtPlayers($data['lookAtPlayers'] ?? false)
                ->setCanCollide($data['canCollide'] ?? false);

            // Add metadata
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                foreach ($data['metadata'] as $key => $value) {
                    $builder->setMetadata($key, $value);
                }
            }

            // Spawn the entity
            $builder->spawn();

            return true;

        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Failed to load entity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all saved entities
     *
     * This deletes the storage file. Use with caution!
     *
     * @return bool True on success
     */
    public function clearAll(): bool {
        try {
            if (file_exists($this->storagePath)) {
                return @unlink($this->storagePath);
            }
            return true;
        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Failed to clear storage: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get count of saved entities
     *
     * @return int Number of saved entities
     */
    public function getCount(): int {
        try {
            $data = $this->loadData();
            return count($data);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Check if storage file exists
     *
     * @return bool True if exists
     */
    public function exists(): bool {
        return file_exists($this->storagePath);
    }

    /**
     * Load data from storage file
     *
     * @return array Entity data array
     */
    private function loadData(): array {
        if (!file_exists($this->storagePath)) {
            return [];
        }

        $content = @file_get_contents($this->storagePath);
        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * Save data to storage file
     *
     * @param array $data Entity data array
     * @return bool True on success
     */
    private function saveData(array $data): bool {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return false;
        }

        return @file_put_contents($this->storagePath, $json) !== false;
    }

    /**
     * Create a backup of the storage file
     *
     * Good to call before major operations. Better safe than sorry!
     *
     * @return bool True on success
     */
    public function createBackup(): bool {
        if (!$this->exists()) {
            return true; // Nothing to backup
        }

        try {
            $backupPath = $this->storagePath . "." . date("Y-m-d_H-i-s") . ".backup";
            return @copy($this->storagePath, $backupPath);
        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Failed to create backup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get list of available backups
     *
     * @return string[] Array of backup file names
     */
    public function getBackups(): array {
        $backups = [];
        $dir = dirname($this->storagePath);

        if (!is_dir($dir)) {
            return [];
        }

        $files = scandir($dir);
        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            if (str_ends_with($file, '.backup')) {
                $backups[] = $file;
            }
        }

        return $backups;
    }
}