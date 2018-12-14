<?php

namespace AppBundle\Service\AgeVerification;


class AgeVerificationProviderFactory
{

    /**
     * @var YotiProvider
     */
    public $yotiProvider;

    public function __construct(YotiProvider $yotiProvider)
    {
        $this->yotiProvider = $yotiProvider;
    }

    public function factory($provider)
    {
        switch ($provider) {
            case YotiProvider::NAME:
                return $this->yotiProvider;
            default:
                throw new \InvalidArgumentException("Unknown provider name: {$provider}");
        }
    }
}