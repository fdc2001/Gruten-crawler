<?php

namespace App\Business;

use Illuminate\Support\Facades\Http;

class SystemAPi
{
    private static $path = "/crawler";
    public static function stores()
    {
        $domain = config('system.domain');
        $stores = Http::get($domain . self::$path . '/stores');

        return $stores->json();
    }

    public static function storeProduct($product)
    {
        $domain = config('system.domain');
        $request = Http::post($domain . self::$path . '/product', $product);
        return $request->json();
    }

    public static function registerError($store, $url, \Exception $e )
    {
        $domain = config('system.domain');
        $request = Http::post($domain . self::$path . '/error', [
            'store' => $store,
            'url' => $url,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
        return $request->json();
    }
}
