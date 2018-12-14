<?php

namespace AppBundle\UseCase\Users\VerifyAge;

use AppBundle\Service\UseCase\UseCaseResponse;

class UsersVerifyAgeResponse extends UseCaseResponse
{
    /**
     * @var bool|null
     */
    public $ageVerified;

}