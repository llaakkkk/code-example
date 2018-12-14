<?php

namespace AppBundle\Controller\Api\V1;

use AppBundle\UseCase\SupportTickets\Create\SupportTicketsCreateRequest;
use AppBundle\UseCase\SupportTickets\Create\SupportTicketsCreateUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SupportTicketsController extends ApiController
{
    /**
     * @Route("/support-tickets", methods={"POST"})
     * @param Request                     $httpRequest
     * @param SupportTicketsCreateUseCase $useCase
     *
     * @return JsonResponse
     */
    public function createAction(Request $httpRequest, SupportTicketsCreateUseCase $useCase)
    {
        $body = $this->getJsonBody($httpRequest);
        if ($body === false) {
            return $this->getNotJsonRequestErrorResponse();
        }

        $request              = new SupportTicketsCreateRequest();
        $request->email       = $body['email'] ?? null;
        $request->subject     = $body['subject'] ?? null;
        $request->description = $body['description'] ?? null;

        $response = $useCase->postSupport($request);

        if ($response->isError()) {
            return $this->getErrorHttpResponse($response->getError());
        }

        return new JsonResponse(['created' => true]);
    }

}