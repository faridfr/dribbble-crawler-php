<?php 
require __DIR__ . '\..\vendor\autoload.php';

use Goutte\Client;
$client = new Client();
$data = [];
$crawler = $client->request('GET', 'https://www.dribbble.com');
$crawler->filter('.shot-thumbnail-link')->each(function ($node) use(&$data) {
    // print $node->text(). '-'. $node->attr('href')."\n";

    $item = new StdClass();
    $item->href = $node->attr('href');
    $item->text = $node->text();
    array_push($data,$item);
});

var_dump($data);
