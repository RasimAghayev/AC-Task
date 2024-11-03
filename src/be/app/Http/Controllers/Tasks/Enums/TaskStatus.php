<?php

namespace App\Http\Controllers\Tasks\Enums;


enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case UNDER_REVIEW = 'under_review';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Gözləyir',
            self::IN_PROGRESS => 'İcra edilir',
            self::UNDER_REVIEW => 'Yoxlanılır',
            self::COMPLETED => 'Tamamlandı',
            self::CANCELLED => 'Ləğv edildi',
        };
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::UNDER_REVIEW => 'yellow',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
        };
    }

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}