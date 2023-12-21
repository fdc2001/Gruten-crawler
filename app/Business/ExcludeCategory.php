<?php

namespace App\Business;

class ExcludeCategory
{
    private static $excludeCategory = [

    ];

    public static function check(string $category)
    {
        $category = strtolower($category);
        $excludeCategory = self::$excludeCategory;

        $excludeCategory = array_map(function ($item) {
            return strtolower($item);
        }, $excludeCategory);
        return in_array($category, $excludeCategory);
    }
}
