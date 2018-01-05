<?php

namespace Canopy\Bundle\UserBundle\Event;

use Canopy\Bundle\UserBundle\Entity\Media;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class MediaEvent.
 *
 * @deprecated Should be removed.
 */
class MediaEvent extends Event
{
    /**
     * @var Media
     */
    protected $media;

    /**
     * Constructor.
     *
     * @param Media $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }
}
