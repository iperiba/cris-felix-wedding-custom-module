<?php

namespace CrisFelixWeddingCustomModule;

use CrisFelixWeddingCustomModule\Actions\Implementations\Obtainer;

class Loader
{
    private $obtainer;

    /**
     * Loader constructor.
     * @param Obtainer $obtainer
     */
    public function __construct(Obtainer $obtainer)
    {
        $this->obtainer = $obtainer;
    }

    public function loadCustomGuestType() {
		$arrayFromPost = $this->obtainer->obtainArrayFromPostPetition();
	}
}




