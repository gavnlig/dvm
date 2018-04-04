<?php

namespace Application\Requests\Person;

abstract class Activities extends \Application\Requests\Person {
    
    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'activityid' ];

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if($this->personId === null) {
            throw new \Util\Http\Exceptions\BadRequestUri(
                    $request->getOriginalPathInfo()
                );
        }
        
        if(array_key_exists('activityid', $options)) {
            $this->activityId = $options['activityid'];
        }
    }
}