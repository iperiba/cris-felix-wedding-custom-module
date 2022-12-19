<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

class SpotifyKeyGetter
{
    const SPOTIFY_TABLE_NAME = 'spotify_authorization';
    const SPOTIFY_AUTHORIZATION_COLUMN = 'spotify_authorization';
    const SPOTIFY_AUTHORIZATION_DATETIME_COLUMN = 'spotify_authorization_datetime ';
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function getSpotifyAuthorizationKeyAndDatetime()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::SPOTIFY_TABLE_NAME;

        $query = "SELECT * FROM $table_name ORDER BY " .
            self::SPOTIFY_AUTHORIZATION_DATETIME_COLUMN . " DESC LIMIT 1";

        $resultsArray = $wpdb->get_results( $query, ARRAY_A);

        if (!is_null($resultsArray) && is_array($resultsArray) && !empty($resultsArray)) {
            $countResults = count($resultsArray);
            $selectedResult = $resultsArray[$countResults-1];
            return $selectedResult;
        } else {
            throw new \Exception(__FILE__ . ": custom error -> spotify key could not be retrieved");
        }
    }

    public function renewSpotifyApi()
    {

    }
}




