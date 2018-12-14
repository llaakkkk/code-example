<?php

namespace Tests\AppBundle\Controller\Api\V1\SupportTicketsController;

use AppBundle\Service\FreshDesk\FreshDeskService;
use EaPaysites\Sqs\Client\SqsClientInterface;
use SendGrid\Email;
use Tests\AppBundle\ApiControllerTestCase;

/**
 * @group controller.api.v1
 * @group controller.api.v1.supportTickets.create
 */
class CreateActionTest extends ApiControllerTestCase
{
    public function testSupportTicketSentSuccess()
    {
        $user = $this->entityFactory->getUser();
        $this->em->persist($user);

        $accessToken = $this->entityFactory->getAccessToken(['user' => $user]);
        $this->em->persist($accessToken);

        $this->em->flush();

        $this->mockFreshDeskService();

        $this->requestApi(
            'POST',
            '/v1/support-tickets',
            [
                'email'       => 'some@email.com',
                'subject'     => 'some subj',
                'description' => 'some descr'
            ],
            $accessToken
        );

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonValue(['created' => true], $response->getContent());
    }


    public function testSupportTicketNotValidEmail()
    {

        $this->requestApi(
            'POST',
            '/v1/support-tickets',
            [
                'email'       => 'some',
                'subject'     => 'some subject',
                'description' => 'some description'
            ]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonKey($response->getContent(), 'data/email/Email::INVALID_VALUE');
    }

    public function testSupportTicketTooShortValues()
    {

        $this->requestApi(
            'POST',
            '/v1/support-tickets',
            [
                'email'       => '1',
                'subject'     => '1',
                'description' => '1'
            ]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonKey($response->getContent(), 'data/email/LengthBetween::TOO_SHORT');
        $this->assertJsonKey($response->getContent(), 'data/subject/LengthBetween::TOO_SHORT');
        $this->assertJsonKey($response->getContent(), 'data/description/LengthBetween::TOO_SHORT');
    }

    public function testSupportTicketTooLongValues()
    {

        $this->requestApi(
            'POST',
            '/v1/support-tickets',
            [
                'email'       => str_repeat('email', 100),
                'subject'     => str_repeat('subject', 100),
                'description' => str_repeat('description', 5000)
            ]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonKey($response->getContent(), 'data/email/LengthBetween::TOO_LONG');
        $this->assertJsonKey($response->getContent(), 'data/subject/LengthBetween::TOO_LONG');
        $this->assertJsonKey($response->getContent(), 'data/description/LengthBetween::TOO_LONG');
    }

    public function testSupportTicketEmptyValues()
    {

        $this->requestApi(
            'POST',
            '/v1/support-tickets',
            [
                'email'       => '',
                'subject'     => '',
                'description' => ''
            ]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonKey($response->getContent(), 'data/email/NotEmpty::EMPTY_VALUE');
        $this->assertJsonKey($response->getContent(), 'data/subject/NotEmpty::EMPTY_VALUE');
        $this->assertJsonKey($response->getContent(), 'data/description/NotEmpty::EMPTY_VALUE');
    }

    public function testErrorResponseWhenFreshdeskFails()
    {
        $freshDeskServiceMock = \Mockery::mock(FreshDeskService::class);
        $freshDeskServiceMock
            ->shouldReceive('sendSupportRequest')
            ->once()
            ->withArgs(
                function ($fromEmail, $subject, $description) {
                    return $fromEmail == 'some@email.com' &&
                        $subject == 'some subj' &&
                        $description == 'some descr';
                }
            )
            ->andReturn(false);

        $this->getContainer()->set('test.' . FreshDeskService::class, $freshDeskServiceMock);

        $this->requestApi(
            'POST',
            '/v1/support-tickets',
            [
                'email'       => 'some@email.com',
                'subject'     => 'some subj',
                'description' => 'some descr'
            ]
        );

        $response = $this->client->getResponse();
        $content  = json_decode($response->getContent(), true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Server Error', $content['message']);

    }
    /**
     * @group controller.api.v1.supportTickets.supportFormCreatesEmailLoggedUser
     */
    public function testSupportFormCreatesEmailLoggedUser()
    {
        $user = $this->entityFactory->getUser();
        $this->em->persist($user);

        $accessToken = $this->entityFactory->getAccessToken(['user' => $user]);
        $this->em->persist($accessToken);

        $this->em->flush();

        $freshDeskServiceMock = \Mockery::mock(FreshDeskService::class);
        $freshDeskServiceMock
            ->shouldReceive('sendSupportRequest')
            ->once()
            ->withArgs(
                function ($fromEmail, $subject, $description, $userData) use ($user) {
                    return $fromEmail == 'some@email.com' &&
                        $subject == 'some subj' &&
                        $description == 'some descr' &&
                        $userData->getId() == $user->getId() &&
                        $userData->getEmailCanonical() == $user->getEmailCanonical();
                }
            )
            ->andReturn(true);

        $this->getContainer()->set('test.' . FreshDeskService::class, $freshDeskServiceMock);

        $this->requestApi(
            'POST',
            '/v1/support-tickets',
            [
                'email'       => 'some@email.com',
                'subject'     => 'some subj',
                'description' => 'some descr'
            ],
            $accessToken
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonValue(['created' => true], $response->getContent());
    }

    protected function mockFreshDeskService()
    {

        $freshDeskServiceMock = \Mockery::mock(FreshDeskService::class);
        $freshDeskServiceMock
            ->shouldReceive('sendSupportRequest')
            ->once()
            ->withArgs(
                function ($fromEmail, $subject, $description) {
                    return $fromEmail == 'some@email.com' &&
                        $subject == 'some subj' &&
                        $description == 'some descr';
                }
            )
            ->andReturn(true);

        $this->getContainer()->set('test.' . FreshDeskService::class, $freshDeskServiceMock);
    }
}