<?php

namespace que\http;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/1/2019
 * Time: 9:44 AM
 */

use Exception;
use que\http\input\Input;
use que\http\network\Redirect;
use que\http\curl\CurlRequest;
use que\http\output\HttpResponse;
use que\http\request\Delete;
use que\http\request\Files;
use que\http\request\Get;
use que\http\request\Header;
use que\http\request\Patch;
use que\http\request\Post;
use que\http\request\Put;
use que\http\request\Request;
use que\http\request\Server;
use que\session\Session;
use ReflectionClass;

class HTTP
{
    /**
     * HTTP status code constants
     */
    const CONTINUE = 100;
    const SWITCHING_PROTOCOLS = 101;
    const PROCESSING = 102;            // RFC2518
    const EARLY_HINTS = 103;           // RFC8297
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;
    const MULTI_STATUS = 207;          // RFC4918
    const ALREADY_REPORTED = 208;      // RFC5842
    const IM_USED = 226;               // RFC3229
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const RESERVED = 306;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENTLY_REDIRECT = 308;  // RFC7238
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUEST_URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;
    const I_AM_A_TEAPOT = 418;
    const EXPIRED_AUTHENTICATION = 419;
    const MAINTENANCE = 420;                                                 // RFC2324
    const MISDIRECTED_REQUEST = 421;                                         // RFC7540
    const UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const LOCKED = 423;                                                      // RFC4918
    const FAILED_DEPENDENCY = 424;                                           // RFC4918
    const TOO_EARLY = 425;                                                   // RFC-ietf-httpbis-replay-04
    const UPGRADE_REQUIRED = 426;                                            // RFC2817
    const PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const VERSION_NOT_SUPPORTED = 505;
    const VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const LOOP_DETECTED = 508;                                               // RFC5842
    const NOT_EXTENDED = 510;                                                // RFC2774
    const NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * @var HTTP
     */
    private static HTTP $instance;

    /**
     * CurlRequest constructor.
     */
    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return HTTP
     */
    public static function getInstance(): HTTP
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param null $default
     * @return mixed|null
     */
    public function getReferer($default = null)
    {

        $referer = Session::getInstance()->getFiles()->get("http.referer");
        if (!empty($referer)) {
            Session::getInstance()->getFiles()->_unset("http.referer");
            return $referer;
        }
        $referer = $this->_header()->get("Referer");
        return !empty($referer) ? $referer : $default;
    }

    /**
     * @param int|null $code
     * @return int|mixed
     */
    public function http_response_code(int $code = null)
    {
        if ($code === null) $code = 200;
        header(server('SERVER_PROTOCOL', 'HTTP/1.0') . " {$code} {$this->getHttpStatusTxt($code)}");
        return $code;
    }

    private function getHttpStatusTxt(int $code)
    {
        try {
            $text = array_search($code, (new ReflectionClass(self::class))->getConstants()) ?: "Unknown Status Code";
            $text = str_replace("_", " ", $text);
            return ucwords(strtolower($text));
        } catch (Exception $exception) {
            return "Unknown Status Code";
        }
    }

    /**
     * @return Redirect
     */
    public function redirect(): Redirect {
        return Redirect::getInstance();
    }

    /**
     * @return CurlRequest
     */
    public function curl_request(): CurlRequest {
        return CurlRequest::getInstance();
    }

    /**
     * @return Get
     */
    public function _get(): Get {
        return Get::getInstance();
    }

    /**
     * @return Post
     */
    public function _post(): Post {
        return Post::getInstance();
    }

    /**
     * @return Put
     */
    public function _put(): Put {
        return Put::getInstance();
    }

    /**
     * @return Patch
     */
    public function _patch(): Patch {
        return Patch::getInstance();
    }

    /**
     * @return Delete
     */
    public function _delete(): Delete {
        return Delete::getInstance();
    }

    /**
     * @return Files
     */
    public function _files(): Files {
        return Files::getInstance();
    }

    /**
     * @return Server
     */
    public function _server(): Server {
        return Server::getInstance();
    }

    /**
     * @return Request
     */
    public function _request(): Request {
        return Request::getInstance();
    }

    /**
     * @return Header
     */
    public function _header(): Header {
        return Header::getInstance();
    }

    /**
     * @return Input
     */
    public function input(): Input {
        return Input::getInstance();
    }

    /**
     * @return HttpResponse
     */
    public function output(): HttpResponse {
        return HttpResponse::getInstance();
    }

}
