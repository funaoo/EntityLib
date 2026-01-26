<?php

/**
 * EntityData - Data structure for storing entity information
 *
 * This is a simple data class that holds all the info about an entity
 * in a structured way. Makes serialization and deserialization cleaner.
 *
 * Think of it as a blueprint or snapshot of an entity's state.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\storage;

use pocketmine\entity\Skin;
use pocketmine\math\Vector3;

/**
 * Entity data container
 *
 * Holds all information needed to recreate an entity.
 * Used by EntityStorage for saving/loading.
 */
class EntityData {

    public string $type;
    public string $name;
    public Vector3 $position;
    public string $worldName;
    public float $yaw;
    public float $pitch;
    public float $scale;
    public bool $nameTagVisible;
    public bool $nameTagAlwaysVisible;
    public bool $lookAtPlayers;
    public bool $canCollide;
    public ?Skin $skin;
    public array $metadata;

    /**
     * Create from array
     *
     * @param array $data Array data from storage
     * @return self EntityData instance
     */
    public static function fromArray(array $data): self {
        $instance = new self();

        $instance->type = $data['type'] ?? 'human';
        $instance->name = $data['name'] ?? '';

        $instance->position = new Vector3(
            $data['position']['x'] ?? 0,
            $data['position']['y'] ?? 64,
            $data['position']['z'] ?? 0
        );

        $instance->worldName = $data['position']['world'] ?? 'world';

        $instance->yaw = $data['rotation']['yaw'] ?? 0.0;
        $instance->pitch = $data['rotation']['pitch'] ?? 0.0;

        $instance->scale = $data['scale'] ?? 1.0;
        $instance->nameTagVisible = $data['nameTagVisible'] ?? true;
        $instance->nameTagAlwaysVisible = $data['nameTagAlwaysVisible'] ?? true;
        $instance->lookAtPlayers = $data['lookAtPlayers'] ?? false;
        $instance->canCollide = $data['canCollide'] ?? false;

        // Load skin
        if (isset($data['skin']['data'])) {
            $skinData = base64_decode($data['skin']['data']);
            if (strlen($skinData) >= 8192) {
                $instance->skin = new Skin(
                    $data['skin']['name'] ?? 'Standard_Custom',
                    $skinData
                );
            }
        }

        $instance->metadata = $data['metadata'] ?? [];

        return $instance;
    }

    /**
     * Convert to array for storage
     *
     * @return array Array representation
     */
    public function toArray(): array {
        $data = [
            'type' => $this->type,
            'name' => $this->name,
            'position' => [
                'x' => $this->position->x,
                'y' => $this->position->y,
                'z' => $this->position->z,
                'world' => $this->worldName
            ],
            'rotation' => [
                'yaw' => $this->yaw,
                'pitch' => $this->pitch
            ],
            'scale' => $this->scale,
            'nameTagVisible' => $this->nameTagVisible,
            'nameTagAlwaysVisible' => $this->nameTagAlwaysVisible,
            'lookAtPlayers' => $this->lookAtPlayers,
            'canCollide' => $this->canCollide,
            'metadata' => $this->metadata
        ];

        // Save skin if exists
        if ($this->skin !== null) {
            $data['skin'] = [
                'name' => $this->skin->getSkinId(),
                'data' => base64_encode($this->skin->getSkinData())
            ];
        }

        return $data;
    }

    /**
     * Validate entity data
     *
     * @return bool True if valid
     */
    public function isValid(): bool {
        if (empty($this->type)) {
            return false;
        }

        if (empty($this->worldName)) {
            return false;
        }

        if ($this->scale <= 0 || $this->scale > 10) {
            return false;
        }

        return true;
    }

    /**
     * Clone this entity data
     *
     * @return self New instance with same data
     */
    public function clone(): self {
        return self::fromArray($this->toArray());
    }
}