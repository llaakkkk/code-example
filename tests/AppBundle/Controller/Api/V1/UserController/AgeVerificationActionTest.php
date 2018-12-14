<?php

namespace Tests\AppBundle\Controller\Api\V1\UsersController;

use Tests\AppBundle\ApiControllerTestCase;
use Yoti\ActivityDetails;
use Yoti\Entity\Attribute;
use Yoti\Entity\Profile;
use Yoti\YotiClient;

/**
 * @group controller.api.v1
 * @group controller.api.v1.users.ageVerification
 */
class AgeVerificationActionTest extends ApiControllerTestCase
{
    public function testYotiProvderReturnsSuccess()
    {
        $user        = $this->entityFactory->getUser();
        $accessToken = $this->entityFactory->getAccessToken(['user' => $user]);

        $this->em->persist($accessToken);
        $this->em->persist($user);
        $this->em->flush();

        $this->mockYotiClient();

        $this->requestApi(
            'POST',
            '/v1/users/age-verification',
            [
                'provider' => 'yoti',
                'token'    => '12345678'

            ],
            $accessToken
        );
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonValue(true, $response->getContent(), 'verified');
    }

    /**
     * @group controller.api.v1.users.ageVerification.fails
     */
    public function testWrongProviderNameFails()
    {
        $user        = $this->entityFactory->getUser();
        $accessToken = $this->entityFactory->getAccessToken(['user' => $user]);

        $this->em->persist($accessToken);
        $this->em->persist($user);
        $this->em->flush();

        $this->requestApi(
            'POST',
            '/v1/users/age-verification',
            [
                'provider' => 'test',
                'token'    => '12345678'

            ],
            $accessToken
        );
        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonValue('validation_failed', $response->getContent(), 'code');
        $this->assertJsonKey($response->getContent(), 'data/provider/InArray::NOT_IN_ARRAY');

    }

    private function mockYotiClient()
    {
        // mock Attribute getValue method
        $attributeMock = \Mockery::mock(Attribute::class);
        $attributeMock
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(true);

        // mock Profile getAgeCondition method
        $profileMock = \Mockery::mock(Profile::class);
        $profileMock
            ->shouldReceive('getAgeCondition')
            ->twice()
            ->andReturn($attributeMock);

        // mock ActivityDetails getOutcome method
        $activityDetailsMock = \Mockery::mock(ActivityDetails::class);
        $activityDetailsMock
            ->shouldReceive('getProfile')
            ->once()
            ->andReturn($profileMock);

        $yotiClientMock = \Mockery::mock(YotiClient::class);

        // mock YotiCLient getOutcome method
        $yotiClientMock
            ->shouldReceive('getOutcome')
            ->once()
            ->andReturn('SUCCESS');

        // mock getActivityDetails method
        $yotiClientMock
            ->shouldReceive('getActivityDetails')
            ->once()
            ->withArgs(
                function ($token) {
                    return $token == '12345678';
                }
            )
            ->andReturn($activityDetailsMock);

        $this->getContainer()->set('yotiClient', $yotiClientMock);
    }
}