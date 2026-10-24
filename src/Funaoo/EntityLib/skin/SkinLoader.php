<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\skin;

use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;

final class SkinLoader {

    /** Reads a 64x32, 64x64 or 128x128 PNG. Returns null if the file is missing or invalid. */
    public static function fromPNG(string $path): ?Skin {
        if (!is_file($path)) return null;

        $img = @imagecreatefrompng($path);
        if ($img === false) return null;

        $w = imagesx($img);
        $h = imagesy($img);

        $valid = ($w === 64 && ($h === 32 || $h === 64)) || ($w === 128 && $h === 128);
        if (!$valid) {
            imagedestroy($img);
            return null;
        }

        imagepalettetotruecolor($img);

        $data = '';
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba  = imagecolorat($img, $x, $y);
                $alpha = 127 - (($rgba >> 24) & 0x7f); // GD: 0 = opaque, 127 = transparent
                $data .= chr(($rgba >> 16) & 0xff)
                       . chr(($rgba >> 8)  & 0xff)
                       . chr($rgba         & 0xff)
                       . chr((int) round($alpha * 255 / 127));
            }
        }
        imagedestroy($img);

        // Legacy 64x32 skins are padded to 64x64.
        if ($h === 32) $data .= str_repeat("\x00", 64 * 32 * 4);

        return self::make('custom_' . md5_file($path), $data);
    }

    public static function fromBase64(string $b64, string $skinId = 'custom'): ?Skin {
        $raw = base64_decode($b64, true);
        return $raw !== false ? self::make($skinId, $raw) : null;
    }

    public static function fromBytes(string $raw, string $skinId = 'custom'): ?Skin {
        return self::make($skinId, $raw);
    }

    private static function make(string $skinId, string $data): ?Skin {
        if (!in_array(strlen($data), Skin::ACCEPTED_SKIN_SIZES, true)) return null;

        try {
            return new Skin($skinId, $data);
        } catch (InvalidSkinException) {
            return null;
        }
    }
}
