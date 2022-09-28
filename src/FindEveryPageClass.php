<?php

namespace Kryuko\FindEveryPage;

use Symfony\Component\DomCrawler\Crawler;

final class FindEveryPageClass
{
    private string $baseUrl = '';
    
    private array $urlsArray = [];
    
    private int $sleepTime = 1;
    
    private bool $enableLog = false;
    
    private $logFileName = 'final_log.json';
    
    public function __construct() { }

    public function setBaseData($data)
    {
        $this->baseUrl = (($data['baseUrl']) ? $data['baseUrl'] : $this->baseUrl);
        $this->sleepTime = ((@$data['sleepTime']) ? @$data['sleepTime'] : $this->sleepTime);
        $this->enableLog = ((@$data['enableLog']) ? @$data['enableLog'] : $this->enableLog);
        $this->logFileName = (@($data['logFileName']) ?@ $data['logFileName'] : $this->logFileName);
    }

    public function automate()
    {
        if (strlen($this->baseUrl) < 4) {
            $this->printlog('>>> BASE URL NOT FOUND!!!', '!!!ERROR!!!');
            exit();
        }

        $this->updateArrayWithCurrentPageUrls($this->baseUrl);

        while ( !$this->hasCrawlingDoneYet($this->urlsArray) ) {
            foreach ($this->urlsArray as $key) {
                if ($key['done']=='no') {
                    $this->updateArrayWithCurrentPageUrls($key['url']);
                }
                // var_dump($key);
            }
        }

        if ($this->enableLog) {
            file_put_contents($this->logFileName, json_encode($this->urlsArray));
        }
        return $this->urlsArray;
    }

    public function updateArrayWithCurrentPageUrls($url)
    {
        $this->printlog('>>> DOWNLOADING PAGE > '.$url, '___START_CRAWLING___');

        $page = file_get_contents($url);
        $crawler = new Crawler($page);

        $this->urlsArray[ $url ]['url'] = $url;
        $this->urlsArray[ $url ]['done'] = 'yes';

        foreach ($crawler->filter('a') as $a) {
            $node = new Crawler($a);
            $currentUrl = $node->extract(['href'])[0];
            // var_dump($currentUrl);
            if ( 
                $this->urlStartsWith($currentUrl, $this->baseUrl) && 
                !@$this->urlsArray[ $currentUrl ] &&
                !$this->endsWith($currentUrl, '.png') &&
                !$this->endsWith($currentUrl, '.jpg') &&
                !$this->endsWith($currentUrl, '.jpeg') &&
                !$this->endsWith($currentUrl, '.pdf') &&
                !$this->endsWith($currentUrl, '.csv') &&
                !$this->endsWith($currentUrl, '.json')
            ) {
                $this->urlsArray[ $currentUrl ]['url'] = $currentUrl;
                $this->urlsArray[ $currentUrl ]['done'] = 'no';
            }
        }
        $this->printlog('Time: '. $this->sleepTime . ' seconds.' ,'....Sleeping....');
        sleep($this->sleepTime);
    }

    public function hasCrawlingDoneYet($urlsArray)
    {
        foreach ($urlsArray as $key) {
            if ($key['done']=='no') {
                return false;
            }
        }
        
        return true;
    }
    
    public function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        
        return (substr($string, -$len) === $endString);
    }

    public function urlStartsWith($url, $start)
    {
        $url_finder = preg_match(';(^'.str_replace('/', '\/', $start).');', $url, $url_found );
        if (@$url_found[0]) {
            return true;
        }
         
        return false;
    }
    
    public function printlog($string, $title = null)
    {
        echo '/// '. (($title) ? $title : 'TITLE') .' ///' . PHP_EOL;
        echo $string;
        echo PHP_EOL . '/// ------------- ///' . PHP_EOL . PHP_EOL;
    }

    public function dumplog($string, $title = null)
    {
        echo '/// '. (($title) ? $title : 'TITLE') .' ///' . PHP_EOL;
        var_dump($string);
        echo PHP_EOL . '/// ------------- ///' . PHP_EOL . PHP_EOL;
    }

}
