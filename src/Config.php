<?php
/*
 * This file is part of the bc-review package.
 *
 * (c) Damien Walsh <me@damow.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BcReview;
use Symfony\Component\Yaml\Yaml;

/**
 * Configuration access.
 * @package BcReview
 */
class Config
{
    private static $username;
    private static $password;
    private static $url;

    public static function getConfig($path)
    {
        $yaml = new Yaml();
        $config = $yaml->parse(file_get_contents($path));

        self::$username = $config['username'];
        self::$password = $config['password'];
        self::$url = $config['url'];
    }

    /**
     * @return mixed
     */
    public static function getUsername()
    {
        return self::$username;
    }

    /**
     * @return mixed
     */
    public static function getPassword()
    {
        return self::$password;
    }

    /**
     * @return mixed
     */
    public static function getUrl()
    {
        return self::$url;
    }
}
