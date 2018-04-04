<?php

namespace Application\Requests\Search;

abstract class Activities extends \Application\Requests\Search {

    /**
     * @var array List of sorted optionNames to be loaded from unhandeled path fragments
     */
    protected static $optionNames = ['activityid','courseid','locationid'];

    protected $activityId = null;
    protected $courseId = null;
    protected $locationId = null;
    
    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        if($this->activityId === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }
        
        return parent::_send();
    }
    
    /**
     * @return array
     */
    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        
        if($this->activityId !== null) {
            $values['acttype'] = $this->activityId;
        }
        
        if($this->courseId !== null) {
            $values['courseid'] = $this->courseId;
        }
        
        if($this->locationId !== null) {
            $values['locationid'] = $this->locationId;
        }
        
        return $values;
    }

    protected function _getSqlFilterColumns() {
        $filter = parent::_getSqlFilterColumns();
        
        if($this->activityId !== null) {
            $filter[] = '( persons_activities.type = :acttype )';
        }
        
        if($this->courseId !== null) {
            $filter[] = '( persons_activities.courseid = :courseid )';
        }
        
        if($this->locationId !== null) {
            $filter[] = '( persons_activities.locationid = :locationid )';
        }
        
        return $filter;
    }
    
    /**
     * @return string
     */
    protected function _getSqlColumns() {
        $columns = parent::_getSqlColumns();
        $columns[] = 'persons_activities.courseid AS courseid';
        $columns[] = 'persons_activities.locationid AS locationid';
        
        return $columns;
    }

    /**
     * @return string Table or joined tables to select from
     */
    protected function _getSqlFrom() {
        $joins = parent::_getSqlFrom();
        $joins[] = 'persons_activities ON persons_activities.personid=persons.id';
        
        return $joins;
    }

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('activityid', $options)) {
            $this->activityId = $options['activityid'];
        }
        
        if(array_key_exists('courseid', $options)) {
            $this->courseId = intval($options['courseid']);
        }
        
        if(array_key_exists('locationid', $options)) {
            $this->locationId = intval($options['locationid']);
        }
    }
}