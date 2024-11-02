<?php
namespace App\Http\Controllers\Tasks\Enums;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;

enum TaskRepeatTypeEnum: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case HALF_A_MONTH = 'half_a_month';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case CUSTOM = 'custom';


    /**
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::DAILY => 'Günlük',
            self::WEEKLY => 'Həftəlik',
            self::HALF_A_MONTH => 'Yarım aylıq',
            self::MONTHLY => 'Aylıq',
            self::YEARLY => 'İllik',
            self::CUSTOM => 'Xüsusi',
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
     * Calculate the next execution date based on the repeat type.
     * @param DateTime $startDate
     * @return DateTime|null
     * @throws InvalidArgumentException
     */
    public function getNextExecutionDate(DateTime $startDate): ?DateTime
    {
        $intervalSpec = match($this) {
            self::DAILY => 'P1D',
            self::WEEKLY => 'P1W',
            self::HALF_A_MONTH => 'P15D',
            self::MONTHLY => 'P1M',
            self::YEARLY => 'P1Y',
            self::CUSTOM => null,
        };

        if ($intervalSpec === null) {
            return null;
        }

        try {
            $nextDate = clone $startDate;
            $nextDate->add(new DateInterval($intervalSpec));
            return $nextDate;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid interval specification: {$intervalSpec}", 0, $e);
        }
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::DAILY => '#4CAF50',
            self::WEEKLY => '#2196F3',
            self::HALF_A_MONTH => '#673AB7',
            self::MONTHLY => '#FF9800',
            self::YEARLY => '#795548',
            self::CUSTOM => '#607D8B',
        };
    }
}