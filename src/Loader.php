<?php

namespace CrisFelixWeddingCustomModule;

use CrisFelixWeddingCustomModule\Actions\Implementations\Obtainer;
use CrisFelixWeddingCustomModule\Actions\Implementations\EntityGenerator;
use CrisFelixWeddingCustomModule\Actions\Implementations\GuestUploader;

class Loader
{
    public static function loadCustomGuestType() {
        try {
            if (!empty($arrayFromPost = Obtainer::obtainArrayFromPostPetition())) {
                $guestEntity = EntityGenerator::generateGuestEntity($arrayFromPost);
                GuestUploader::uploadGuest($guestEntity);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}




