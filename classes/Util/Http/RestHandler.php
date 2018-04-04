<?php

namespace Util\Http;

class RestHandler extends RequestHandler {

    /**
     * @var string The original full path info.
     */
    private $originalPathInfo = null;

    /**
     * Constructor for the RequestHandler.
     * @param array $options Representation of the Apache $_SERVER variable
     */
    public function __construct(array $options) {
        parent::__construct($options);
        
        $this->originalPathInfo = @$this->httpOptions['PATH_INFO'] ?: '';
        $this->httpOptions['PATH_INFO'] = 
                explode('/', 
                        trim(@$this->httpOptions['PATH_INFO'] ?: '', '/')
                );
    }

    /**
     * Returns the unhandeled parts of the requested URI as an array.
     * @return array The unhandeled parts of the requested URI 
     */
    public function getPathInfo() {
        return parent::getPathInfo();
    }

    /**
     * Returns the original path info as string.
     * @return string The original path info as string
     */
    public function getOriginalPathInfo() {
        return $this->originalPathInfo;
    }

    /**
     * Returns the Camel written next component of the unhandeled uri. 
     * May be null.
     * @param type $remove Remove the returned component from the remaining list.
     * @return string|null Next component of the unhandeled uri
     */
    public function getNextPathComponent($remove = true) {
        $next = $remove 
                ? array_shift($this->httpOptions['PATH_INFO'])
                : reset($this->httpOptions['PATH_INFO']);
        return $next !== null ? ucfirst($next) : null;
    }
    
    /**
     * Returns the Camel written http method used for this request.
     * @return string Camel written name of the http method
     */
    public function getMethod() {
        return ucfirst(strtolower(parent::getMethod()));
    }

}