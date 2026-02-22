<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\storage;

final class EntityData {

    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly float  $x,
        public readonly float  $y,
        public readonly float  $z,
        public readonly string $world,
        public readonly float  $yaw,
        public readonly float  $pitch,
        public readonly float  $scale,
        public readonly bool   $nameTagVisible,
        public readonly bool   $nameTagAlwaysVisible,
        public readonly bool   $lookAtPlayers,
        public readonly bool   $canCollide,
        public readonly string $skinId,
        public readonly string $skinDataBase64,
        public readonly array  $metadata,
        public readonly array  $particles,
    ) {}

    public static function fromArray(array $d): self {
        return new self(
            type:                 (string)($d['type']                ?? 'human'),
            name:                 (string)($d['name']                ?? ''),
            x:                    (float)($d['x']                    ?? 0.0),
            y:                    (float)($d['y']                    ?? 64.0),
            z:                    (float)($d['z']                    ?? 0.0),
            world:                (string)($d['world']               ?? 'world'),
            yaw:                  (float)($d['yaw']                  ?? 0.0),
            pitch:                (float)($d['pitch']                ?? 0.0),
            scale:                (float)($d['scale']                ?? 1.0),
            nameTagVisible:       (bool)($d['nameTagVisible']        ?? true),
            nameTagAlwaysVisible: (bool)($d['nameTagAlwaysVisible']  ?? true),
            lookAtPlayers:        (bool)($d['lookAtPlayers']         ?? false),
            canCollide:           (bool)($d['canCollide']            ?? false),
            skinId:               (string)($d['skinId']              ?? 'Standard_Custom'),
            skinDataBase64:       (string)($d['skinData']            ?? ''),
            metadata:             (array)($d['metadata']             ?? []),
            particles:            (array)($d['particles']            ?? []),
        );
    }

    public function toArray(): array {
        return [
            'type'                 => $this->type,
            'name'                 => $this->name,
            'x'                    => $this->x,
            'y'                    => $this->y,
            'z'                    => $this->z,
            'world'                => $this->world,
            'yaw'                  => $this->yaw,
            'pitch'                => $this->pitch,
            'scale'                => $this->scale,
            'nameTagVisible'       => $this->nameTagVisible,
            'nameTagAlwaysVisible' => $this->nameTagAlwaysVisible,
            'lookAtPlayers'        => $this->lookAtPlayers,
            'canCollide'           => $this->canCollide,
            'skinId'               => $this->skinId,
            'skinData'             => $this->skinDataBase64,
            'metadata'             => $this->metadata,
            'particles'            => $this->particles,
        ];
    }
}
