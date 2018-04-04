<?php

namespace Application\Requests;

abstract class Search extends \Application\Requests {
    
    /**
     * @var array List of sorted optionNames to be loaded from unhandeled path fragments
     */
    protected static $optionNames = ['maxquery', 'offset'];
    
    /**
     * @var int Maximal serach results
     */
    protected $maxQuery = null;
    
    /**
     * @var int Start query at this index
     */
    protected $offset = 0;
    
    /**
     * @var array List of columns to be queried
     */
    protected $filterColumns = [];

    /**
     * @var string Table or joined tables to select from
     */
    protected $queryTable = '';

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('offset', $options)) {
            $this->offset = intval($options['offset']);
        }
        
        if(array_key_exists('maxquery', $options)) {
            $this->maxQuery = intval($options['maxquery']);
        }
    }

    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    protected function _isAuthorized() {
        /*
         * Grant SEARCH access with limited properties 
         * to all authenticated users.
         */
        return $this->getAuthenticatedUserId() !== null;
    }

    /**
     * @return string
     */
    protected function _getSqlColumns() {
        return [ 
            'persons.id AS id',
            'persons.givenname AS givenname',
            'COALESCE(persons.surename, persons.birthname) AS surename'
        ];
    }
    
    /**
     * @return string Table or joined tables to select from
     */
    protected function _getSqlFrom() {
        return [ 'persons' ];
    }
    
    protected function _getSqlFilterColumns() {
        return [];
    }
    
    protected function _getSqlFilterValues() {
        return [];
    }

    protected function _send() {
        $response = $this->getObjects();
        
        if ($response === null) {
            throw new \Util\Http\Exceptions\NotFound();
        }
        
        return $response;
    }
    
    /**
     * @return array Serach result
     */
    protected function getObjects() {
        $result = [];
        $result['_type'] = 'results';

        $limit = '';
        if($this->maxQuery !== null) {
            $limit = sprintf(' LIMIT %d OFFSET %d ', 
                    $this->maxQuery + 1,
                    $this->offset);
        }
        $sql = sprintf(
                'SELECT "result" as "_type", %s FROM %s WHERE %s ORDER BY persons.id %s;',
                implode(',', $this->_getSqlColumns()),
                implode(' LEFT JOIN ', $this->_getSqlFrom()),
                implode(' AND ', $this->_getSqlFilterColumns()),
                $limit);
        error_log($sql);
        error_log(print_r($this->_getSqlFilterValues(),true));
        $result['results'] = $this->db->query($sql, $this->_getSqlFilterValues());

        if( $this->maxQuery !== null && count($result['results']) > $this->maxQuery ) {
            $result['next'] = $this->offset + $this->maxQuery + 1;
            array_pop($result['results']);
        }
        
        return $result;
    }

}