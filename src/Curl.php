<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\HttpClient;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\HttpClient\Exception\ClientException;
use SocialConnect\HttpClient\Exception\NetworkException;
use SocialConnect\HttpClient\Exception\RequestException;

class Curl implements ClientInterface
{
    /**
     * Curl resource
     *
     * @var resource
     */
    protected $curlHandler;

    /**
     * Curl options
     *
     * @var array
     */
    protected $parameters = array(
        CURLOPT_USERAGENT => 'SocialConnect\HttpClient (https://github.com/socialconnect/http-client) v1',
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT_MS => 10 * 1000,
        CURLOPT_TIMEOUT_MS => 30 * 1000,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2
    );

    /**
     * @param array|null $parameters
     */
    public function __construct(array $parameters = null)
    {
        if (!extension_loaded('curl')) {
            throw new ClientException('You need to install curl-ext to use SocialConnect\HttpClient\Curl.');
        }

        if ($parameters) {
            $this->parameters = array_replace($this->parameters, $parameters);
        }

        $curlHandlerOrFalse = curl_init();
        if ($curlHandlerOrFalse === false) {
            throw new ClientException('Unable to init curl');
        }

        $this->curlHandler = $curlHandlerOrFalse;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'HEAD':
                curl_setopt($this->curlHandler, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->curlHandler, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->curlHandler, CURLOPT_POST, true);
                break;
            case 'DELETE':
            case 'PATCH':
            case 'OPTIONS':
            case 'PUT':
            default:
                curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }

        curl_setopt($this->curlHandler, CURLOPT_HTTP_VERSION, $this->getProtocolVersion($request));

        if ($request->getBody()->getSize()) {
            curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, (string) $request->getBody());
        }

        curl_setopt_array($this->curlHandler, $this->parameters);

        $headersParser = new HeadersParser();
        curl_setopt($this->curlHandler, CURLOPT_HEADERFUNCTION, array($headersParser, 'parseHeaders'));
        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, self::toHttpHeaders($request->getHeaders()));
        curl_setopt($this->curlHandler, CURLOPT_URL, (string) $request->getUri()->withFragment(''));

        $result = curl_exec($this->curlHandler);

        try {
            $errno = curl_errno($this->curlHandler);
            switch ($errno) {
                case CURLE_OK:
                    break;
                case CURLE_COULDNT_RESOLVE_PROXY:
                case CURLE_COULDNT_RESOLVE_HOST:
                case CURLE_COULDNT_CONNECT:
                case CURLE_OPERATION_TIMEOUTED:
                case CURLE_SSL_CONNECT_ERROR:
                case CURLOPT_DNS_CACHE_TIMEOUT:
                case CURLOPT_TIMEOUT:
                case CURLE_GOT_NOTHING:
                    throw new NetworkException(
                        $request,
                        curl_error($this->curlHandler),
                        $errno
                    );
                default:
                    throw new RequestException(
                        $request,
                        curl_error($this->curlHandler),
                        $errno
                    );
            }

            return new Response(
                curl_getinfo($this->curlHandler, CURLINFO_HTTP_CODE),
                $headersParser->getHeaders(),
                $result,
                // CURLINFO_HTTP_VERSION is supported only since 7.3
                defined('CURLINFO_HTTP_VERSION') ? (string)curl_getinfo($this->curlHandler, CURLINFO_HTTP_VERSION) : '1.1',
                // Should be empty string to auto populate reason inside Guzzle Response
                ''
            );
        } finally {
            /**
             * Remove all callback functions manually because they holds references and they are not cleaned
             * by curl_reset
             */
            curl_setopt($this->curlHandler, CURLOPT_HEADERFUNCTION, null);
            curl_setopt($this->curlHandler, CURLOPT_READFUNCTION, null);
            curl_setopt($this->curlHandler, CURLOPT_WRITEFUNCTION, null);
            curl_setopt($this->curlHandler, CURLOPT_PROGRESSFUNCTION, null);

            curl_reset($this->curlHandler);
        }
    }

    /**
     * Convert PSR-18 headers to HTTP headers
     *
     * @param array $headers
     * @return array
     */
    protected static function toHttpHeaders(array $headers): array
    {
        $result = [];

        foreach ($headers as $key => $values) {
            if (!\is_array($values)) {
                $result[] = sprintf('%s: %s', $key, $values);
            } else {
                foreach ($values as $value) {
                    $result[] = sprintf('%s: %s', $key, $value);
                }
            }
        }

        return $result;
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    protected function getProtocolVersion(RequestInterface $request): int
    {
        switch ($request->getProtocolVersion()) {
            case '1.0':
                return CURL_HTTP_VERSION_1_0;
            case '1.1':
                return CURL_HTTP_VERSION_1_1;
            case '2.0':
                if (\defined('CURL_HTTP_VERSION_2_0')) {
                    return CURL_HTTP_VERSION_2_0;
                }

                throw new ClientException('libcurl 7.33 is needed for HTTP 2.0 support');
            default:
                return CURL_HTTP_VERSION_NONE;
        }
    }

    public function __destruct()
    {
        curl_close($this->curlHandler);
    }

    /**
     * @param int $option
     * @param mixed $value
     * @return void
     */
    public function setCurlOption($option, $value)
    {
        curl_setopt($this->curlHandler, $option, $value);
    }
}
