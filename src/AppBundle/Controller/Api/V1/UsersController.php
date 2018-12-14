<?php

namespace AppBundle\Controller\Api\V1;

use AppBundle\UseCase\Users\VerifyAge\UsersVerifyAgeRequest;
use AppBundle\UseCase\Users\VerifyAge\UsersVerifyAgeUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends ApiController
{

    /**
     * @Route("/users/age-verification", methods={"POST"})
     * @param Request               $httpRequest
     * @param UsersVerifyAgeUseCase $useCase
     *
     * @return JsonResponse
     */
    public function ageVerificationAction(
        Request $httpRequest,
        UsersVerifyAgeUseCase $useCase
    ) {
        $request = new UsersVerifyAgeRequest();

        $body = $this->getJsonBody($httpRequest);
        if ($body === false) {
            return $this->getNotJsonRequestErrorResponse();
        }
        $request->provider = $body['provider'] ?? null;
        $request->token    = $body['token'] ?? null;

        $response = $useCase->verifyAge($request);

        if ($response->isError()) {
            return $this->getErrorHttpResponse($response->getError());
        }

        return new JsonResponse(['verified' => $response->ageVerified]);
    }
}