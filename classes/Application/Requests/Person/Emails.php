<?php

namespace Application\Requests\Person;

abstract class Emails extends \Application\Requests\Person {

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ 'address', 'hint', 'prefer' ];
    
    /**
     * @var string table name
     */
    protected $tableName = 'persons_emails';

    /**
     * @var string object type name 
     */
    protected $typeName = 'email';

    /**
     * @var string requested object id
     */
    protected $uriIdField = 'emailId';
    
    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'emailid' ];

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
        
        if(array_key_exists('emailid', $options)) {
            $this->emailId = $options['emailid'];
        }
    }

}
