<?php

namespace Application\Requests\Person\Activities;

abstract class Notes extends \Application\Requests\Person\Activities {
    
    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'noteid' ];

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if($this->activityId === null) {
            throw new \Util\Http\Exceptions\BadRequestUri(
                    $request->getOriginalPathInfo()
                );
        }
        
        if(array_key_exists('noteid', $options)) {
            $this->noteId = $options['noteid'];
        }
    }

}
