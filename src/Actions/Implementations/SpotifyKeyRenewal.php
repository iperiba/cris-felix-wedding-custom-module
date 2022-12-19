<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

class SpotifyKeyRenewal
{
    const SPOTIFY_TABLE_NAME = 'spotify_authorization';
    const SPOTIFY_AUTHORIZATION_COLUMN = 'spotify_authorization';
    const SPOTIFY_AUTHORIZATION_DATETIME_COLUMN = 'spotify_authorization_datetime ';
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function renewSpotifyCode()
    {
        $ch = curl_init();
// set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, WP_SPOTIFY_REDIRECT_URI01);
        curl_setopt($ch, CURLOPT_HEADER, 0);

// grab URL and pass it to the browser
        curl_exec($ch);

// close cURL resource, and free up system resources
        curl_close($ch);
    }
}




