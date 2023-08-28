<?php

namespace App\Business;

class ExcludeCategory
{
    private static $excludeCategory = [
        'limpeza',
        'jardim, bricolage e auto',
        'animais',
        'brinquedos',
        'brinquedos e jogos',
        'livraria e papelaria',
        'beleza e higiene',
        'desporto, bagagens, roupa',
        'casa, mobiliário, decoração',
        'nutricosmética',
        'bebe',
        'limpeza',

    ];

    public static function check(string $category)
    {
        $category = strtolower($category);

        return in_array($category, self::$excludeCategory);
    }
}
