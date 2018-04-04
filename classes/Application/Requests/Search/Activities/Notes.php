<?php

namespace Application\Requests\Search\Activities;

class Notes extends \Application\Requests\Search\Activities {

    /**
     * @var array List of sorted optionNames to be loaded from unhandeled path fragments
     */
    protected static $optionNames = ['notetypeid'];
    
    /**
     * @var string
     */
    protected $notetypeId = null;

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('notetypeid', $options)) {
            $this->notetypeId = $options['notetypeid'];
        }
    }

    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        /*
        if($this->noteId === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }
        */
        
        return parent::_send();
    }

    protected function _getSqlFilterColumns() {
        $filter = parent::_getSqlFilterColumns();
        if($this->notetypeId !== null) {
            $filter[] = '( persons_activities_notes.typeid = :notetypeid )';
        }
        
        return $filter;
    }

    /**
     * @return array
     */
    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        if($this->notetypeId !== null) {
            $values['notetypeid'] = $this->notetypeId;
        }
        
        return $values;
    }
    
    /**
     * @return string
     */
    protected function _getSqlColumns() {
        $columns = parent::_getSqlColumns();
        $columns[] = 'persons_activities_notes.typeid AS noteid';
        
        return $columns;
    }
    
    /**
     * @return string Table or joined tables to select from
     */
    protected function _getSqlFrom() {
        $joins = parent::_getSqlFrom();
        $joins[] = 'persons_activities_notes ON persons_activities_notes.activityid=persons_activities.id';
        
        return $joins;
    }
}