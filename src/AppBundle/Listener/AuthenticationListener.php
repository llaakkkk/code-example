<?php

namespace AppBundle\Listener;


use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationListener
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * LoginListener constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * @param AuthenticationEvent $event
     */
    public function onAuthenticationSuccess(AuthenticationEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $user->updateLastLogin();
            $this->userRepository->store($user, true);
        }
    }
}