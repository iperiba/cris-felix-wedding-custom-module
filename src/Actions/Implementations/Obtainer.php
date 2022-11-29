<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

class Obtainer
{
    const POST_FIELDS_TYPE_FIELD = array(
        "guest_name" => "text",
        "surname" => "text",
        "nid" => "text",
        "email" => "email",
        "phone" => "text",
        "days" => "array",
        "upper_age" => "boolean",
        "menu_type" => "text",
        "allergens" => "textarea",
        "extra_service" => "array",
        "special_requirements" => "textarea",
        "spotify_song" => "url",
    );

    const POST_FIELDS_ENTITY_FIELDS = array(
        "guest_name" => "guest_name",
        "surname" => "surname",
        "nid" => "nid",
        "email" => "email",
        "phone" => "phone",
        "days" => "days",
        "upper_age" => "upper_age",
        "menu_type" => "menu_type",
        "allergens" => "allergens",
        "extra_service" => "extra_service",
        "special_requirements" => "special_requirements",
        "spotify_song" => "spotify_song",
    );

    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function obtainArrayFromPostPetition()
    {
        $postArray = array();

        foreach (self::POST_FIELDS_ENTITY_FIELDS as $key => $value) {
            if (is_null($nonTreatedPostValue = $_POST[$key])) {
                $this->logger->error(__FILE__ . ": custom error -> $key is missing from the compulsory array");
                continue;
            }

            $treatedPostValue = "";

            switch (self::POST_FIELDS_TYPE_FIELD[$key]) {
                case "text":
                    $treatedPostValue = sanitize_text_field($nonTreatedPostValue);
                    break;
                case "email":
                    $treatedPostValue = sanitize_email($nonTreatedPostValue);
                    break;
                case "textarea":
                    $treatedPostValue = sanitize_textarea_field($nonTreatedPostValue);
                    break;
                case "url":
                    $treatedPostValue = sanitize_url($nonTreatedPostValue);
                    break;
                case "array":
                    try {
                        $treatedPostValue = $this->arrayTreatment($nonTreatedPostValue, $key);
                    } catch (\Exception $e) {
                        $treatedPostValue = array();
                        $this->logger->error($e->getMessage());
                    }
                    break;
                case "boolean":
                    try {
                        $treatedPostValue = $this->booleanTreatment($nonTreatedPostValue, $key);
                    } catch (\Exception $e) {
                        $treatedPostValue = TRUE;
                        $this->logger->error($e->getMessage());
                    }
                    break;
            }

            $postArray[$key] = $treatedPostValue;
        }

        return $postArray;
    }

    private function arrayTreatment($nonTreatedPostValue, $key)
    {
        if (!is_array($nonTreatedPostValue)) {
            throw new \Exception(__FILE__ . ": custom error -> $key not an array");
        }

        return $nonTreatedPostValue;
    }

    private function booleanTreatment($nonTreatedPostValue, $key)
    {
        if (!is_string($nonTreatedPostValue)) {
            throw new \Exception(__FILE__ . ": custom error -> $key not a string");
        }

        if (strtolower($nonTreatedPostValue) === "no") {
            $treatedPostValue = FALSE;
        } else {
            $treatedPostValue = TRUE;
        }

        return $treatedPostValue;
    }
}