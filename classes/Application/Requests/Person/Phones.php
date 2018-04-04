<?php

namespace Application\Requests\Person;

abstract class Phones extends \Application\Requests\Person {

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ 'number', 'hint', 'prefer' ];
    
    /**
     * @var string table name
     */
    protected $tableName = 'persons_phones';

    /**
     * @var string object type name 
     */
    protected $typeName = 'phone';

    /**
     * @var string requested object id
     */
    protected $uriIdField = 'phoneId';
    
    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'phoneid' ];

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
        
        if(array_key_exists('phoneid', $options)) {
            $this->phoneId = $options['phoneid'];
        }
    }

}
