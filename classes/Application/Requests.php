<?php

namespace Application;

abstract class Requests {
    
    /**
     * Returns the class handling this request.
     * @param \Util\Http\RestHandler $request
     * @param array $options
     * @return Requests
     */
    public static function dispatchPath($request, $options = array()) {
        $path = $request->getNextPathComponent();
        if($path === null) {
            return static::dispatchMethod($request, $options);
        }
        
        $className = get_called_class();
        $nextClassName = sprintf('%s\%s', $className, $path);
        error_log($nextClassName);
        if(!class_exists($nextClassName, true)) {
            error_log($nextClassName . ': loading as option');
            static::loadOption($options, $path, $request);
            return static::dispatchPath($request, $options);
        }
        
        return $nextClassName::dispatchPath($request, $options);
    }
    
    /**
     * Returns the class handling the method for this request.
     * @param \Util\Http\RestHandler $request
     * @param array $options
     * @throws \Util\Http\Exceptions\BadRequestUri
     * @return Requests
     */
    protected static function dispatchMethod($request, $options) {
        $method = $request->getMethod();
        $className = get_called_class();
        $nextClassName = sprintf('%s\%s', $className, $method);
        
        error_log($nextClassName);
        if(!class_exists($nextClassName, true)) {
            throw new \Util\Http\Exceptions\BadRequestUri(
                    $request->getOriginalPathInfo()
                );
        }
        
        return new $nextClassName($request, $options);
    }
    
    /**
     * @var array List of sorted keys in $options where unhandeled path fragments  should be loaded in
     */
    protected static $optionNames = null;
    
    /**
     * Loads a path fragment with no corrosponding class as class-specific option.
     * The option name is read from a sorted list
     * of names stored in $optionNames. If no more $optionsNames 
     * are set or the key already exists, an error will be thrown.
     * @param type $options List of current options (to be appended)
     * @param type $path The Current path fragment
     * @param type $request Optional request context used for better error messages
     * @throws \Util\Http\Exceptions\BadRequestUri
     */
    protected static function loadOption(&$options, $path, $request = null) {
        error_log(print_r(static::$optionNames,1));
        error_log(print_r($options,1));
        $optionName = array_shift(static::$optionNames);
        if ( $optionName === null 
                || array_key_exists($optionName, $options)) {
            throw new \Util\Http\Exceptions\BadRequestUri(
                $request
                    ? $request->getOriginalPathInfo()
                    : $path
            );
        }
        
        $options[$optionName] = $path;
    }
    
    /**
     * @var \Util\Http\RestHandler
     */
    protected $request = null;
    
    /**
     * @var array
     */
    protected $options = null;
    
    /**
     * @var \Application\Db
     */
    protected $db = null;
    
    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        $this->request = $request;
        $this->options = $options;
        
        $this->db = new \Application\Db();
        $this->db->exec('PRAGMA FOREIGN_KEYS=ON');
        
        /*
         * setup database on request (flag is set when calling
         * /accounts/self during login
         */
        if(@$options['setup'] === true ) {
            $this->db->setup();
        }
        
        $this->authenticateRequest();
    }

    /**
     * @var array Details of the authenticated user performing this request
     */
    protected $authenticatedUserDetails = null;

    /**
     * Returns the user id of the authenticated request user.
     * @return string User id of the authenticated request user
     */    
    protected function getAuthenticatedUserId() {
        return $this->authenticatedUserDetails['userid'];
    }
    
    /**
     * Returns the list of allowed functions for the authenticated user.
     * @return array List of allowed functions for the authenticated user
     */    
    protected function getAuthorizedFunctions() {
        if (!array_key_exists('functions', $this->authenticatedUserDetails)) {
             $functions = $this->db->query(
                    'SELECT functions.id FROM functions JOIN users_to_functions ON users_to_functions.functionid=functions.id WHERE users_to_functions.userid = :id', 
                    [ 'id' => $this->authenticatedUserDetails['userid'] ]
                );
            $this->authenticatedUserDetails['functions'] = [];
            foreach($functions as $func => $desc) {
                $this->authenticatedUserDetails['functions'][] = $func;
            }
        }
        
        return $this->authenticatedUserDetails['functions'];
    }
    
    /**
     * Returns the list of allowed locations for the authenticated user.
     * @return array List of allowed locations for the authenticated user
     */    
    protected function getAuthorizedLocations() {
        if (!array_key_exists('locations', $this->authenticatedUserDetails)) {
             $locations = $this->db->query(
                    'SELECT locations.id FROM locations JOIN users_to_locations ON users_to_locations.id=locations.id WHERE users_to_locations.userid = :id', 
                    [ 'id' => $this->authenticatedUserDetails['userid'] ]
                );
            $this->authenticatedUserDetails['locations'] = [];
            foreach($locations as $loc => $desc) {
                $this->authenticatedUserDetails['locations'][] = $loc;
            }
        }
        
        return $this->authenticatedUserDetails['locations'];
    }
    
    /**
     * Returns the list of allowed courses for the authenticated user.
     * @return array List of allowed courses for the authenticated user
     */    
    protected function getAuthorizedCourses() {
        if (!array_key_exists('courses', $this->authenticatedUserDetails)) {
            $courses = $this->db->query(
                    'SELECT courses.id FROM users_to_courses LEFT JOIN courses ON users_to_courses.courseid=courses.id WHERE users_to_courses.userid = :id', 
                    [ 'id' => $this->authenticatedUserDetails['userid'] ]
                );
            
            $this->authenticatedUserDetails['courses'] = [];
            foreach($courses as $loc => $desc) {
                $this->authenticatedUserDetails['courses'][] = $desc['id'];
            }
        }
        
        return $this->authenticatedUserDetails['courses'];
    }

    /**
     * Authenticates the current request credentials and loads some user details.
     * @throws \Util\Http\Exceptions\InvalidAuthentication
     */
    public function authenticateRequest() {
        if ($this->authenticatedUserDetails === null) {
            $results = $this->db->query(
                    'SELECT id, password FROM users WHERE active = 1 AND name = :username LIMIT 1', 
                    [ 'username' => $this->request->getAuthUser() ]
                );
            
            $result = array_shift($results);
            
            if ($result && password_verify($this->request->getAuthPassword(), $result['password'])) {
                $this->authenticatedUserDetails = array('userid' => $result['id']);
            }
        }
        
        if ($this->authenticatedUserDetails === null) {
            throw new \Util\Http\Exceptions\InvalidAuthentication();
        }        
    }
    
    /**
     * Performs request authorization and returns some data to the client.
     * @throws \Util\Http\Exceptions\Forbidden
     */
    public function send() {
        if(!$this->_isAuthorized()) {
            throw new \Util\Http\Exceptions\Forbidden();
        }
        
        $content = $this->_send();
        if ($content === null) {
            $this->request->sendStatus(204);
        }
        else if ($content === true) {
            $this->request->sendStatus(201);
        }
        else {
            $this->request->sendStatus(200);
            $this->request->sendContentType('application/json');
            echo json_encode($content);
        }
    }
    
    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    abstract protected function _isAuthorized();
    
    /**
     * Returns the http response as string.
     * @return string|boolean|null
     */
    abstract protected function _send();
 
    /**
     * @param type $idName Name of ID field
     * @param type $fnName Name of delete function
     * @return null
     * @throws \Util\Http\Exceptions\MethodNotAllowed
     * @throws \Util\Http\Exceptions\Forbidden
     */
    protected function _sendDelete($idName, $fnName) {
        if ($this->$idName === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            $this->$fnName($this->$idName);
        }
        catch (\Util\Db\ExecutionException $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
            
        return null;
    }
    
    /**
     * @param type $idName Name of ID field
     * @param type $fnName Name of single-get function
     * @param type $fnNames Name of multi-get function
     * @return array
     * @throws \Util\Http\Exceptions\NotFound
     */
    protected function _sendGet($idName, $fnName, $fnNames) {
        $response = $this->$idName
            ? $this->$fnName($this->$idName)
            : $this->$fnNames();
        
        if ($response === null) {
            throw new \Util\Http\Exceptions\NotFound();
        }
        
        return $response;
    }
    
    /**
     * @param type $idName Name of ID field
     * @param type $fnName Name of function
     * @return array
     * @throws \Util\Http\Exceptions\MethodNotAllowed
     * @throws \Util\Http\Exceptions\Forbidden
     */
    protected function _sendPatch($idName, $fnName) {
        if ($this->$idName === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            $this->$fnName(
                    $this->$idName,
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\Exception $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
        
        //@todo http status for PATCH
        return array();
    }

    /**
     * @param type $idName Name of ID field (has to be null)
     * @param type $fnName Name of function
     * @return array
     * @throws \Util\Http\Exceptions\MethodNotAllowed
     * @throws \Util\Http\Exceptions\Forbidden
     */
    protected function _sendPost($idName, $fnName) {
        if ($this->$idName !== null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            return $this->$fnName(
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\ExecutionException $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
    }
    
    /**
     * Returns true if the authenticated user is allowed to acces function $name.
     * @param string $name Function name
     * @return boolean
     */
    protected function isPermittedUserFunction($name) {
       return in_array($name, $this->getAuthorizedFunctions());
    }
    
    /**
     * Returns true if the authenticated user is allowed to acces location $name.
     * @param string $name Location name
     * @return boolean
     */
    protected function isPermittedUserLocation($name) {
        return in_array($name, $this->getAuthorizedLocations());
    }

    /**
     * Returns the requests paylod as json-parsed array.
     * @return array The requests paylod as json-parsed array
     * @throws \Util\Http\Exceptions\BadRequest
     */
    protected function getJsonPostData() {
        $data = json_decode($this->request->getRawPostData(), true);
        if ( $data === null ) {
            throw new \Util\Http\Exceptions\BadRequest('unreadable data');
        }
        return $data;
    }
}