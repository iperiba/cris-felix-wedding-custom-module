<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

use CrisFelixWeddingCustomModule\Entities\Implementations\Guest;

class EntityGenerator
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function generateGuestEntity(array $postArray)
    {
        return new Guest(
            $postArray["guest_name"],
            $postArray["surname"],
            $postArray["nid"],
            $postArray["email"],
            $postArray["phone"],
            $postArray["days"],
            $postArray["upper_age"],
            $postArray["menu_type"],
            $postArray["allergens"],
            $postArray["extra_service"],
            $postArray["special_requirements"],
            $postArray["spotify_song"]
        );
    }
}




