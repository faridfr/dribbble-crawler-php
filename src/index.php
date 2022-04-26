<?php 
require __DIR__ . '\..\vendor\autoload.php';

use Goutte\Client;
$client = new Client();
$data = [];
$crawler = $client->request('GET', 'https://www.dribbble.com');
$crawler->filter('.shot-thumbnail-link')->each(function ($node) use(&$data,$client) {
    // print $node->text(). '-'. $node->attr('href')."\n";

    $item = new StdClass();
    $item->colors = [];
    $item->href = $node->attr('href');
    $item->text = $node->text();

    $new = $client->click($node->link());
    $new->filter('head > title')->each(function ($node) use(&$data,$client,$item) {
        $item->title = $node->text();
    });
    $new->filter('li.color > a')->each(function ($node) use(&$data,$client,$item) {
        array_push($item->colors, $node->text());
    });

    array_push($data,$item);
});

var_dump($data);
