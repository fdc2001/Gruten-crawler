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
        'papelaria e livraria',
        'Regresso às Aulas',
        'Tecnologia e Eletrodomésticos',
        'Tecnologia e Eletrodomésticos',
        'Regresso às Aulas',

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
