<?php

namespace Application\Requests\Search\Activities;

class Missingnote extends \Application\Requests\Search\Activities {

    /**
     * @var array List of sorted optionNames to be loaded from unhandeled path fragments
     */
    protected static $optionNames = ['noteid'];
    
    /**
     * @var string
     */
    protected $noteId = null;

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('noteid', $options)) {
            $this->noteId = $options['noteid'];
        }
    }

    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        if($this->noteId === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }
        
        return parent::_send();
    }

    protected function _getSqlFilterColumns() {
        $filter = parent::_getSqlFilterColumns();
        $filter[] = '( NOT EXISTS (SELECT 1 FROM persons_activities_notes sub LEFT JOIN persons_activities par ON sub.activityid=par.id WHERE sub.typeid = :noteid AND sub.personid = persons.id AND par.type = :partype AND sub.activityid=persons_activities.id) )';
        
        return $filter;
    }

    /**
     * @return array
     */
    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        $values['noteid'] = $this->noteId;
        $values['partype'] = $this->activityId;
        
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