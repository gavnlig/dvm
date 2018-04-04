<?php

namespace Application\Requests\Person;

abstract class Addresses extends \Application\Requests\Person {

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ 'co', 'street', 'zipcode', 'city', 'country', 'hint', 'prefer' ];
    
    /**
     * @var string table name
     */
    protected $tableName = 'persons_addresses';

    /**
     * @var string object type name 
     */
    protected $typeName = 'address';

    /**
     * @var string requested object id
     */
    protected $uriIdField = 'addressId';
    
    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'addressid' ];

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
        
        if(array_key_exists('addressid', $options)) {
            $this->addressId = $options['addressid'];
        }
    }
}