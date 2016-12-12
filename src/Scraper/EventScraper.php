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
use GuzzleHttp\Client;

class EventScraper
{
    const EVENTS_PATH = '/ajax/events/eventsIndex.php';

    /**
     * @var string
     */
    private $url;

    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * EventScraper constructor.
     * @param $url
     * @param Cookie $cookie
     */
    public function __construct($url, Cookie $cookie)
    {
        $this->url = $url;
        $this->cookie = $cookie;
    }

    /**
     * Find events by timestamp range.
     *
     * @param int $start
     * @param int $end
     * @param int|null $cameraId
     * @return Event[]
     */
    public function getEvents($start, $end, $cameraId = null)
    {
        $client = new Client([
            'base_uri' => $this->url,
            'verify' => false
        ]);

        $response = $client->get(self::EVENTS_PATH, array(
            'query' => array(
                'startDate' => $start,
                'endDate' => $end
            ),
            'headers' => array(
                'Cookie' => $this->cookie->getValue()
            )
        ));

        $eventsXml = simplexml_load_string($response->getBody()->getContents());

        $events = array();
        foreach ($eventsXml->children() as $child) {

            if ($child->getName() == 'entry') {

                $children = $child->children();

                // No way to server-side filter on camera ID... Gah.
                $term = $children->category->attributes()['term'];
                if ($cameraId && !preg_match('#' . $cameraId . '/warn/#', $term . '#')) {
                    // Didn't match the requested camera ID
                    continue;
                }

                $events[] = new Event(
                    intval($children->id->attributes()['raw']),
                    strval($children->title),
                    strtotime(strval($children->published)),
                    strtotime(strval($children->updated)),
                    intval($children->content->attributes()['media_id']),
                    strval($children->content),
                    intval($children->content->attributes()['media_size'])
                );
            }
        }

        return $events;
    }
}
