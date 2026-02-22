<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\storage;

final class JsonProvider {

    public function __construct(private readonly string $path) {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    public function load(): array {
        if (!file_exists($this->path)) {
            return [];
        }
        $raw = @file_get_contents($this->path);
        if ($raw === false || $raw === '') {
            return [];
        }
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\JsonException) {
            return [];
        }
    }

    public function save(array $data): bool {
        try {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return false;
        }
        return @file_put_contents($this->path, $json, LOCK_EX) !== false;
    }

    public function exists(): bool {
        return file_exists($this->path);
    }

    public function delete(): bool {
        return !$this->exists() || (bool)@unlink($this->path);
    }

    public function backup(): bool {
        if (!$this->exists()) {
            return true;
        }
        return (bool)@copy($this->path, $this->path . '.' . date('Y-m-d_H-i-s') . '.bak');
    }
}
