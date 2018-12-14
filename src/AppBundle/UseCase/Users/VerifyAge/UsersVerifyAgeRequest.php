<?php

namespace AppBundle\UseCase\Users\VerifyAge;

use AppBundle\Service\UseCase\UseCaseRequest;

class UsersVerifyAgeRequest extends UseCaseRequest
{
    /**
     * @var string
     */
    public $provider;

    /**
     * @var string
     */
    public $token;

}