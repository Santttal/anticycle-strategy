<?php

namespace App\Enum;

class InstrumentEnum
{
    public const SP_500 = 'sp500';
    public const GOLD = 'gold';
    public const HIGH_YIELD = 'high_yield';
    public const T10Y2Y = 't10y2y';

    /**
     * @return string[]
     */
    public static function getValues(): array
    {
        return [
            self::SP_500,
            self::GOLD,
            self::HIGH_YIELD,
            self::T10Y2Y,
        ];
    }
}
