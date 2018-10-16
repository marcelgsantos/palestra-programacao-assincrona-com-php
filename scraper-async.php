<?php

include './vendor/autoload.php';

use React\EventLoop\Factory;
use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

$loop = Factory::create();
$client = new Browser($loop);

$client
    ->get('https://speakerdeck.com/marcelgsantos')
    ->then(function(ResponseInterface $response) use ($client) {
        $crawler = new Crawler((string) $response->getBody());
        $links = $crawler->filter('a[title]');
        $slides = $links->each(function(Crawler $item, $i) {
            return [
                'id' => $i,
                'href' => 'https://speakerdeck.com' . trim($item->attr('href')),
                'description' => trim($item->attr('title')),
            ];
        });

        foreach($slides as $slide) {
            $client->get($slide['href'])->then(function(ResponseInterface $response) {
                $crawler = new Crawler((string) $response->getBody());
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
            });
        }
    });

$loop->run();