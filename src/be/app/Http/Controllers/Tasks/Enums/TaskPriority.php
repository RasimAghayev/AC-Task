<?php

namespace App\Http\Controllers\Tasks\Enums;


enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    /**
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::LOW => 'Aşağı',
            self::MEDIUM => 'Orta',
            self::HIGH => 'Yüksək',
            self::URGENT => 'Təcili',
        };
    }

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::LOW => '#8BB9DD',
            self::MEDIUM => '#FFB347',
            self::HIGH => '#FF6B6B',
            self::URGENT => '#DC3545',
        };
    }
}
