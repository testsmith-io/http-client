<?php

namespace Test\Functional;

use SocialConnect\HttpClient\Curl;
use SocialConnect\HttpClient\Request;
use SocialConnect\HttpClient\StreamFactory;

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

    public function testPostMethod()
    {
        $streamFactory = new StreamFactory();

        $request = new Request('POST', 'http://127.0.0.1:5555/test-get');
        $request = $request->withHeader('X-MY-Header', '5');
        $request = $request->withBody(
            $streamFactory->createStream('payload')
        );

        $client = new Curl();
        $response = $client->sendRequest($request);

        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);

        parent::assertEquals($result['method'], 'POST');
        parent::assertEquals($result['uri'], 'http://127.0.0.1:5555/test-get');
        parent::assertEquals($result['headers']['x-my-header'], ['5']);
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