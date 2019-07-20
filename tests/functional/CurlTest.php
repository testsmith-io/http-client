<?php

namespace Test\Functional;

use SocialConnect\HttpClient\Curl;
use SocialConnect\HttpClient\Request;

class CurlTest extends \PHPUnit\Framework\TestCase
{
    public function testGetMethod()
    {
        $client = new Curl();
        $response = $client->sendRequest(
            new Request('GET', 'http://127.0.0.1:5555/test-get')
        );

        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);

        parent::assertEquals($result['method'], 'GET');
        parent::assertEquals($result['uri'], 'http://127.0.0.1:5555/test-get');
    }

    public function testDeleteMethod()
    {
        $client = new Curl();
        $response = $client->sendRequest(
            new Request('DELETE', 'http://127.0.0.1:5555/test-delete')
        );

        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);

        parent::assertEquals($result['method'], 'DELETE');
        parent::assertEquals($result['uri'], 'http://127.0.0.1:5555/test-delete');
    }
}