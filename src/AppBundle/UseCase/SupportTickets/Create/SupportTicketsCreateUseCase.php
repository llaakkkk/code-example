<?php

namespace AppBundle\UseCase\SupportTickets\Create;


use AppBundle\Entity\User;
use AppBundle\Service\FreshDesk\FreshDeskService;
use AppBundle\Service\UseCase\GenericErrorsTrait;
use Particle\Validator\Validator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SupportTicketsCreateUseCase
{
    use GenericErrorsTrait;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var FreshDeskService
     */
    private $freshDesk;


    /**
     * RatingsGetUseCase constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param FreshDeskService      $freshDesk
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        FreshDeskService $freshDesk
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->freshDesk    = $freshDesk;
    }

    /**
     * @param SupportTicketsCreateRequest $request
     *
     * @return SupportTicketsCreateResponse
     */
    public function postSupport(SupportTicketsCreateRequest $request): SupportTicketsCreateResponse
    {
        $response = new SupportTicketsCreateResponse();

        $this->validateRequest($request, $response);

        if ($response->isError()) {
            return $response;
        }

        /** @var User|null $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $user = $user instanceof User ? $user : null;


        $created = $this->freshDesk->sendSupportRequest(
            $request->email,
            $request->subject,
            $request->description,
            $user
        );
        if (!$created) {
            $this->setServerError($response);

            return $response;
        }

        return $response;
    }

    /**
     * @param SupportTicketsCreateRequest  $request
     * @param SupportTicketsCreateResponse $response
     */
    private function validateRequest(SupportTicketsCreateRequest $request, SupportTicketsCreateResponse $response)
    {

        $v = new Validator();
        $v->required('email')
          ->email()
          ->lengthBetween(3, 100);

        $v->required('subject')
          ->lengthBetween(3, 256);

        $v->required('description')
          ->lengthBetween(3, 5000);

        $result = $v->validate(get_object_vars($request));

        if (!$result->isValid()) {
            $this->setValidationError($response, $result->getMessages());

            return;
        }
    }
}
