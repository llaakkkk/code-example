<?php

namespace AppBundle\Service\FreshDesk;

use AppBundle\Entity\ContentPermission;
use AppBundle\Entity\Repository\ContentPermissionRepository;
use AppBundle\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class FreshDeskService
{
    const STATUS_OPEN_TICKET = 2;

    const PRIORITY_MEDIUM = 2;

    const USER_DATA_REQUEST_SUBJECT = 'User data request';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * @var ContentPermissionRepository
     */
    private $contentPermissionRepo;

    /**
     * FeedbackService constructor.
     *
     * @param LoggerInterface             $logger
     * @param ClientInterface             $guzzle
     * @param ContentPermissionRepository $contentPermissionRepo
     */
    public function __construct(
        LoggerInterface $logger,
        ClientInterface $guzzle,
        ContentPermissionRepository $contentPermissionRepo
    ) {
        $this->logger                = $logger;
        $this->guzzle                = $guzzle;
        $this->contentPermissionRepo = $contentPermissionRepo;
    }

    /**
     * @param string    $fromEmail
     * @param string    $subject
     * @param string    $description
     * @param User|null $user
     *
     * @return bool
     */
    public function sendSupportRequest(
        string $fromEmail,
        string $subject,
        string $description,
        ?User $user
    ): bool {
        $tags         = [];
        $customFields = [];

        if ($user) {
            $description .= "<br><br>--- metadata added automatically below (confirm ticket tags) ---<br>";
            $description .= "Logged in user ID: {$user->getId()}<br>";
            $description .= "Logged in user email: {$user->getEmailCanonical()}<br>";

            $contentPermissions = $this->contentPermissionRepo->findBy(['user' => $user->getId()]);

            if (count($contentPermissions) > 0) {
                $description .= "<table><thead><tr><th>Content Id</th><th>Content Type</th><th>Permission</th><th>Granted At (UTC)</th><th>Expires At (UTC)</th><th>Granted Via</th><th>Granted Via Type</th></tr></thead><tbody>";

                foreach ($contentPermissions as $contentPermission) {
                    /** @var ContentPermission $contentPermission */
                    $expiresAt = $contentPermission->getExpiresAt() ? $contentPermission->getExpiresAt()->format(
                        'Y-m-d H:i:s'
                    ) : '-';
                    $grantedAt = $contentPermission->getGrantedAt() ? $contentPermission->getGrantedAt()->format(
                        'Y-m-d H:i:s'
                    ) : '-';

                    $description .= "<tr><td>{$contentPermission->getContentId()}</td><td>{$contentPermission->getContentType()}</td><td>{$contentPermission->getPermission()}</td><td>{$grantedAt}</td><td>{$expiresAt}</td><td>{$contentPermission->getGrantedVia()}</td><td>{$contentPermission->getGrantedViaType()}</td></tr>";
                }

                $description .= "</tbody></table>";
            }
            $tags[] = 'logged-in-user';

            // TODO Uncomment when plan is upgraded
//            $customFields = [
//                'cf_real_user_id'    => $userId,
//                'cf_real_user_email' => $email,
//            ];
        }

        $body = [
            'email'       => $fromEmail,
            'subject'     => $subject,
            'description' => $description,
            'priority'    => self::PRIORITY_MEDIUM,
            'status'      => self::STATUS_OPEN_TICKET,
            'tags'        => $tags,
        ];

        if (!empty($customFields)) {
            $body['custom_fields'] = $customFields;
        }

        return $this->makeFreshDeskRequest($body);
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function sendUsersDataRequest(
        User $user
    ): bool {
        $customFields  = [];
        $userCreatedAt = $user->getCreatedAt() ? $user->getCreatedAt()->format('c') : null;

        $description = "User ID: {$user->getId()}<br>";
        $description .= "User email: {$user->getEmailCanonical()}<br>";
        $description .= "User profile creation date: {$userCreatedAt}<br>";

        $tags = ['logged-in-user', 'user-data-request'];

        // TODO Uncomment when plan is upgraded
//            $customFields = [
//                'cf_real_user_id'    => $userId,
//                'cf_real_user_email' => $email,
//            ];

        $body = [
            'email'       => $user->getEmailCanonical(),
            'subject'     => self::USER_DATA_REQUEST_SUBJECT,
            'description' => $description,
            'priority'    => self::PRIORITY_MEDIUM,
            'status'      => self::STATUS_OPEN_TICKET,
            'tags'        => $tags,
        ];

        if (!empty($customFields)) {
            $body['custom_fields'] = $customFields;
        }

        return $this->makeFreshDeskRequest($body);

    }


    /**
     * @param array $body
     *
     * @return bool
     */
    private function makeFreshDeskRequest(array $body): bool
    {
        $response = $this->guzzle->request(
            'POST',
            '/api/v2/tickets',
            [
                'json' => $body
            ]
        );
        $body     = (string)$response->getBody();

        switch ($response->getStatusCode()) {
            case 201:
                return true;

            default:
                $this->logger->error(
                    "API request to Freshdesk faiiled",
                    ['responseCode' => $response->getStatusCode(), 'responseBody' => $body]
                );

                return false;
        }
    }
}
