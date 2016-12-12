<?php
/*
 * This file is part of the bc-review package.
 *
 * (c) Damien Walsh <me@damow.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BcReview\Model;

class Event
{
    private $title;
    private $published;
    private $updated;
    private $mediaId;
    private $mediaUrl;
    private $mediaSize;
    private $mediaPath;
    private $id;

    /**
     * Event constructor.
     * @param $id
     * @param $title
     * @param $published
     * @param $updated
     * @param $mediaId
     * @param $imageUrl
     * @param $mediaSize
     */
    public function __construct($id, $title, $published, $updated, $mediaId, $imageUrl, $mediaSize)
    {
        $this->title = $title;
        $this->published = $published;
        $this->updated = $updated;
        $this->mediaId = $mediaId;
        $this->mediaUrl = $imageUrl;
        $this->mediaSize = $mediaSize;
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return mixed
     */
    public function getMediaUrl()
    {
        return $this->mediaUrl;
    }

    /**
     * @return mixed
     */
    public function getMediaSize()
    {
        return $this->mediaSize;
    }

    /**
     * @return mixed
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getMediaPath()
    {
        return $this->mediaPath;
    }

    /**
     * @param mixed $mediaPath
     * @return Event
     */
    public function setMediaPath($mediaPath)
    {
        $this->mediaPath = $mediaPath;
        return $this;
    }
}
