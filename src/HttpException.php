<?php

require_once __DIR__ . '/http_response_codes.php';

/**
 * HTTP Exception class with static factory methods for common HTTP errors
 *
 * Usage:
 *   throw HttpException::NotFound('User not found');
 *   throw HttpException::Unauthorized('Please log in');
 *   throw HttpException::InternalServerError('Something went wrong');
 */
class HttpException extends Exception
{
    /**
     * @var array Default messages for HTTP status codes
     */
    private static $defaultMessages = array(
        // 4xx Client Errors
        HTTP_BAD_REQUEST => 'Bad Request',
        HTTP_UNAUTHORIZED => 'Unauthorized',
        HTTP_PAYMENT_REQUIRED => 'Payment Required',
        HTTP_FORBIDDEN => 'Forbidden',
        HTTP_NOT_FOUND => 'Not Found',
        HTTP_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        HTTP_NOT_ACCEPTABLE => 'Not Acceptable',
        HTTP_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        HTTP_REQUEST_TIMEOUT => 'Request Timeout',
        HTTP_CONFLICT => 'Conflict',
        HTTP_GONE => 'Gone',
        HTTP_LENGTH_REQUIRED => 'Length Required',
        HTTP_PRECONDITION_FAILED => 'Precondition Failed',
        HTTP_PAYLOAD_TOO_LARGE => 'Payload Too Large',
        HTTP_URI_TOO_LONG => 'URI Too Long',
        HTTP_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        HTTP_RANGE_NOT_SATISFIABLE => 'Range Not Satisfiable',
        HTTP_EXPECTATION_FAILED => 'Expectation Failed',
        HTTP_IM_A_TEAPOT => "I'm a teapot",
        HTTP_MISDIRECTED_REQUEST => 'Misdirected Request',
        HTTP_UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        HTTP_LOCKED => 'Locked',
        HTTP_FAILED_DEPENDENCY => 'Failed Dependency',
        HTTP_TOO_EARLY => 'Too Early',
        HTTP_UPGRADE_REQUIRED => 'Upgrade Required',
        HTTP_PRECONDITION_REQUIRED => 'Precondition Required',
        HTTP_TOO_MANY_REQUESTS => 'Too Many Requests',
        HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        HTTP_UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        // 5xx Server Errors
        HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        HTTP_NOT_IMPLEMENTED => 'Not Implemented',
        HTTP_BAD_GATEWAY => 'Bad Gateway',
        HTTP_SERVICE_UNAVAILABLE => 'Service Unavailable',
        HTTP_GATEWAY_TIMEOUT => 'Gateway Timeout',
        HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        HTTP_VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        HTTP_INSUFFICIENT_STORAGE => 'Insufficient Storage',
        HTTP_LOOP_DETECTED => 'Loop Detected',
        HTTP_NOT_EXTENDED => 'Not Extended',
        HTTP_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
    );

    /**
     * Create an HttpException with a status code
     *
     * @param int $code HTTP status code
     * @param string|null $message Custom message (uses default if null)
     * @param Exception|null $previous Previous exception
     * @return HttpException
     */
    public static function create($code, $message = null, $previous = null)
    {
        if ($message === null) {
            $message = isset(self::$defaultMessages[$code])
                ? self::$defaultMessages[$code]
                : 'Unknown Error';
        }
        return new self($message, $code, $previous);
    }

    // 4xx Client Errors

    /** @return HttpException */
    public static function BadRequest($message = null, $previous = null)
    {
        return self::create(HTTP_BAD_REQUEST, $message, $previous);
    }

    /** @return HttpException */
    public static function Unauthorized($message = null, $previous = null)
    {
        return self::create(HTTP_UNAUTHORIZED, $message, $previous);
    }

    /** @return HttpException */
    public static function PaymentRequired($message = null, $previous = null)
    {
        return self::create(HTTP_PAYMENT_REQUIRED, $message, $previous);
    }

    /** @return HttpException */
    public static function Forbidden($message = null, $previous = null)
    {
        return self::create(HTTP_FORBIDDEN, $message, $previous);
    }

    /** @return HttpException */
    public static function NotFound($message = null, $previous = null)
    {
        return self::create(HTTP_NOT_FOUND, $message, $previous);
    }

    /** @return HttpException */
    public static function MethodNotAllowed($message = null, $previous = null)
    {
        return self::create(HTTP_METHOD_NOT_ALLOWED, $message, $previous);
    }

    /** @return HttpException */
    public static function NotAcceptable($message = null, $previous = null)
    {
        return self::create(HTTP_NOT_ACCEPTABLE, $message, $previous);
    }

    /** @return HttpException */
    public static function ProxyAuthenticationRequired($message = null, $previous = null)
    {
        return self::create(HTTP_PROXY_AUTHENTICATION_REQUIRED, $message, $previous);
    }

    /** @return HttpException */
    public static function RequestTimeout($message = null, $previous = null)
    {
        return self::create(HTTP_REQUEST_TIMEOUT, $message, $previous);
    }

    /** @return HttpException */
    public static function Conflict($message = null, $previous = null)
    {
        return self::create(HTTP_CONFLICT, $message, $previous);
    }

    /** @return HttpException */
    public static function Gone($message = null, $previous = null)
    {
        return self::create(HTTP_GONE, $message, $previous);
    }

    /** @return HttpException */
    public static function LengthRequired($message = null, $previous = null)
    {
        return self::create(HTTP_LENGTH_REQUIRED, $message, $previous);
    }

    /** @return HttpException */
    public static function PreconditionFailed($message = null, $previous = null)
    {
        return self::create(HTTP_PRECONDITION_FAILED, $message, $previous);
    }

    /** @return HttpException */
    public static function PayloadTooLarge($message = null, $previous = null)
    {
        return self::create(HTTP_PAYLOAD_TOO_LARGE, $message, $previous);
    }

    /** @return HttpException */
    public static function UriTooLong($message = null, $previous = null)
    {
        return self::create(HTTP_URI_TOO_LONG, $message, $previous);
    }

    /** @return HttpException */
    public static function UnsupportedMediaType($message = null, $previous = null)
    {
        return self::create(HTTP_UNSUPPORTED_MEDIA_TYPE, $message, $previous);
    }

    /** @return HttpException */
    public static function RangeNotSatisfiable($message = null, $previous = null)
    {
        return self::create(HTTP_RANGE_NOT_SATISFIABLE, $message, $previous);
    }

    /** @return HttpException */
    public static function ExpectationFailed($message = null, $previous = null)
    {
        return self::create(HTTP_EXPECTATION_FAILED, $message, $previous);
    }

    /** @return HttpException */
    public static function ImATeapot($message = null, $previous = null)
    {
        return self::create(HTTP_IM_A_TEAPOT, $message, $previous);
    }

    /** @return HttpException */
    public static function MisdirectedRequest($message = null, $previous = null)
    {
        return self::create(HTTP_MISDIRECTED_REQUEST, $message, $previous);
    }

    /** @return HttpException */
    public static function UnprocessableEntity($message = null, $previous = null)
    {
        return self::create(HTTP_UNPROCESSABLE_ENTITY, $message, $previous);
    }

    /** @return HttpException */
    public static function Locked($message = null, $previous = null)
    {
        return self::create(HTTP_LOCKED, $message, $previous);
    }

    /** @return HttpException */
    public static function FailedDependency($message = null, $previous = null)
    {
        return self::create(HTTP_FAILED_DEPENDENCY, $message, $previous);
    }

    /** @return HttpException */
    public static function TooEarly($message = null, $previous = null)
    {
        return self::create(HTTP_TOO_EARLY, $message, $previous);
    }

    /** @return HttpException */
    public static function UpgradeRequired($message = null, $previous = null)
    {
        return self::create(HTTP_UPGRADE_REQUIRED, $message, $previous);
    }

    /** @return HttpException */
    public static function PreconditionRequired($message = null, $previous = null)
    {
        return self::create(HTTP_PRECONDITION_REQUIRED, $message, $previous);
    }

    /** @return HttpException */
    public static function TooManyRequests($message = null, $previous = null)
    {
        return self::create(HTTP_TOO_MANY_REQUESTS, $message, $previous);
    }

    /** @return HttpException */
    public static function RequestHeaderFieldsTooLarge($message = null, $previous = null)
    {
        return self::create(HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE, $message, $previous);
    }

    /** @return HttpException */
    public static function UnavailableForLegalReasons($message = null, $previous = null)
    {
        return self::create(HTTP_UNAVAILABLE_FOR_LEGAL_REASONS, $message, $previous);
    }

    // 5xx Server Errors

    /** @return HttpException */
    public static function InternalServerError($message = null, $previous = null)
    {
        return self::create(HTTP_INTERNAL_SERVER_ERROR, $message, $previous);
    }

    /** @return HttpException */
    public static function NotImplemented($message = null, $previous = null)
    {
        return self::create(HTTP_NOT_IMPLEMENTED, $message, $previous);
    }

    /** @return HttpException */
    public static function BadGateway($message = null, $previous = null)
    {
        return self::create(HTTP_BAD_GATEWAY, $message, $previous);
    }

    /** @return HttpException */
    public static function ServiceUnavailable($message = null, $previous = null)
    {
        return self::create(HTTP_SERVICE_UNAVAILABLE, $message, $previous);
    }

    /** @return HttpException */
    public static function GatewayTimeout($message = null, $previous = null)
    {
        return self::create(HTTP_GATEWAY_TIMEOUT, $message, $previous);
    }

    /** @return HttpException */
    public static function HttpVersionNotSupported($message = null, $previous = null)
    {
        return self::create(HTTP_VERSION_NOT_SUPPORTED, $message, $previous);
    }

    /** @return HttpException */
    public static function VariantAlsoNegotiates($message = null, $previous = null)
    {
        return self::create(HTTP_VARIANT_ALSO_NEGOTIATES, $message, $previous);
    }

    /** @return HttpException */
    public static function InsufficientStorage($message = null, $previous = null)
    {
        return self::create(HTTP_INSUFFICIENT_STORAGE, $message, $previous);
    }

    /** @return HttpException */
    public static function LoopDetected($message = null, $previous = null)
    {
        return self::create(HTTP_LOOP_DETECTED, $message, $previous);
    }

    /** @return HttpException */
    public static function NotExtended($message = null, $previous = null)
    {
        return self::create(HTTP_NOT_EXTENDED, $message, $previous);
    }

    /** @return HttpException */
    public static function NetworkAuthenticationRequired($message = null, $previous = null)
    {
        return self::create(HTTP_NETWORK_AUTHENTICATION_REQUIRED, $message, $previous);
    }
}
