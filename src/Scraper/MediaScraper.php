<?php
/*
 * This file is part of the bc-review package.
 *
 * (c) Damien Walsh <me@damow.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BcReview\Scraper;

use BcReview\Authentication\Cookie;
use BcReview\Model\Event;
use BcReview\Model\Media;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class MediaScraper
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * MediaScraper constructor.
     * @param $url
     * @param Cookie $cookie
     */
    public function __construct($url, Cookie $cookie)
    {
        $this->url = $url;
        $this->cookie = $cookie;
    }

    /**
     * @param Event[] $events
     */
    public function getForEvents($events, $progress = null)
    {
        $client = new Client(array(
            'verify' => false
        ));

        // Key-ize the events
        $events = array_combine(array_map(function ($event) {
            return $event->getId();
        }, $events), $events);

        $requests = array_map(function ($event) {
            return new Request(
                'GET',
                $event->getMediaUrl() . '&mode=screenshot',
                array(
                    'Cookie' => $this->cookie->getValue(),
                )
            );
        }, $events);

        $pool = new Pool($client, $requests, [
            'concurrency' => 2,
            'fulfilled' => function ($response, $id) use ($events, $progress) {
                $path = __DIR__ . '/../../.cache/' . $events[$id]->getMediaId() . '.jpg';
                file_put_contents($path, $response->getBody()->getContents());
                $events[$id]->setMediaPath(realpath($path));
                if (is_callable($progress)) {
                    $progress();
                }
            },
            'rejected' => function ($reason, $index) use ($progress) {
                if (is_callable($progress)) {
                    $progress();
                }
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }
}
