<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\skin;

use pocketmine\entity\Skin;

final class SkinLoader {

    public static function fromPNG(string $path): ?Skin {
        if (!file_exists($path)) {
            return null;
        }
        $img = @imagecreatefrompng($path);
        if ($img === false) {
            return null;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        if (($w !== 64 || ($h !== 32 && $h !== 64))) {
            imagedestroy($img);
            return null;
        }
        $data = '';
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                $a    = ((~($rgba >> 24)) << 1) & 0xff;
                $r    = ($rgba >> 16) & 0xff;
                $g    = ($rgba >> 8) & 0xff;
                $b    = $rgba & 0xff;
                $data .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        imagedestroy($img);
        if ($h === 32) {
            $data .= str_repeat("\x00", 8192);
        }
        if (strlen($data) < 8192) {
            return null;
        }
        return new Skin('custom_' . md5_file($path), $data);
    }

    public static function fromBase64(string $b64, string $skinId = 'custom'): ?Skin {
        $raw = base64_decode($b64, true);
        if ($raw === false || strlen($raw) < 8192) {
            return null;
        }
        return new Skin($skinId, $raw);
    }

    public static function fromBytes(string $raw, string $skinId = 'custom'): ?Skin {
        if (strlen($raw) < 8192) {
            return null;
        }
        return new Skin($skinId, $raw);
    }
}
