<?php

namespace Application\Requests\Search\Activities\Notes;

abstract class Open extends \Application\Requests\Search\Activities\Notes {
    /**
     * @return array
     */
    protected function _getSqlFilterColumns() {
        $filter = parent::_getSqlFilterColumns();
        $filter[] = '(NOT ( persons_activities_notes.remind = "" OR persons_activities_notes.remind IS null ))';
        
        return $filter;
    }

    /**
     * @return array
     */
    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        
        return $values;
    }
}