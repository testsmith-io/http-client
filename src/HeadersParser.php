<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\HttpClient;

class HeadersParser
{
    /**
     * @var string[][]
     */
    protected $headers = [];

    /**
     * @param resource $client
     * @param string $headerLine
     * @return int
     */
    public function parseHeaders($client, $headerLine)
    {
        $parts = explode(':', $headerLine, 2);
        if (count($parts) == 2) {
            list ($name, $value) = $parts;
            $this->headers[trim($name)][] = trim($value);
        }

        return mb_strlen($headerLine, '8bit');
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
