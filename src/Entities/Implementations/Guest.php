<?php

namespace CrisFelixWeddingCustomModule\Entities\Implementations;

use CrisFelixWeddingCustomModule\Entities\Interfaces\GuestInterface;

class Guest implements GuestInterface
{
	private string $guest_name;
	private string $surname;
	private string $nid;
    private string $mail;
	private string $phone;
	private array $days;
	private bool $upper_age;
	private string $menu_type;
	private string $allergens;
	private array $extra_service;
	private string $special_requirements;
	private string $spotify_song;
    private string $spotify_song02;
    private string $spotify_song03;

    /**
     * Guest constructor.
     * @param string $guest_name
     * @param string $surname
     * @param string $nid
     * @param string $mail
     * @param string $phone
     * @param array $days
     * @param bool $upper_age
     * @param string $menu_type
     * @param string $allergens
     * @param array $extra_service
     * @param string $special_requirements
     * @param string $spotify_song
     * @param string $spotify_song02
     * @param string $spotify_song03
     */
    public function __construct($guest_name, $surname, $nid, $mail, $phone, $days, $upper_age, $menu_type, $allergens, $extra_service, $special_requirements, $spotify_song, $spotify_song02, $spotify_song03)
    {
        $this->guest_name = $guest_name;
        $this->surname = $surname;
        $this->nid = $nid;
        $this->mail = $mail;
        $this->phone = $phone;
        $this->days = $days;
        $this->upper_age = $upper_age;
        $this->menu_type = $menu_type;
        $this->allergens = $allergens;
        $this->extra_service = $extra_service;
        $this->special_requirements = $special_requirements;
        $this->spotify_song = $spotify_song;
        $this->spotify_song02 = $spotify_song02;
        $this->spotify_song03 = $spotify_song03;
    }

    /**
     * @return string
     */
    public function getGuestName()
    {
        return $this->guest_name;
    }

    /**
     * @param string $guest_name
     */
    public function setGuestName($guest_name)
    {
        $this->guest_name = $guest_name;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return string
     */
    public function getNid()
    {
        return $this->nid;
    }

    /**
     * @param string $nid
     */
    public function setNid($nid)
    {
        $this->nid = $nid;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return array
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param array $days
     */
    public function setDays($days)
    {
        $this->days = $days;
    }

    /**
     * @return bool
     */
    public function isUpperAge()
    {
        return $this->upper_age;
    }

    /**
     * @param bool $upper_age
     */
    public function setUpperAge($upper_age)
    {
        $this->upper_age = $upper_age;
    }

    /**
     * @return string
     */
    public function getMenuType()
    {
        return $this->menu_type;
    }

    /**
     * @param string $menu_type
     */
    public function setMenuType($menu_type)
    {
        $this->menu_type = $menu_type;
    }

    /**
     * @return string
     */
    public function getAllergens()
    {
        return $this->allergens;
    }

    /**
     * @param string $allergens
     */
    public function setAllergens($allergens)
    {
        $this->allergens = $allergens;
    }

    /**
     * @return array
     */
    public function getExtraService()
    {
        return $this->extra_service;
    }

    /**
     * @param array $extra_service
     */
    public function setExtraService($extra_service)
    {
        $this->extra_service = $extra_service;
    }

    /**
     * @return string
     */
    public function getSpecialRequirements()
    {
        return $this->special_requirements;
    }

    /**
     * @param string $special_requirements
     */
    public function setSpecialRequirements($special_requirements)
    {
        $this->special_requirements = $special_requirements;
    }

    /**
     * @return string
     */
    public function getSpotifySong()
    {
        return $this->spotify_song;
    }

    /**
     * @param string $spotify_song
     */
    public function setSpotifySong($spotify_song)
    {
        $this->spotify_song = $spotify_song;
    }

    /**
     * @return string
     */
    public function getSpotifySong02()
    {
        return $this->spotify_song02;
    }

    /**
     * @param string $spotify_song02
     */
    public function setSpotifySong02($spotify_song02)
    {
        $this->spotify_song02 = $spotify_song02;
    }

    /**
     * @return string
     */
    public function getSpotifySong03()
    {
        return $this->spotify_song03;
    }

    /**
     * @param string $spotify_song03
     */
    public function setSpotifySong03($spotify_song03)
    {
        $this->spotify_song03 = $spotify_song03;
    }
}
