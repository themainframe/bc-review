<?php
/*
 * This file is part of the bc-review package.
 *
 * (c) Damien Walsh <me@damow.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BcReview\Authentication;

use GuzzleHttp\Client;

class Cookie
{
    /**
     * @var string
     */
    private $value;

    /**
     * Cookie constructor.
     * @param $url
     * @param $username
     * @param $password
     */
    public function __construct($url, $username, $password)
    {
        $client = new Client([
            'base_uri' => $url,
            'verify' => false
        ]);
        $response = $client->post('/login', array(
            'form_params' => array(
                'login' => $username,
                'password' => $password
            )
        ));

        if ($response->hasHeader('Set-Cookie')) {
            $this->value = $response->getHeader('Set-Cookie');
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
