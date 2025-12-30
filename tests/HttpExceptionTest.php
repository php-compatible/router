<?php

use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    // 4xx Client Errors

    public function test_bad_request()
    {
        $e = HttpException::BadRequest();
        $this->assertEquals(400, $e->getCode());
        $this->assertEquals('Bad Request', $e->getMessage());
    }

    public function test_bad_request_custom_message()
    {
        $e = HttpException::BadRequest('Invalid input');
        $this->assertEquals(400, $e->getCode());
        $this->assertEquals('Invalid input', $e->getMessage());
    }

    public function test_unauthorized()
    {
        $e = HttpException::Unauthorized();
        $this->assertEquals(401, $e->getCode());
        $this->assertEquals('Unauthorized', $e->getMessage());
    }

    public function test_payment_required()
    {
        $e = HttpException::PaymentRequired();
        $this->assertEquals(402, $e->getCode());
        $this->assertEquals('Payment Required', $e->getMessage());
    }

    public function test_forbidden()
    {
        $e = HttpException::Forbidden();
        $this->assertEquals(403, $e->getCode());
        $this->assertEquals('Forbidden', $e->getMessage());
    }

    public function test_forbidden_custom_message()
    {
        $e = HttpException::Forbidden('Access denied');
        $this->assertEquals(403, $e->getCode());
        $this->assertEquals('Access denied', $e->getMessage());
    }

    public function test_not_found()
    {
        $e = HttpException::NotFound();
        $this->assertEquals(404, $e->getCode());
        $this->assertEquals('Not Found', $e->getMessage());
    }

    public function test_not_found_custom_message()
    {
        $e = HttpException::NotFound('User not found');
        $this->assertEquals(404, $e->getCode());
        $this->assertEquals('User not found', $e->getMessage());
    }

    public function test_method_not_allowed()
    {
        $e = HttpException::MethodNotAllowed();
        $this->assertEquals(405, $e->getCode());
        $this->assertEquals('Method Not Allowed', $e->getMessage());
    }

    public function test_not_acceptable()
    {
        $e = HttpException::NotAcceptable();
        $this->assertEquals(406, $e->getCode());
        $this->assertEquals('Not Acceptable', $e->getMessage());
    }

    public function test_proxy_authentication_required()
    {
        $e = HttpException::ProxyAuthenticationRequired();
        $this->assertEquals(407, $e->getCode());
        $this->assertEquals('Proxy Authentication Required', $e->getMessage());
    }

    public function test_request_timeout()
    {
        $e = HttpException::RequestTimeout();
        $this->assertEquals(408, $e->getCode());
        $this->assertEquals('Request Timeout', $e->getMessage());
    }

    public function test_conflict()
    {
        $e = HttpException::Conflict();
        $this->assertEquals(409, $e->getCode());
        $this->assertEquals('Conflict', $e->getMessage());
    }

    public function test_gone()
    {
        $e = HttpException::Gone();
        $this->assertEquals(410, $e->getCode());
        $this->assertEquals('Gone', $e->getMessage());
    }

    public function test_length_required()
    {
        $e = HttpException::LengthRequired();
        $this->assertEquals(411, $e->getCode());
        $this->assertEquals('Length Required', $e->getMessage());
    }

    public function test_precondition_failed()
    {
        $e = HttpException::PreconditionFailed();
        $this->assertEquals(412, $e->getCode());
        $this->assertEquals('Precondition Failed', $e->getMessage());
    }

    public function test_payload_too_large()
    {
        $e = HttpException::PayloadTooLarge();
        $this->assertEquals(413, $e->getCode());
        $this->assertEquals('Payload Too Large', $e->getMessage());
    }

    public function test_uri_too_long()
    {
        $e = HttpException::UriTooLong();
        $this->assertEquals(414, $e->getCode());
        $this->assertEquals('URI Too Long', $e->getMessage());
    }

    public function test_unsupported_media_type()
    {
        $e = HttpException::UnsupportedMediaType();
        $this->assertEquals(415, $e->getCode());
        $this->assertEquals('Unsupported Media Type', $e->getMessage());
    }

    public function test_range_not_satisfiable()
    {
        $e = HttpException::RangeNotSatisfiable();
        $this->assertEquals(416, $e->getCode());
        $this->assertEquals('Range Not Satisfiable', $e->getMessage());
    }

    public function test_expectation_failed()
    {
        $e = HttpException::ExpectationFailed();
        $this->assertEquals(417, $e->getCode());
        $this->assertEquals('Expectation Failed', $e->getMessage());
    }

    public function test_im_a_teapot()
    {
        $e = HttpException::ImATeapot();
        $this->assertEquals(418, $e->getCode());
        $this->assertEquals("I'm a teapot", $e->getMessage());
    }

    public function test_misdirected_request()
    {
        $e = HttpException::MisdirectedRequest();
        $this->assertEquals(421, $e->getCode());
        $this->assertEquals('Misdirected Request', $e->getMessage());
    }

    public function test_unprocessable_entity()
    {
        $e = HttpException::UnprocessableEntity();
        $this->assertEquals(422, $e->getCode());
        $this->assertEquals('Unprocessable Entity', $e->getMessage());
    }

    public function test_locked()
    {
        $e = HttpException::Locked();
        $this->assertEquals(423, $e->getCode());
        $this->assertEquals('Locked', $e->getMessage());
    }

    public function test_failed_dependency()
    {
        $e = HttpException::FailedDependency();
        $this->assertEquals(424, $e->getCode());
        $this->assertEquals('Failed Dependency', $e->getMessage());
    }

    public function test_too_early()
    {
        $e = HttpException::TooEarly();
        $this->assertEquals(425, $e->getCode());
        $this->assertEquals('Too Early', $e->getMessage());
    }

    public function test_upgrade_required()
    {
        $e = HttpException::UpgradeRequired();
        $this->assertEquals(426, $e->getCode());
        $this->assertEquals('Upgrade Required', $e->getMessage());
    }

    public function test_precondition_required()
    {
        $e = HttpException::PreconditionRequired();
        $this->assertEquals(428, $e->getCode());
        $this->assertEquals('Precondition Required', $e->getMessage());
    }

    public function test_too_many_requests()
    {
        $e = HttpException::TooManyRequests();
        $this->assertEquals(429, $e->getCode());
        $this->assertEquals('Too Many Requests', $e->getMessage());
    }

    public function test_request_header_fields_too_large()
    {
        $e = HttpException::RequestHeaderFieldsTooLarge();
        $this->assertEquals(431, $e->getCode());
        $this->assertEquals('Request Header Fields Too Large', $e->getMessage());
    }

    public function test_unavailable_for_legal_reasons()
    {
        $e = HttpException::UnavailableForLegalReasons();
        $this->assertEquals(451, $e->getCode());
        $this->assertEquals('Unavailable For Legal Reasons', $e->getMessage());
    }

    // 5xx Server Errors

    public function test_internal_server_error()
    {
        $e = HttpException::InternalServerError();
        $this->assertEquals(500, $e->getCode());
        $this->assertEquals('Internal Server Error', $e->getMessage());
    }

    public function test_not_implemented()
    {
        $e = HttpException::NotImplemented();
        $this->assertEquals(501, $e->getCode());
        $this->assertEquals('Not Implemented', $e->getMessage());
    }

    public function test_bad_gateway()
    {
        $e = HttpException::BadGateway();
        $this->assertEquals(502, $e->getCode());
        $this->assertEquals('Bad Gateway', $e->getMessage());
    }

    public function test_service_unavailable()
    {
        $e = HttpException::ServiceUnavailable();
        $this->assertEquals(503, $e->getCode());
        $this->assertEquals('Service Unavailable', $e->getMessage());
    }

    public function test_gateway_timeout()
    {
        $e = HttpException::GatewayTimeout();
        $this->assertEquals(504, $e->getCode());
        $this->assertEquals('Gateway Timeout', $e->getMessage());
    }

    public function test_http_version_not_supported()
    {
        $e = HttpException::HttpVersionNotSupported();
        $this->assertEquals(505, $e->getCode());
        $this->assertEquals('HTTP Version Not Supported', $e->getMessage());
    }

    public function test_variant_also_negotiates()
    {
        $e = HttpException::VariantAlsoNegotiates();
        $this->assertEquals(506, $e->getCode());
        $this->assertEquals('Variant Also Negotiates', $e->getMessage());
    }

    public function test_insufficient_storage()
    {
        $e = HttpException::InsufficientStorage();
        $this->assertEquals(507, $e->getCode());
        $this->assertEquals('Insufficient Storage', $e->getMessage());
    }

    public function test_loop_detected()
    {
        $e = HttpException::LoopDetected();
        $this->assertEquals(508, $e->getCode());
        $this->assertEquals('Loop Detected', $e->getMessage());
    }

    public function test_not_extended()
    {
        $e = HttpException::NotExtended();
        $this->assertEquals(510, $e->getCode());
        $this->assertEquals('Not Extended', $e->getMessage());
    }

    public function test_network_authentication_required()
    {
        $e = HttpException::NetworkAuthenticationRequired();
        $this->assertEquals(511, $e->getCode());
        $this->assertEquals('Network Authentication Required', $e->getMessage());
    }

    // General tests

    public function test_create_with_code()
    {
        $e = HttpException::create(409, 'Resource conflict');
        $this->assertEquals(409, $e->getCode());
        $this->assertEquals('Resource conflict', $e->getMessage());
    }

    public function test_create_with_unknown_code()
    {
        $e = HttpException::create(999);
        $this->assertEquals(999, $e->getCode());
        $this->assertEquals('Unknown Error', $e->getMessage());
    }

    public function test_exception_is_throwable()
    {
        $this->expectException(HttpException::class);
        throw HttpException::NotFound('Page not found');
    }

    public function test_previous_exception()
    {
        $previous = new Exception('Database error');
        $e = HttpException::InternalServerError('Something went wrong', $previous);

        $this->assertEquals(500, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }
}
