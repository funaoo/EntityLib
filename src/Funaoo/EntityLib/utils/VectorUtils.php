<?php

/**
 * VectorUtils - Math utilities for positions and directions
 *
 * Working with 3D vectors can be a pain. These utilities make common
 * calculations easier - rotations, distances, circles, etc.
 *
 * I've used these in so many projects that I just include them by default now.
 */

declare(strict_types=1);

namespace Funaoo\EntityLib\utils;

use pocketmine\math\Vector3;

/**
 * Vector mathematics utilities
 *
 * Helper functions for working with 3D positions and directions.
 */
class VectorUtils {

    /**
     * Calculate points in a circle around a center point
     *
     * Super useful for spawning NPCs in formations.
     *
     * @param Vector3 $center Center position
     * @param float $radius Circle radius
     * @param int $points Number of points
     * @param float $yOffset Y offset from center
     * @return Vector3[] Array of positions
     */
    public static function getCirclePoints(
        Vector3 $center,
        float $radius,
        int $points,
        float $yOffset = 0.0
    ): array {
        $positions = [];
        $angleStep = (2 * M_PI) / $points;

        for ($i = 0; $i < $points; $i++) {
            $angle = $angleStep * $i;

            $x = $center->x + (cos($angle) * $radius);
            $z = $center->z + (sin($angle) * $radius);
            $y = $center->y + $yOffset;

            $positions[] = new Vector3($x, $y, $z);
        }

        return $positions;
    }

    /**
     * Calculate points in a spiral going upward
     *
     * @param Vector3 $start Starting position
     * @param float $radius Spiral radius
     * @param float $height Total height
     * @param int $points Number of points
     * @return Vector3[] Array of positions
     */
    public static function getSpiralPoints(
        Vector3 $start,
        float $radius,
        float $height,
        int $points
    ): array {
        $positions = [];
        $angleStep = (4 * M_PI) / $points; // Two full rotations
        $heightStep = $height / $points;

        for ($i = 0; $i < $points; $i++) {
            $angle = $angleStep * $i;
            $currentHeight = $heightStep * $i;

            $x = $start->x + (cos($angle) * $radius);
            $z = $start->z + (sin($angle) * $radius);
            $y = $start->y + $currentHeight;

            $positions[] = new Vector3($x, $y, $z);
        }

        return $positions;
    }

    /**
     * Get random position within a radius
     *
     * @param Vector3 $center Center position
     * @param float $radius Maximum radius
     * @param float $yOffset Y offset from center
     * @return Vector3 Random position
     */
    public static function getRandomInRadius(
        Vector3 $center,
        float $radius,
        float $yOffset = 0.0
    ): Vector3 {
        $angle = mt_rand(0, 360) * (M_PI / 180);
        $distance = (mt_rand(0, 100) / 100) * $radius;

        $x = $center->x + (cos($angle) * $distance);
        $z = $center->z + (sin($angle) * $distance);
        $y = $center->y + $yOffset;

        return new Vector3($x, $y, $z);
    }

    /**
     * Calculate yaw angle from one position to another
     *
     * @param Vector3 $from Starting position
     * @param Vector3 $to Target position
     * @return float Yaw angle in degrees
     */
    public static function calculateYaw(Vector3 $from, Vector3 $to): float {
        $xDist = $to->x - $from->x;
        $zDist = $to->z - $from->z;

        return atan2($zDist, $xDist) / M_PI * 180 - 90;
    }

    /**
     * Calculate pitch angle from one position to another
     *
     * @param Vector3 $from Starting position
     * @param Vector3 $to Target position
     * @return float Pitch angle in degrees
     */
    public static function calculatePitch(Vector3 $from, Vector3 $to): float {
        $xDist = $to->x - $from->x;
        $yDist = $to->y - $from->y;
        $zDist = $to->z - $from->z;

        $distance = sqrt($xDist * $xDist + $zDist * $zDist);

        return -atan2($yDist, $distance) / M_PI * 180;
    }

    /**
     * Get direction vector from yaw and pitch
     *
     * @param float $yaw Yaw angle in degrees
     * @param float $pitch Pitch angle in degrees
     * @return Vector3 Direction vector (normalized)
     */
    public static function getDirectionFromRotation(float $yaw, float $pitch): Vector3 {
        $y = -sin(deg2rad($pitch));
        $xz = cos(deg2rad($pitch));
        $x = -$xz * sin(deg2rad($yaw));
        $z = $xz * cos(deg2rad($yaw));

        return new Vector3($x, $y, $z);
    }

    /**
     * Interpolate between two positions
     *
     * @param Vector3 $from Start position
     * @param Vector3 $to End position
     * @param float $progress Progress (0.0 to 1.0)
     * @return Vector3 Interpolated position
     */
    public static function lerp(Vector3 $from, Vector3 $to, float $progress): Vector3 {
        $progress = max(0, min(1, $progress));

        return new Vector3(
            $from->x + ($to->x - $from->x) * $progress,
            $from->y + ($to->y - $from->y) * $progress,
            $from->z + ($to->z - $from->z) * $progress
        );
    }

    /**
     * Check if position is within bounds
     *
     * @param Vector3 $pos Position to check
     * @param Vector3 $min Minimum bounds
     * @param Vector3 $max Maximum bounds
     * @return bool True if within bounds
     */
    public static function isWithinBounds(Vector3 $pos, Vector3 $min, Vector3 $max): bool {
        return $pos->x >= $min->x && $pos->x <= $max->x &&
            $pos->y >= $min->y && $pos->y <= $max->y &&
            $pos->z >= $min->z && $pos->z <= $max->z;
    }

    /**
     * Get closest point on a line to a position
     *
     * @param Vector3 $point The point
     * @param Vector3 $lineStart Line start
     * @param Vector3 $lineEnd Line end
     * @return Vector3 Closest point on line
     */
    public static function getClosestPointOnLine(
        Vector3 $point,
        Vector3 $lineStart,
        Vector3 $lineEnd
    ): Vector3 {
        $line = $lineEnd->subtract($lineStart);
        $lineLength = $line->length();

        if ($lineLength < 0.001) {
            return $lineStart;
        }

        $pointToStart = $point->subtract($lineStart);
        $dot = ($pointToStart->x * $line->x +
                $pointToStart->y * $line->y +
                $pointToStart->z * $line->z) / ($lineLength * $lineLength);

        $dot = max(0, min(1, $dot));

        return $lineStart->addVector($line->multiply($dot));
    }

    /**
     * Rotate a vector around the Y axis
     *
     * @param Vector3 $vector Vector to rotate
     * @param float $angle Angle in degrees
     * @return Vector3 Rotated vector
     */
    public static function rotateAroundY(Vector3 $vector, float $angle): Vector3 {
        $rad = deg2rad($angle);
        $cos = cos($rad);
        $sin = sin($rad);

        $x = $vector->x * $cos - $vector->z * $sin;
        $z = $vector->x * $sin + $vector->z * $cos;

        return new Vector3($x, $vector->y, $z);
    }

    /**
     * Get grid positions in an area
     *
     * @param Vector3 $start Start corner
     * @param Vector3 $end End corner
     * @param float $spacing Spacing between points
     * @return Vector3[] Array of grid positions
     */
    public static function getGridPositions(
        Vector3 $start,
        Vector3 $end,
        float $spacing = 1.0
    ): array {
        $positions = [];

        $minX = min($start->x, $end->x);
        $maxX = max($start->x, $end->x);
        $minZ = min($start->z, $end->z);
        $maxZ = max($start->z, $end->z);
        $y = $start->y;

        for ($x = $minX; $x <= $maxX; $x += $spacing) {
            for ($z = $minZ; $z <= $maxZ; $z += $spacing) {
                $positions[] = new Vector3($x, $y, $z);
            }
        }

        return $positions;
    }

    /**
     * Get random position in cuboid
     *
     * @param Vector3 $min Minimum corner
     * @param Vector3 $max Maximum corner
     * @return Vector3 Random position
     */
    public static function getRandomInCuboid(Vector3 $min, Vector3 $max): Vector3 {
        return new Vector3(
            $min->x + mt_rand() / mt_getrandmax() * ($max->x - $min->x),
            $min->y + mt_rand() / mt_getrandmax() * ($max->y - $min->y),
            $min->z + mt_rand() / mt_getrandmax() * ($max->z - $min->z)
        );
    }
}