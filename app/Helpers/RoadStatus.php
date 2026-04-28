<?php
/**
 * SISMAP Status Helper
 * Single source of truth for all road condition statuses.
 */

namespace App\Helpers;

class RoadStatus
{
    const VALID_STATUSES = ['baik', 'sedang', 'rusak_ringan', 'rusak_berat'];

    const LABELS = [
        'baik'         => 'Baik',
        'sedang'       => 'Sedang',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat'  => 'Rusak Berat',
    ];

    const COLORS = [
        'baik'         => '#10b981',
        'sedang'       => '#f59e0b',
        'rusak_ringan' => '#f97316',
        'rusak_berat'  => '#e11d48',
    ];

    // Normalize legacy statuses
    const LEGACY_MAP = [
        'rusak' => 'rusak_berat',
    ];

    public static function normalize(string $status): string
    {
        $s = strtolower(trim($status));
        return self::LEGACY_MAP[$s] ?? (in_array($s, self::VALID_STATUSES) ? $s : 'baik');
    }

    public static function label(string $status): string
    {
        return self::LABELS[self::normalize($status)] ?? 'Baik';
    }

    public static function color(string $status): string
    {
        return self::COLORS[self::normalize($status)] ?? '#10b981';
    }

    public static function isDamaged(string $status): bool
    {
        return in_array(self::normalize($status), ['rusak_ringan', 'rusak_berat']);
    }
}
