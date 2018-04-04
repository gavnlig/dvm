<?php

namespace Application\Requests\Search\Activities\Notes;

abstract class Remind extends \Application\Requests\Search\Activities\Notes {
    /**
     * @return array
     */
    protected function _getSqlFilterColumns() {
        $filter = parent::_getSqlFilterColumns();
        $filter[] = '( persons_activities_notes.remind <= CURRENT_TIMESTAMP AND NOT ( persons_activities_notes.remind = "" OR persons_activities_notes.remind is null ))';
        
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
        $columns[] = 'persons_activities_notes.remind AS reminder';
        
        return $columns;
    }    
}