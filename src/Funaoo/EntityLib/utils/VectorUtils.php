<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\utils;

use pocketmine\math\Vector3;

final class VectorUtils {

    public static function circlePoints(Vector3 $center, float $radius, int $points, float $yOffset = 0.0): array {
        $out  = [];
        $step = (2 * M_PI) / max(1, $points);
        for ($i = 0; $i < $points; $i++) {
            $a     = $step * $i;
            $out[] = new Vector3($center->x + cos($a) * $radius, $center->y + $yOffset, $center->z + sin($a) * $radius);
        }
        return $out;
    }

    public static function spiralPoints(Vector3 $start, float $radius, float $height, int $points): array {
        $out   = [];
        $n     = max(1, $points);
        $hStep = $height / $n;
        $aStep = (4 * M_PI) / $n;
        for ($i = 0; $i < $points; $i++) {
            $a     = $aStep * $i;
            $out[] = new Vector3($start->x + cos($a) * $radius, $start->y + $hStep * $i, $start->z + sin($a) * $radius);
        }
        return $out;
    }

    public static function randomInRadius(Vector3 $center, float $radius, float $yOffset = 0.0): Vector3 {
        $a = mt_rand(0, 360) * M_PI / 180;
        $d = mt_rand(0, 100) / 100 * $radius;
        return new Vector3($center->x + cos($a) * $d, $center->y + $yOffset, $center->z + sin($a) * $d);
    }

    public static function yawTo(Vector3 $from, Vector3 $to): float {
        return (float)(atan2($to->x - $from->x, $to->z - $from->z) / M_PI * 180.0);
    }

    public static function pitchTo(Vector3 $from, Vector3 $to): float {
        $dx = $to->x - $from->x;
        $dy = $to->y - $from->y;
        $dz = $to->z - $from->z;
        return (float)(-atan2($dy, sqrt($dx ** 2 + $dz ** 2)) / M_PI * 180.0);
    }

    public static function lerp(Vector3 $from, Vector3 $to, float $t): Vector3 {
        $t = max(0.0, min(1.0, $t));
        return new Vector3(
            $from->x + ($to->x - $from->x) * $t,
            $from->y + ($to->y - $from->y) * $t,
            $from->z + ($to->z - $from->z) * $t,
        );
    }

    public static function isInBounds(Vector3 $pos, Vector3 $min, Vector3 $max): bool {
        return $pos->x >= $min->x && $pos->x <= $max->x
            && $pos->y >= $min->y && $pos->y <= $max->y
            && $pos->z >= $min->z && $pos->z <= $max->z;
    }

    public static function rotateY(Vector3 $v, float $degrees): Vector3 {
        $rad = deg2rad($degrees);
        return new Vector3(
            $v->x * cos($rad) - $v->z * sin($rad),
            $v->y,
            $v->x * sin($rad) + $v->z * cos($rad),
        );
    }
}
