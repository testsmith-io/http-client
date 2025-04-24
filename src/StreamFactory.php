<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\HttpClient;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return \GuzzleHttp\Psr7\Utils::streamFor($content);
    }
    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        try {
            $resource = \GuzzleHttp\Psr7\Utils::try_fopen($filename, $mode);
        } catch (\RuntimeException $e) {
            if ('' === $mode || false === \in_array($mode[0], ['r', 'w', 'a', 'x', 'c'], true)) {
                throw new \InvalidArgumentException(sprintf('Invalid file opening mode "%s"', $mode), 0, $e);
            }
            throw $e;
        }
        return \GuzzleHttp\Psr7\Utils::streamFor($resource);
    }
    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return \GuzzleHttp\Psr7\Utils::streamFor($resource);
    }
}
