<?php

namespace Tests\AppBundle;

use AppBundle\Entity\AccessToken;
use Symfony\Bundle\FrameworkBundle\Client;
use \Symfony\Component\DomCrawler\Crawler;

abstract class ApiControllerTestCase extends AppTestCase
{
    /**
     * @var Client
     */
    protected $client = null;

    public function setUp()
    {
        parent::setUp();

        $this->client = static::$kernel->getContainer()->get('test.client');
        $this->client->setServerParameter('HTTP_HOST', 'host');
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $data
     *
     * @return Crawler
     */
    public function requestApi(string $method, string $path, array $data = null, AccessToken $accessToken = null): Crawler
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($accessToken) {
            $headers['HTTP_X_ACCESS_TOKEN'] = $accessToken->getId();
        }

        return $this->client->request(
            $method,
            $path,
            [],
            [],
            $headers,
            \GuzzleHttp\json_encode(
                $data
            )
        );
    }
}
