<?php

include './vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;

// 1. visit profile page
$curl = curl_init('https://speakerdeck.com/marcelgsantos');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

// 2. get all slides links
$crawler = new Crawler($response);
$links = $crawler->filter('a[title]');
$slides = $links->each(function(Crawler $item, $i) {
    return [
        'id' => $i,
        'href' => 'https://speakerdeck.com' . trim($item->attr('href')),
        'description' => trim($item->attr('title')),
    ];
});


foreach ($slides as $key => $slide) {
    // 3. visit slides page
    $curl = curl_init($slide['href']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    // 4. get all the data
    $crawler = new Crawler($response);
    $title = $crawler->filter('.container h1')->text();
    $description = $crawler->filter('.container p')->text();
    $stars = $crawler->filter('.js-stargazer')->text();
    $views = $crawler->filter('span[title*="view"]')->attr('title');

    print_r([
        'title' => $title,
        'description' => $description,
        'stars' => trim($stars),
        'views' => preg_replace('/\D/', '', trim($views)),
    ]);
}