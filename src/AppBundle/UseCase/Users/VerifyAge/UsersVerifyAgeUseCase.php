<?php

namespace AppBundle\UseCase\Users\VerifyAge;


use AppBundle\Service\AgeVerification\AgeVerificationProviderFactory;
use AppBundle\Service\UseCase\GenericErrorsTrait;
use AppBundle\Service\AgeVerification\AgeVerificationProviderException;
use AppBundle\Service\AgeVerification\YotiProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Particle\Validator\Validator;

class UsersVerifyAgeUseCase
{
    use GenericErrorsTrait;

    /**
     * @var AgeVerificationProviderFactory
     */
    private $ageVerificationProviderFactory;

    /**
     * UsersVerifyAgeUseCase constructor.
     *
     * @param AgeVerificationProviderFactory $ageVerificationProviderFactory
     */
    public function __construct(
        AgeVerificationProviderFactory $ageVerificationProviderFactory
    ) {
        $this->ageVerificationProviderFactory = $ageVerificationProviderFactory;
    }

    public function verifyAge(UsersVerifyAgeRequest $request): UsersVerifyAgeResponse
    {
        $response = new UsersVerifyAgeResponse();

        $this->validateRequest($request, $response);

        if ($response->isError()) {
            return $response;
        }
        $ageVerificationProvider = $this->ageVerificationProviderFactory->factory($request->provider);

        try {
            $response->ageVerified = $ageVerificationProvider->verifyAge($request->token);
        } catch (AgeVerificationProviderException $exception) {
            $this->setServerError($response);
        }

        return $response;
    }

    /**
     * @param UsersVerifyAgeRequest  $request
     * @param UsersVerifyAgeResponse $response
     */
    private function validateRequest(
        UsersVerifyAgeRequest $request,
        UsersVerifyAgeResponse $response
    ): void {

        $v = new Validator();
        $v->required('provider')
          ->string()
          ->inArray([YotiProvider::NAME]);
        $v->required('token')
          ->string();

        $result = $v->validate((array)$request);
        if (!$result->isValid()) {
            $this->setValidationError($response, $result->getMessages());

            return;
        }
    }
}