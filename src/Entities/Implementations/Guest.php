<?php

namespace CrisFelixWeddingCustomModule\Entities\Implementations;

use CrisFelixWeddingCustomModule\Entities\Interfaces\GuestInterface;

class Guest implements GuestInterface
{
	private string $name;
	private string $surname;
	private string $nid;
	private string $phone;
	private array $days;
	private bool $under_age;
	private string $menu_type;
	private string $allergens;
	private array $extra_service;
	private string $special_requirements;
	private string $spotify_song;

	/**
	 * Guess constructor.
	 * @param string $name
	 * @param string $surname
	 * @param string $nid
	 * @param string $phone
	 * @param array $days
	 * @param bool $under_age
	 * @param string $menu_type
	 * @param string $allergens
	 * @param array $extra_service
	 * @param string $special_requirements
	 * @param string $spotify_song
	 */
	public function __construct($name, $surname, $nid, $phone, array $days, $under_age, $menu_type, $allergens, array $extra_service, $special_requirements, $spotify_song)
	{
		$this->name = $name;
		$this->surname = $surname;
		$this->nid = $nid;
		$this->phone = $phone;
		$this->days = $days;
		$this->under_age = $under_age;
		$this->menu_type = $menu_type;
		$this->allergens = $allergens;
		$this->extra_service = $extra_service;
		$this->special_requirements = $special_requirements;
		$this->spotify_song = $spotify_song;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	public function isUnderAge()
	{
		return $this->under_age;
	}

	/**
	 * @param bool $under_age
	 */
	public function setUnderAge($under_age)
	{
		$this->under_age = $under_age;
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
}
