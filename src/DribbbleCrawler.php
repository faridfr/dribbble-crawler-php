<?php

require __DIR__ . '\..\vendor\autoload.php';
use Goutte\Client;

/**
 * Class Dribbble Crawler
 */
class DribbbleCrawler {

    public $counter=1, $colors = [], $page_limit = 2000;

    public function __construct($page_limit = null){
        if(!is_null($page_limit))
            $this->page_limit = $page_limit;
        return $this->start_crawling($page_limit);
    }

    public function create_unique_random_hex(){
        while(1){
            $rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
            $color = $rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
            if(!in_array($color, $this->colors))
                break;
        }
        return $color;
    }

    public function start_crawling(){

        $client = new Client();
        $data = [];

        while($this->counter <= $this->page_limit){
            $crawler = $client->request('GET', 'https://dribbble.com/shots/popular/web-design?color='.$this->create_unique_random_hex());
            $crawler->filter('.shot-thumbnail-link')->each(function ($node) use(&$data,$client,$crawler) {
                
                // set page basic data
                $page = new StdClass();
                $page->colors = [];
                $page->info = [];
                $page->tags = [];
                $page->href = $node->attr('href');
                $page->text = $node->text();

                if($this->is_duplicate($data,$page))
                    print "Page ".$this->counter." ignored because it's duplicate\n";
                
                elseif($this->counter > $this->page_limit)
                    print "Page ".$this->counter." ignored because limitation\n";

                else {
                    // crawl to user interface pages and get their data
                    $crawler = $client->click($crawler->selectLink($node->text())->link());
                    $this->getUserInterfaceTitle($crawler,$page);
                    $this->getUserInterfaceColors($crawler,$page);
                    $this->getUserInterfaceInfo($crawler,$page);

                    // add page data to array if not exists
                    array_push($data,$page);
                    print "Page ".$this->counter++." ( ".$page->title." ) added to array\n";
                }
               
            });
        }

        // save all data in a json file
        if (file_put_contents("data.json", json_encode($data)))
            echo "JSON file created successfully...";
        else 
            echo "Oops! Error creating json file...";
       
    }

    public function is_duplicate($data,$page){
        $duplicate = 0;
        foreach($data as $item){
            if($item->href == $page->href)
            {
                $duplicate = 1;
                break;
            }
        }
        return $duplicate;
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
        array_push($page->info,$matches[1] ? json_decode($matches[1]) : []);
    }

}