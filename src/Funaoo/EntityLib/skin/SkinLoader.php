<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\skin;

use pocketmine\entity\Skin;

/**
 * SkinLoader - Loads skins from PNG files
 *
 * Handles the conversion from PNG image to Minecraft skin format.
 */
class SkinLoader {

    /**
     * Load skin from PNG file
     */
    public static function fromPNG(string $path): ?Skin {
        if (!file_exists($path)) {
            return null;
        }

        $img = @imagecreatefrompng($path);

        if ($img === false) {
            return null;
        }

        $width = imagesx($img);
        $height = imagesy($img);

        if (($width !== 64 || $height !== 64) && ($width !== 64 || $height !== 32)) {
            imagedestroy($img);
            return null;
        }

        $skinData = self::imageToSkinData($img, $width, $height);
        imagedestroy($img);

        if ($skinData === null) {
            return null;
        }

        $skinId = "custom_" . md5_file($path);
        return new Skin($skinId, $skinData);
    }

    /**
     * Convert image resource to skin data
     */
    private static function imageToSkinData($img, int $width, int $height): ?string {
        $skinData = "";

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($img, $x, $y);

                $a = ((~($rgba >> 24)) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;

                $skinData .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        if ($height === 32) {
            $skinData .= str_repeat("\x00", 8192);
        }

        return strlen($skinData) >= 8192 ? $skinData : null;
    }

    /**
     * Load skin from base64 data
     */
    public static function fromBase64(string $base64Data, string $skinId = "custom"): ?Skin {
        $skinData = base64_decode($base64Data);

        if (strlen($skinData) < 8192) {
            return null;
        }

        return new Skin($skinId, $skinData);
    }

    /**
     * Create skin from raw bytes
     */
    public static function fromBytes(string $skinData, string $skinId = "custom"): ?Skin {
        if (strlen($skinData) < 8192) {
            return null;
        }

        return new Skin($skinId, $skinData);
    }
}