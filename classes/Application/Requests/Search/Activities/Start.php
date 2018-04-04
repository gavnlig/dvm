<?php

namespace Application\Requests\Search\Activities;

class Start extends \Application\Requests\Search\Activities {

    /**
     * @var array List of sorted optionNames to be loaded from unhandeled path fragments
     */
    protected static $optionNames = ['startid'];
    
    /**
     * @var string
     */
    protected $startId = null;

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('startid', $options)) {
            $this->startId = $options['startid'];
        }
    }

    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        if($this->startId === null) {
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
        $comparisonString = $this->startId[0];
        if($comparisonString == '<' || $comparisonString == '>') {
            $this->startId = substr($this->startId, 1);
            $comparisonString .= '=';
        }
        else {
            $comparisonString = '=';
        }
        
        $filter = parent::_getSqlFilterColumns();
        $filter[] = sprintf(
                '( persons_activities.start %s :start )',
                $comparisonString);
        
        return $filter;
    }

    /**
     * @return array
     */
    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        $values['start'] = $this->startId;
        
        return $values;
    }

    /**
     * @return string
     */
    protected function _getSqlColumns() {
        $columns = parent::_getSqlColumns();
        $columns[] = 'persons_activities.start AS start';
        
        return $columns;
    }
}