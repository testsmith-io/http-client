<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\HttpClient\Exception;

class NetworkException extends RequestException implements \Psr\Http\Client\NetworkExceptionInterface
{
}
