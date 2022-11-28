<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

use CrisFelixWeddingCustomModule\Entities\Implementations\Guest;

class EntityGenerator
{
    public static function generateGuestEntity(array $postArray)
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




