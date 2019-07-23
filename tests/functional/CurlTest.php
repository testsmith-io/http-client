<?php

namespace Test\Functional;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\HttpClient\Curl;
use SocialConnect\HttpClient\Request;
use SocialConnect\HttpClient\StreamFactory;

class CurlTest extends \PHPUnit\Framework\TestCase
{
    protected function executeRequest(RequestInterface $request)
    {
        $client = new Curl();
        return $client->sendRequest($request);
    }

    protected function assertOk(ResponseInterface $response)
    {
        parent::assertSame(200, $response->getStatusCode());
        parent::assertSame('OK', $response->getReasonPhrase());
        parent::assertSame('application/json', $response->getHeaderLine('content-type'));
    }

    public function testGetMethod()
    {
        $response = $this->executeRequest(new Request('GET', 'http://127.0.0.1:5555/test-get'));

        $this->assertOk($response);

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

        $response = $this->executeRequest($request);

        $this->assertOk($response);
        parent::assertSame($response->getHeaderLine('content-type'), 'application/json');

        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);

        parent::assertEquals($result['method'], 'POST');
        parent::assertEquals($result['uri'], 'http://127.0.0.1:5555/test-get');
        parent::assertEquals($result['headers']['x-my-header'], ['5']);
    }

    public function testPutMethod()
    {
        $streamFactory = new StreamFactory();

        $request = new Request('PUT', 'http://127.0.0.1:5555/test-put');
        $request = $request->withHeader('X-MY-Header', '5');
        $request = $request->withBody(
            $streamFactory->createStream('payload')
        );

        $response = $this->executeRequest($request);

        $this->assertOk($response);

        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);

        parent::assertEquals($result['method'], 'PUT');
        parent::assertEquals($result['uri'], 'http://127.0.0.1:5555/test-put');
        parent::assertEquals($result['headers']['x-my-header'], ['5']);
    }

    public function testDeleteMethod()
    {
        $response = $this->executeRequest(new Request('DELETE', 'http://127.0.0.1:5555/test-delete'));

        $this->assertOk($response);

        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);

        parent::assertEquals($result['method'], 'DELETE');
        parent::assertEquals($result['uri'], 'http://127.0.0.1:5555/test-delete');
    }
}