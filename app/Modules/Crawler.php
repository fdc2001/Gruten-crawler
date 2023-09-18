<?php

namespace App\Modules;

use App\Business\SystemAPi;
use App\Modules\Crawlers\Stores\AuchanDriver;
use App\Modules\Crawlers\Stores\ContinenteDriver;
use GuzzleHttp\RequestOptions;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;

class Crawler
{
    private array $stores;
    private string $driver;

    public function __construct()
    {
        $this->stores = SystemAPi::stores();
    }

    public function run()
    {
        foreach ($this->stores as $store) {
            $this->setDriver($store);
        }
    }

    private function getProxyServer(){
        $servers = [
            //'144.64.0.35:8080',

        ];

        return $servers[array_rand($servers)];
    }

    private function setDriver($store)
    {
        switch ($store['name']){
            case 'Continente':
                $this->driver = ContinenteDriver::class;
                break;
            case 'Auchan':
                $this->driver = AuchanDriver::class;
                break;
        }
        $this->runCrawler($store);
    }

    private function runCrawler($store)
    {
        \Spatie\Crawler\Crawler::create([
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::TIMEOUT => 60,
            RequestOptions::DELAY => 3000,
            //RequestOptions::PROXY => $this->getProxyServer(),
        ])
            ->acceptNofollowLinks()
            ->respectRobots()
            ->setConcurrency(1)
            ->setCrawlObserver(new $this->driver)
            ->setCrawlProfile(new CrawlInternalUrls($store['url']))
            //->setTotalCrawlLimit(10000)
            ->startCrawling($store['url']);

        return true;

    }
}
