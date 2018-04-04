<?php

namespace Application\Requests\Search\Activities;

class Birth extends \Application\Requests\Search\Activities {

    /**
     * @var array List of sorted optionNames to be loaded from unhandeled path fragments
     */
    protected static $optionNames = ['birthid'];
    
    /**
     * @var string
     */
    protected $birthId = null;

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('birthid', $options)) {
            $this->birthId = $options['birthid'];
        }
    }

    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        if($this->birthId === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }
        
        return parent::_send();
    }
    
    /**
     * @return array
     */
    protected function _getSqlFilterColumns() {
        /*
         * detect +/-/absolute
         */
        $comparisonString = $this->birthId[0];
        if($comparisonString == '<' || $comparisonString == '>') {
            $this->birthId = substr($this->birthId, 1);
            $comparisonString .= '=';
        }
        else {
            $comparisonString = '=';
        }
        
        $filter = parent::_getSqlFilterColumns();
        $filter[] = sprintf(
                '( persons.birth %s :birth )',
                $comparisonString);
        
        return $filter;
    }

    /**
     * @return array
     */
    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        $values['birth'] = $this->birthId;
        
        return $values;
    }

    /**
     * @return string
     */
    protected function _getSqlColumns() {
        $columns = parent::_getSqlColumns();
        $columns[] = 'persons.birth AS birth';
        
        return $columns;
    }
}