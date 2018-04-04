<?php

namespace Util\Http;

class RequestHandler {

    /**
     * @var array Common http status codes
     */
    protected static $httpCodes = array(
        100 => 'Continue',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        503 => 'Service Unavailable'
    );

    /**
     * @var array Representation of the Apache $_SERVER variable
     */
    protected $httpOptions = null;

    /**
     * Constructor for the RequestHandler.
     * @param array $options Representation of the Apache $_SERVER variable
     */
    public function __construct(array $options) {
        $this->httpOptions = $options;
    }

    /**
     * Returns the payload sent by this request
     * @return string The requests data transfered in the http body
     */
    public static function getRawPostData() {
        return file_get_contents('php://input');
    }

    /**
     * Returns the unhandeled part of the requested URI. May be null.
     * @return string|null The unhandeled part of the requested URI
     */
    public function getPathInfo() {
        return @$this->httpOptions['PATH_INFO'] ?: '';
    }

    /**
     * Returns the http method used for this request. May be null.
     * @return string|null The http method used for this request
     */
    public function getMethod() {
        return $this->httpOptions['REQUEST_METHOD'];
    }

    /**
     * Returns the username read from the http authorization header used to 
     * send this request. May be null.
     * @return string|null The username used to send this request
     */
    public function getAuthUser() {
        return $this->httpOptions['PHP_AUTH_USER'];
    }

    /**
     * Returns the password read from the http authorization header used to 
     * send this request. May be null.
     * @return string|null The password used to send this request
     */
    public function getAuthPassword() {
        return $this->httpOptions['PHP_AUTH_PW'];
    }

    /**
     * Set the http status code for the response. The status text is derived 
     * from the status code by default but may be overridden.
     * @param integer $code Status code to be sent to the client
     * @param string $message Optional text to be sent with the status code
     * @return RequestHandler
     * @throws Exceptions\HeadersSent
     */
    public static function sendStatus($code, $protocol = 'HTTP/1.0', $message = null) {
        if (headers_sent()) {
            throw new Exceptions\HeadersSent();
        }
        
        header(
                sprintf(
                        '%s %d %s', 
                        $protocol, 
                        $code, 
                        $message ?: static::$httpCodes[strval($code)]
                )
        );
    }

    /**
     * Require the client to resend this request with an authentication.
     * @param string $realm Authentication context name
     * @return RequestHandler
     * @throws Exceptions\HeadersSent
     */
    public static function sendHttpAuthChallenge($realm) {
        if (headers_sent()) {
            throw new Exceptions\HeadersSent();
        }
        
        header(
                sprintf(
                        'WWW-Authenticate: Basic realm="%s"', $realm
                )
        );
    }

    /**
     * Set the content type of the response.
     * @param string $mimeType The content type of the response
     * @throws Exceptions\HeadersSent
     */
    public static function sendContentType($mimeType) {
        if (headers_sent()) {
            throw new Exceptions\HeadersSent();
        }
        
        header(
                sprintf(
                        'Content-Type: %s', $mimeType
                )
        );
    }
}