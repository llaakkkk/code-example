<?php

namespace AppBundle\Service\AgeVerification;

use Psr\Log\LoggerInterface;
use Yoti\YotiClient;

class YotiProvider extends AgeVerificationProviderInterface
{
    public const NAME = 'yoti';

    /**
     * @var YotiClient
     */
    private $yotiClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * YotiProvider constructor.
     *
     * @param YotiClient      $yotiClient
     * @param LoggerInterface $logger
     */
    public function __construct(YotiClient $yotiClient, LoggerInterface $logger)
    {
        $this->yotiClient = $yotiClient;
        $this->logger     = $logger;
    }

    /**
     * @param string $token
     *
     * @return bool|null
     */
    public function verifyAge(string $token): ?bool
    {
        try {
            $activityDetails = $this->yotiClient->getActivityDetails($token);

            // if success
            if ($this->yotiClient->getOutcome() == \Yoti\YotiClient::OUTCOME_SUCCESS) {
                $profile = $activityDetails->getProfile();

                // check is age verified
                $verifiedAge = $profile->getAgeCondition() ? $profile->getAgeCondition()->getValue() : null;

                return $verifiedAge;
            }

            $this->throwAgeVerificationProviderException('Yoti returned empty data', 'Failed to check is age verified');
        } catch (\Exception $e) {
            $this->throwAgeVerificationProviderException($e->getMessage(), 'Failed to check is age verified');
        }

        return null;

    }

    /**
     * @param string $logMessage
     * @param string $exceptionMessage
     */
    private function throwAgeVerificationProviderException(string $logMessage, string $exceptionMessage)
    {
        $this->logger->error($logMessage);
        throw new AgeVerificationProviderException($exceptionMessage);
    }
}
