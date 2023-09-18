<?php

namespace App\Modules\Crawlers\Stores;

use App\Business\ExcludeCategory;
use App\Business\SystemAPi;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class AuchanDriver extends CrawlObserver
{
    private $store = 'Auchan';

    public function crawled(UriInterface $url, ResponseInterface $response, UriInterface $foundOnUrl = null, string $linkText = null,): void
    {
        try {
            dump($url->getScheme() . '://' . $url->getHost() . $url->getPath());
            if ($response->getStatusCode() !== 200) {
                dump('Not a 200');
                return;
            }

            $doc = new \DOMDocument();
            @$doc->loadHTML($response->getBody());
            $xpath = new \DOMXPath($doc);

            if($xpath->query('//*[@id="maincontent"]/div[2]/div[1]/div[2]/div/div[1]/div/div[2]/span[2]')->item(0) === null) {
                dump('Not a product');
                return;
            }

            $categorys = [];
            $categoryElement = $xpath->query('//*[@id="maincontent"]/div[1]/div/div[1]/div/div/ol/li[@class="breadcrumb-item"]/a');
            foreach ($categoryElement as $category){
                $possibleCategory = $this->clearString($category->nodeValue);
                if (ExcludeCategory::check($possibleCategory)) {
                    dump('Category excluded');
                    return;
                }
                $categorys[] = $possibleCategory;
            }

            $eanElement = $xpath->query('//*[@id="maincontent"]/div[2]/div[1]/div[2]/div/div[1]/div/div[2]/span[2]');
            $eanCode = $this->clearString($eanElement->item(0)->nodeValue);

            $productNameElement = $xpath->query('//*[@id="maincontent"]/div[1]/div/div[2]/div/h1');
            $productName = $this->clearString($productNameElement->item(0)->nodeValue);

            $jsonProductElement = $xpath->query('//*/script[@type="application/ld+json"]');
            $jsonProduct = json_decode($jsonProductElement->item(0)->nodeValue);

            $productBrand = $jsonProduct->brand->name;

            $images = $jsonProduct->image;


            $ingredientsElement = $xpath->query('//*/div[@class="col-12 value content auc-pdp__accordion-body auc-pdp__attribute-container"]');
            $ingredients = $ingredientsElement->item(0)->nodeValue;
            $ingredients = $this->removeJsChars(utf8_decode($ingredients));

            SystemAPi::storeProduct([
                'product_name' => $productName,
                'brand' => $productBrand,
                'ean' => $eanCode,
                'main_category' => \Arr::first($categorys),
                'sub_category' => \Arr::last($categorys),
                'images' => $images,
                'url' => $url->getScheme() . '://' . $url->getHost() . $url->getPath(),
                'information' => $ingredients,
                'store' => $this->store,
            ]);
        }catch (\Exception $e) {
            #dump($e->getMessage());
            logs()->error($e->getMessage(), [
                'url' => $url->getScheme() . '://' . $url->getHost() . $url->getPath(),
                'store' => $this->store,
                'Line'=>$e->getLine()
            ]);
            SystemAPi::registerError($this->store, $url->getScheme() . '://' . $url->getHost() . $url->getPath(), $e);
        }


        #dd($productName, $productBrand,$eanCode, $firstCategory, $secondCategory, $image, $url->getScheme() . '://' . $url->getHost() . $url->getPath(), $LegalInformation, $this->store);
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, UriInterface $foundOnUrl = null, string $linkText = null,): void
    {
        dump($url, $requestException, $foundOnUrl, $linkText);
    }

    private function clearString($text){
        $text = str_replace("\n", '', $text);
        $text = str_replace("\r", '', $text);
        $text = str_replace("\t", '', $text);
        $text = str_replace("\u{A0}", ' ', $text);
        $text = utf8_decode($text);

        return trim($text);
    }

    private function removeJsChars($text){
        $text = str_replace("\u{A0}", ' ', $text);
        return trim($text);
    }


}
