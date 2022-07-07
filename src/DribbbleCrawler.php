<?php

require __DIR__ . '\..\vendor\autoload.php';
use Goutte\Client;

/**
 * Class Dribbble Crawler
 */
class DribbbleCrawler {

    public $counter=1;
    public $colors = ['2516C7','eeeeee','aaaaaa'];

    public function __construct()
    {
        return $this->start_crawling();
    }

    public function start_crawling(){

        $client = new Client();
        $data = [];

        foreach($this->colors as $color){
            $crawler = $client->request('GET', 'https://dribbble.com/shots/popular/web-design?color='.$color);
            $crawler->filter('.shot-thumbnail-link')->each(function ($node) use(&$data,$client,$crawler) {
                
                // set page basic data
                $page = new StdClass();
                $page->colors = [];
                $page->info = [];
                $page->tags = [];
                $page->href = $node->attr('href');
                $page->text = $node->text();

                // crawl to user interface pages and get their data
                $crawler = $client->click($crawler->selectLink($node->text())->link());
                $this->getUserInterfaceTitle($crawler,$page);
                $this->getUserInterfaceColors($crawler,$page);
                $this->getUserInterfaceInfo($crawler,$page);

                // add page data to array
                array_push($data,$page);
                print "Page ".$this->counter++." ( ".$page->title." ) added to array\n";
            });
        }

        // save all data in a json file
        if (file_put_contents("data.json", json_encode($data)))
            echo "JSON file created successfully...";
        else 
            echo "Oops! Error creating json file...";
       
    }

    public function getUserInterfaceTitle($crawler,$page){
        $crawler->filter('head > title')->each(function ($node) use($page){
            $page->title = $node->text();
        });
    }

    public function getUserInterfaceColors($crawler,$page){
        $crawler->filter('li.color > a')->each(function ($node) use($page){
            array_push($page->colors, $node->text());
        });
    }

    public function getUserInterfaceInfo($crawler,$page){
        preg_match('#shotData: (.*?),\s*$#m', $crawler->outerHtml(), $matches);
        array_push($page->info,json_decode($matches[1]));
    }

}