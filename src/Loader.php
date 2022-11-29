<?php

namespace CrisFelixWeddingCustomModule;

class Loader
{
    protected $checker;
    protected $entityGenerator;
    protected $guestUploader;
    protected $obtainer;
    protected $sendMailDatabaseGestor;
    protected $currentHashForm;

    /**
     * Loader constructor.
     * @param $checker
     * @param $entityGenerator
     * @param $guestUploader
     * @param $obtainer
     */
    public function __construct($checker, $entityGenerator, $guestUploader, $obtainer, $sendMailDatabaseGestor, $currentHashForm)
    {
        $this->checker = $checker;
        $this->entityGenerator = $entityGenerator;
        $this->guestUploader = $guestUploader;
        $this->obtainer = $obtainer;
        $this->sendMailDatabaseGestor = $sendMailDatabaseGestor;
        $this->currentHashForm = $currentHashForm;
    }

    public function loadCustomGuestType()
    {
        try {
            if ($this->checker->validatedPost() && !empty($arrayFromPost = $this->obtainer->obtainArrayFromPostPetition())) {
                $guestEntity = $this->entityGenerator->generateGuestEntity($arrayFromPost);
                $this->guestUploader->uploadGuest($guestEntity);
            } else {
                $this->sendMailDatabaseGestor->addRegister($this->currentHashForm);
            }
        } catch (\Exception $e) {
            $this->sendMailDatabaseGestor->addRegister($this->currentHashForm);
            throw new \Exception($e->getMessage());
        }
    }
}




