<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

use WP_Query;

class Checker
{
    const COMPULSORY_FIELDS = array("guest_name", "surname", "nid", "email", "phone", "days", "upper_age", "menu_type");
    const POST_TYPE = 'guest';

    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function validatedPost(): bool
    {
        if (empty($_POST)) {
            $this->logger->error(__FILE__ . ": custom error -> no array post parameter found");
            return FALSE;
        }

        foreach (self::COMPULSORY_FIELDS as $compulsoryField) {
            if (is_null($_POST[$compulsoryField])) {
                $this->logger->error(__FILE__ . ": custom error -> $compulsoryField is missing from the compulsory array");
                return FALSE;
            }
        }

        try {
            $alreadyNidPost = $this->alreadyNidPost();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        if ($alreadyNidPost) {
            return FALSE;
        }

        return TRUE;
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
            $this->logger->info(__FILE__ . ": custom notice -> There is already a guest post with id $nid");
        }

        return $alreadyNidPost;
    }
}




