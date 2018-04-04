<?php

namespace Application\Requests\Settings;

abstract class Functions extends \Application\Requests\Settings {

    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'functionid' ];
    
    /**
     * @var string|null Current course id
     */
    protected $functionId = null;

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('functionid', $options)) {
            $this->functionId = $options['functionid'];
        }
    }

    /**
     * @return array
     */
    protected function getFunctions() {
        return $this->db->query(
            'SELECT id, name FROM functions WHERE active = 1',
            array()
        );
    }

    /**
     * @param string $functionId
     * @return array|null
     */
    protected function getFunction($functionId) {
        $result = $this->db->query(
            'SELECT id, name FROM functions WHERE active = 1 AND id = :id', 
            array(
                'id' => $functionId
            )
        );
        return array_shift($result);
    }
}