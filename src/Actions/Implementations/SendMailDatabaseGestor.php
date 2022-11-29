<?php

namespace CrisFelixWeddingCustomModule\Actions\Implementations;

class SendMailDatabaseGestor
{
    const TABLE_NAME = 'send_email';
    const FORM_INSTANCE_HASH_COLUMN = 'form_instance_hash';
    const SEND_EMAIL_COLUMN = 'send_email';
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function addRegister($currentHashForm)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $result = $wpdb->insert($table_name, array(
            self::FORM_INSTANCE_HASH_COLUMN => $currentHashForm,
            self::SEND_EMAIL_COLUMN => FALSE
        ));

        if (!$result) {
            throw new \Exception(__FILE__ . ": custom error -> $currentHashForm could not be inserted in $table_name");
        }
    }

    public function obtainSendEmailRegister($currentHashForm) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $query = "SELECT " . self::SEND_EMAIL_COLUMN . " FROM $table_name WHERE form_instance_hash = \"$currentHashForm\" LIMIT 1";
        $resultsArray = $wpdb->get_results( $query, ARRAY_A);
        if (!is_null($resultsArray) && is_array($resultsArray) && !empty($resultsArray)) {
            $countResults = count($resultsArray);
            $selectedResult = $resultsArray[$countResults-1];
            $sendEmailResult = $selectedResult[self::SEND_EMAIL_COLUMN];
        } else {
            $sendEmailResult = TRUE;
        }

        return $sendEmailResult;
    }
}




