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
        "menu_type" => "string",
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

    const COMPULSORY_FIELDS = array("gues_name", "surname", "nid", "email", "phone", "days", "upper_age", "menu_type");

    public function obtainArrayFromPostPetition()
    {
        try {
            $this->postValidation();
            return $this->getPostArray();
        } catch (\Exception $e) {
            error_log($e->getMessage(), 0);
            exit;
        }
    }

    private function getPostArray()
    {
        $postArray = array();

        foreach (self::POST_FIELDS_ENTITY_FIELDS as $key => $value) {
            if (is_null($nonTreatedPostValue = $_POST[$key])) {
                error_log(__FILE__ . ":$key is missing from the compulsory array");
                break;
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
                        error_log($e->getMessage(), 0);
                    }
                    break;
                case "boolean":
                    try {
                        $treatedPostValue = $this->booleanTreatment($nonTreatedPostValue, $key);
                    } catch (\Exception $e) {
                        $treatedPostValue = TRUE;
                        error_log($e->getMessage(), 0);
                    }
                    break;
            }

            $postArray[$key] = $treatedPostValue;
        }

        return $postArray;
    }

    private function postValidation()
    {
        if (empty($_POST)) {
            throw new \Exception(__FILE__ . ": no array post parameter found");
        }

        foreach (self::COMPULSORY_FIELDS as $compulsoryField) {
            if (is_null($_POST[$compulsoryField])) {
                throw new \Exception(__FILE__ . ":$compulsoryField is missing from the compulsory array");
            }
        }
    }

    private function arrayTreatment($nonTreatedPostValue, $key)
    {
        if (!is_array($nonTreatedPostValue)) {
            throw new \Exception(__FILE__ . ": $key not an array");
        }

        return $nonTreatedPostValue;
    }

    private function booleanTreatment($nonTreatedPostValue, $key)
    {
        if (!is_string($nonTreatedPostValue)) {
            throw new \Exception(__FILE__ . ": $key not an array");
        }

        if (strtolower($nonTreatedPostValue) === "no") {
            $treatedPostValue = FALSE;
        } else {
            $treatedPostValue = TRUE;
        }

        return $treatedPostValue;
    }
}