<?php

namespace Modules\Attendance\Enums;

enum AttendanceStatus: string
{
    CASE PRESENT = 'present';
    CASE ABSENT = 'absent';
    CASE LATE = 'late';
    CASE HALF_DAY = 'half_day';
    CASE ON_LEAVE = 'on_leave';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
