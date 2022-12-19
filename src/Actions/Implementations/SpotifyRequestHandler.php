<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

class SpotifyRequestHandler
{
    const TABLE_NAME = 'send_email';
    const FORM_INSTANCE_HASH_COLUMN = 'form_instance_hash';
    const PLAYLIST_ID = 'send_email';
    private $logger;
    private $spotifyAuthorizationKey;
    private $spotifyApi;

    public function __construct($spotifyAuthorizationKey, $spotifyApi, $logger)
    {
        $this->logger = $logger;
        $this->spotifyAuthorizationKey = $spotifyAuthorizationKey;
        $this->spotifyApi = $spotifyApi;
    }

    public function addTrackToPlaylist() {
        $this->spotifyApi->addPlaylistTracks(WP_PLAYLIST_ID, [
            '0khVEzctbwmqLvOpg1ecbg'
        ]);
    }
}




