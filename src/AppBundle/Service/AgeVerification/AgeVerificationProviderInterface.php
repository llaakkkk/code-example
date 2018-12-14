<?php

namespace AppBundle\Service\AgeVerification;


class AgeVerificationProviderInterface
{

    /**
     * @param string $token
     *
     * @return bool|null
     */
    public function verifyAge(string $token): ?bool {}

}