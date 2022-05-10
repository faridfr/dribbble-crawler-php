<?php 
require __DIR__ . '\..\vendor\autoload.php';

use Goutte\Client;
use Symfony\Component\CssSelector\CssSelectorConverter;


$client = new Client();
$data = [];
$crawler = $client->request('GET', 'https://dribbble.com');
$crawler->filter('.shot-thumbnail-link')->each(function ($node) use(&$data,$client) {
    // print $node->text(). '-'. $node->attr('href')."\n";

    $item = new StdClass();
    $item->colors = [];
    $item->info = [];
    $item->tags = [];
    $item->href = $node->attr('href');
    $item->text = $node->text();
    $page = $client->click($node->link());

    getThemeTitle($page,$item);
    getThemeColors($page,$item);
    getThemeInfo($page,$item);
    getThemeTags($page,$item);

    array_push($data,$item);
});

if (file_put_contents("data.json", json_encode($data)))
    echo "JSON file created successfully...";
else 
    echo "Oops! Error creating json file...";

function getThemeTitle($page,$item){
     $page->filter('head > title')->each(function ($node) use($item) {
        $item->title = $node->text();
    });
}

function getThemeColors($page,$item){
    $page->filter('li.color > a')->each(function ($node) use($item) {
        array_push($item->colors, $node->text());
    });
}

function getThemeInfo($page,$item){
    $converter = new CssSelectorConverter();
     $page->filterXPath($converter->toXPath('div.font-body-large div.margin-t-8'))->each(function ($node) use($item) {
        array_push($item->info, $node->text());
    });
}

function getThemeTags($page,$item){
    $converter = new CssSelectorConverter();
    $page->filterXPath($converter->toXPath('a[href*="/tags/"]'))->each(function ($node) use($item) {
        array_push($item->tags, $node->text());
    });
}
