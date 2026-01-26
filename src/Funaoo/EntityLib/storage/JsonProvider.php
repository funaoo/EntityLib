<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\storage;

/**
 * JsonProvider - Handles JSON file operations
 *
 * Simple wrapper for reading/writing JSON files safely.
 */
class JsonProvider {

    private string $filePath;

    public function __construct(string $filePath) {
        $this->filePath = $filePath;

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    /**
     * Load data from JSON file
     */
    public function load(): array {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $content = @file_get_contents($this->filePath);

        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Save data to JSON file
     */
    public function save(array $data): bool {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return false;
        }

        return @file_put_contents($this->filePath, $json) !== false;
    }

    /**
     * Check if file exists
     */
    public function exists(): bool {
        return file_exists($this->filePath);
    }

    /**
     * Delete the file
     */
    public function delete(): bool {
        if (!$this->exists()) {
            return true;
        }

        return @unlink($this->filePath);
    }

    /**
     * Get file path
     */
    public function getFilePath(): string {
        return $this->filePath;
    }

    /**
     * Create a backup
     */
    public function backup(): bool {
        if (!$this->exists()) {
            return true;
        }

        $backupPath = $this->filePath . '.' . date('Y-m-d_H-i-s') . '.backup';
        return @copy($this->filePath, $backupPath);
    }
}