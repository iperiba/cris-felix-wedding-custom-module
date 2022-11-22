<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

use WP_Query;

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

    const COMPULSORY_FIELDS = array("guest_name", "surname", "nid", "email", "phone", "days", "upper_age", "menu_type");
    const POST_TYPE = 'guest';

    public static function obtainArrayFromPostPetition()
    {
        $obtainer = new self();
        $postArray = array();

        try {
             if ($validatedPost = $obtainer->validatedPost()) {
                 $postArray = $obtainer->getPostArray();
             }

             return $postArray;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function getPostArray()
    {
        $postArray = array();

        foreach (self::POST_FIELDS_ENTITY_FIELDS as $key => $value) {
            if (is_null($nonTreatedPostValue = $_POST[$key])) {
                error_log(__FILE__ . ": custom error -> $key is missing from the compulsory array");
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

    private function validatedPost(): bool
    {
        $validatedPost = TRUE;

        if (empty($_POST)) {
            throw new \Exception(__FILE__ . ": custom error -> no array post parameter found");
        }

        foreach (self::COMPULSORY_FIELDS as $compulsoryField) {
            if (is_null($_POST[$compulsoryField])) {
                throw new \Exception(__FILE__ . ": custom error -> $compulsoryField is missing from the compulsory array");
            }
        }

        try {
            $alreadyNidPost = $this->alreadyNidPost();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        if ($alreadyNidPost) {
            $validatedPost = FALSE;
        }

        return $validatedPost;
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

    private function alreadyNidPost()
    {
        $alreadyNidPost = FALSE;

        if (empty($nid = $_POST["nid"])) {
            throw new \Exception(__FILE__ . ": custom error -> nid post value is empty");
        }

        $args = array(
            'post_type' => self::POST_TYPE,
            'meta_key' => "nid",
            'meta_value' => $nid,
            'meta_compare' => '=',
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $alreadyNidPost = TRUE;
            error_log(__FILE__ . ": custom notice -> There is already a guest post with id $nid", 0);
        }

        return $alreadyNidPost;
    }
}