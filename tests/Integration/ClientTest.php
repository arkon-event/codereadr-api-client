<?php

namespace ArkonEvent\CodeReadr\Tests\Integration;

use ArkonEvent\CodeReadr\ApiClient\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    /**
     *
     * @var string
     */
    protected $apiKey;

    /**
     *
     * @var Client
     */
    protected $client;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $config = parse_ini_file(__DIR__ . '/config.ini');
        $this->apiKey = $config['apiKey'];
    }

    protected function setUp()
    {
        $this->client = new Client($this->apiKey);
    }

    public function testGetUsers()
    {
        $xml = $this->client->request(Client::SECTION_USERS, Client::ACTION_RETREIVE);

        $this->assertEquals(1, (int)$xml->status);
    }

    public function testInvalidSection()
    {
        $this->expectException('\ArkonEvent\CodeReadr\Exceptions\CodeReadrApiException');
        $this->client->request('fsdfds', Client::ACTION_RETREIVE);
    }
}