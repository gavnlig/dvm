<?php

namespace Application\Requests;

abstract class Settings extends \Application\Requests {
    
    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
    }
    
    public function getSettings() {
        $return = [];
        
        $return['courses'] = $this->db->query(
            'SELECT * FROM courses WHERE active = 1',
            array()
        );
        $return['locations'] = $this->db->query(
            'SELECT * FROM locations WHERE active = 1',
            array()
        );
        $return['functions'] = $this->db->query(
            'SELECT * FROM functions WHERE active = 1',
            array()
        );
        $return['status'] = $this->db->query(
            'SELECT * FROM status WHERE active = 1',
            array()
        );
        $return['notetypes'] = $this->db->query(
            'SELECT *, type AS name FROM notetypes WHERE active = 1',
            array()
        );
        
        return $return;
    }
}