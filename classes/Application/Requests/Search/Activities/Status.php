<?php

namespace Application\Requests\Search\Activities;

class Status extends \Application\Requests\Search\Activities {

    /**
     * @var array List of sorted optionNames to be loaded from unhandeled path fragments
     */
    protected static $optionNames = ['statusid'];
    
    /**
     * @var string
     */
    protected $statusId = null;

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('statusid', $options)) {
            $this->statusId = $options['statusid'];
        }
    }

    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        if($this->statusId === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }
        
        return parent::_send();
    }
    /**
     * @return array
     */
    protected function _getSqlFilterColumns() {
        $filter = parent::_getSqlFilterColumns();
        $filter[] = '( persons_activities.statusid = :status )';
        
        return $filter;
    }

    /**
     * @return array
     */
    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        $values['status'] = $this->statusId;
        
        return $values;
    }

    /**
     * @return string
     */
    protected function _getSqlColumns() {
        $columns = parent::_getSqlColumns();
        
        return $columns;
    }
}