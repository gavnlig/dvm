<?php

namespace Application\Requests\Search;

abstract class Any extends \Application\Requests\Search {

    /**
     * @var array List of sorted optionNames to be loaded from unhandeled path fragments
     */
    protected static $optionNames = ['searchstring'];
    
    /**
     * @var string|null Value to search for
     */
    protected $searchTerm = null;

    /**
     * @var array List of columns to be queried
     */
    protected $filterColumns = [ 
        'persons.givenname', 
        'persons.surename', 
        'persons.birthname', 
        'persons.birthplace' 
    ];

    /**
     * @return string
     */
    protected function _getSqlColumns() {
        $columns = parent::_getSqlColumns();
        $columns[] = 'persons_addresses.city AS city';
        
        return $columns;
    }
    
    /**
     * @return string Table or joined tables to select from
     */
    protected function _getSqlFrom() {
        $joins = parent::_getSqlFrom();
        $joins[] = 'persons_addresses ON persons_addresses.personid=persons.id';
        
        return $joins;
    }

    protected function _getSqlFilterValues() {
        $values = parent::_getSqlFilterValues();
        
        for($i=1; $i<count($this->filterColumns); $i++) {
            $values['term'. $i] = '%'.$this->searchTerm.'%';
        }
        
        return $values;
    }

    protected function _getSqlFilterColumns() {
        $filter = parent::_getSqlFilterColumns();
        $filter[] = '( persons.givenname LIKE :term1 OR persons.surename LIKE :term2 OR persons.birthname LIKE :term3 OR persons.birthplace LIKE :term4 )';
        
        return $filter;
    }
    
    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('searchstring', $options)) {
            $this->searchTerm = $options['searchstring'];
        }
    }
    
}