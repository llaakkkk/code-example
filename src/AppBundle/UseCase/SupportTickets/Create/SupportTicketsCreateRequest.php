<?php

namespace AppBundle\UseCase\SupportTickets\Create;

use AppBundle\Service\UseCase\UseCaseRequest;

class SupportTicketsCreateRequest extends UseCaseRequest
{
    /**
     * @var int|string
     */
    public $email;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $description;


}
