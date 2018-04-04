<?php

namespace Application\Requests\Search\Activities;

class Current extends \Application\Requests\Search\Activities {

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
    }

    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        return parent::_send();
    }

    protected function _getSqlFilterColumns() {
        $filter = parent::_getSqlFilterColumns();
        $filter[] = '( persons_activities.start <= CURRENT_TIMESTAMP AND ( persons_activities.end >= CURRENT_TIMESTAMP OR persons_activities.end = "" OR persons_activities.end is null ) )';
        
        return $filter;
    }

    /**
     * @return array
     */
    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        
        return $values;
    }
    
    /**
     * @return string
     */
    protected function _getSqlColumns() {
        $columns = parent::_getSqlColumns();
        
        return $columns;
    }
    
    /**
     * @return string Table or joined tables to select from
     */
    protected function _getSqlFrom() {
        $joins = parent::_getSqlFrom();
        
        return $joins;
    }
}