<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

use CrisFelixWeddingCustomModule\Entities\Interfaces\GuestInterface;

class GuestUploader
{
    const POST_TYPE = 'guest';
    const POST_STATUS = 'publish';
    const POST_AUTHOR = 1;

    public static function uploadGuest(GuestInterface $guestEntity): GuestInterface
    {
        $post_id = wp_insert_post(array (
            'post_type' => self::POST_TYPE,
            'post_status' => self::POST_STATUS,
            'post_author' => self::POST_AUTHOR,
        ));

        if ($post_id) {
            add_post_meta($post_id, 'guest_name', $guestEntity->getGuestName());
            add_post_meta($post_id, 'surname', $guestEntity->getSurname());
            add_post_meta($post_id, 'nid', $guestEntity->getNid());
            add_post_meta($post_id, 'email', $guestEntity->getMail());
            add_post_meta($post_id, 'phone', $guestEntity->getPhone());
            add_post_meta($post_id, 'days', $guestEntity->getDays());
            add_post_meta($post_id, 'upper_age', $guestEntity->isUpperAge());
            add_post_meta($post_id, 'menu_type', $guestEntity->getMenuType());
            add_post_meta($post_id, 'allergens', $guestEntity->getAllergens());
            add_post_meta($post_id, 'extra_service', $guestEntity->getExtraService());
            add_post_meta($post_id, 'special_requirements', $guestEntity->getSpecialRequirements());
            add_post_meta($post_id, 'spotify_song', $guestEntity->getSpotifySong());
        }
    }
}




