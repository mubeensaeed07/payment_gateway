<?php

namespace App\Helpers;

class SlabHelper
{
    /**
     * Fixed slab ranges for all admins
     */
    public static function getFixedSlabRanges()
    {
        return [
            1 => ['from' => 0, 'to' => 10000, 'label' => 'Up to 10K'],
            2 => ['from' => 10000, 'to' => 100000, 'label' => '10K+ to 100K'],
            3 => ['from' => 100000, 'to' => 250000, 'label' => '100K+ to 250K'],
            4 => ['from' => 250000, 'to' => 1000000, 'label' => '250K+ to 1Mln'],
            5 => ['from' => 1000000, 'to' => 2500000, 'label' => '1M+ to 2.5M'],
            6 => ['from' => 2500000, 'to' => 5000000, 'label' => '2.5M+ to 5M'],
            7 => ['from' => 5000000, 'to' => null, 'label' => '5M+'],
        ];
    }

    /**
     * Fixed 1Link fees for each slab
     */
    public static function getFixedOnelinkFees()
    {
        return [
            1 => 12.5,      // Up to 10K
            2 => 31.25,    // 10K+ to 100K
            3 => 62.5,     // 100K+ to 250K
            4 => 125,      // 250K+ to 1Mln
            5 => 250,      // 1M+ to 2.5M
            6 => 375,      // 2.5M+ to 5M
            7 => 500,      // 5M+
        ];
    }

    /**
     * Get 1Link fee for a specific slab number
     */
    public static function getOnelinkFee($slabNumber)
    {
        $fees = self::getFixedOnelinkFees();
        return $fees[$slabNumber] ?? 0;
    }

    /**
     * Get slab range for a specific slab number
     */
    public static function getSlabRange($slabNumber)
    {
        $ranges = self::getFixedSlabRanges();
        return $ranges[$slabNumber] ?? null;
    }
}

