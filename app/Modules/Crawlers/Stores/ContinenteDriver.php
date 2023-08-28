<?php

namespace App\Modules\Crawlers\Stores;

use App\Business\ExcludeCategory;
use App\Business\SystemAPi;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class ContinenteDriver extends CrawlObserver
{
    private $store = 'Continente';

    public function crawled(UriInterface $url, ResponseInterface $response, UriInterface $foundOnUrl = null, string $linkText = null,): void
    {
        try {
            dump($url->getScheme() . '://' . $url->getHost() . $url->getPath());
            if ($response->getStatusCode() !== 200) {
                dump('Not a 200');
                return;
            }

            if (strpos($url->getPath(), 'produto') === false) {
                dump('Not a product');
                return;
            }

            $doc = new \DOMDocument();
            @$doc->loadHTML($response->getBody());
            $xpath = new \DOMXPath($doc);

            $categoryElement = $xpath->query('//*[@id="maincontent"]/div/div/div[1]/div/div/div/div/ul/li[3]/a/span');
            $firstCategory = $this->clearString($categoryElement->item(0)->nodeValue);

            if (ExcludeCategory::check($firstCategory)) {
                dump('Category excluded');
                return;
            }

            $categoryElement = $xpath->query('//*[@id="maincontent"]/div/div/div[1]/div/div/div/div/ul/li[5]/a/span');
            $secondCategory = $this->clearString($categoryElement->item(0)->nodeValue);

            if (ExcludeCategory::check($secondCategory)) {
                dump('Category excluded');
                return;
            }

            $categoryElement = $xpath->query('//*[@id="maincontent"]/div/div/div[1]/div/div/div/div/ul/li[7]/a/span');
            $lastCategory = $this->clearString($categoryElement->item(0)->nodeValue);

            if (ExcludeCategory::check($lastCategory)) {
                dump('Category excluded');
                return;
            }

            $productNameElement = $xpath->query('//h1[@class="pwc-h3 col-h3 product-name pwc-font--primary-extrabold mb-0"]');
            $productName = $this->clearString($productNameElement->item(0)->nodeValue);

            $productBrandElement = $xpath->query('//*[@class="ct-pdp--brand col-pdp--brand"]');
            $productBrand = $this->clearString($productBrandElement->item(0)->nodeValue);

            $detailsElements = $xpath->query('//a[@data-url]/@data-url');
            $detailsString = $detailsElements->item(0)->nodeValue;

            $imageElement = $xpath->query('//*[@id="slick-slide00"]/img');
            if ($imageElement->item(0)!=null){
                $image = $imageElement->item(0)->getAttribute('src');
            }else{
                $imageElement = $xpath->query('//*[@id="maincontent"]/div/div/div[2]/div[1]/div/div[1]/div/img');
                $image = $imageElement->item(0)->getAttribute('src');
            }

            parse_str($detailsString, $params);
            $eanCode = $params['ean'];


            $LegalInformationElement = $xpath->query('//*[@id="collapsible-description-nutri"]');
            if ($LegalInformationElement->item(0)==null){
                $LegalInformationElement = $xpath->query('//*[@id="collapsible-description-3"]');
            }
            $LegalInformation = $LegalInformationElement->item(0)->nodeValue;
            $LegalInformation = $this->removeJsChars(utf8_decode($LegalInformation));

            SystemAPi::storeProduct([
                'product_name' => $productName,
                'brand' => $productBrand,
                'ean' => $eanCode,
                'main_category' => $firstCategory,
                'sub_category' => $secondCategory,
                'image' => $image,
                'url' => $url->getScheme() . '://' . $url->getHost() . $url->getPath(),
                'information' => $LegalInformation,
                'store' => $this->store,
            ]);
        }catch (\Exception $e) {
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
