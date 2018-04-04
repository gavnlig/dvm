<?php

namespace Application\Requests;

abstract class Accounts extends \Application\Requests {

    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'userid' ];
    
    /**
     * @var string|null Current user id
     */
    protected $objectId = null;

    /**
     * @var string Permission identifier
     */
    protected $requiredPermission = 'accounts';
    
    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('userid', $options)) {
            if ( $options['userid'] == 'Self' ) {
                $options['userid'] = $this->getAuthenticatedUserId();
            }
            $this->objectId = $options['userid'];
        }
    }

    /**
     * @return array
     */
    protected function getObjects() {
        return $this->db->query(
            'SELECT userid, username, realname FROM users',
            array()
        );
        
    }

    /**
     * @param string $userId
     * @return array|null
     */
    protected function getObject($userId) {
        
        $result = $this->db->query(
            'SELECT userid, username, realname FROM users WHERE users.userid = :id LIMIT 1',
            array(
                'id' => $userId
            )
        );
        $object = array_shift($result);
        unset($result);
        
        $object['locations'] = array();
        $query = $this->db->query(
            'SELECT locations.id FROM users_to_locations JOIN locations ON locations.id=users_to_locations.id WHERE users_to_locations.userid = :userid', 
            array(
                'userid' => $userId
            )
        );
        
        while($item = array_shift($query)) {
            $object['locations'][] = $item['id'];
        }
        
        $object['functions'] = array();
        $query = $this->db->query(
            'SELECT functions.id FROM users_to_functions JOIN functions ON functions.id=users_to_functions.id WHERE users_to_functions.userid = :userid', 
            array(
                'userid' => $userId
            )
        );
        
        while($item = array_shift($query)) {
            $object['functions'][] = $item['id'];
        }
        
        return $object
            ? $object
            : null;
    }
    
    /**
     * @param string $userId
     */
    protected function deleteObject($userId) {
        $tid = $this->db->startTransaction();
        
        try {
            $this->db->delete(
                    'users_to_functions',
                    array('userid' => $userId)
                );
            $this->db->delete(
                    'users_to_locations',
                    array('userid' => $userId)
                );
            $this->db->delete(
                    'users',
                    array('userid' => $userId)
                );
            $this->db->commitTransaction($tid);
        }
        catch (\Exception $e) {
            $this->db->rollbackTransaction($tid);
            throw $e;
        }
        
        $this->db->commitTransaction($tid);
    }
    
    /**
     * @param array $data Data required to create new user item
     * @return array|null
     * @throws \Util\Http\Exceptions\BadRequest
     */
    protected function createObject($data) {
        $allowedNames = [ 'username', 'realname' ];
        if( count(array_diff(array_keys($data), $allowedNames)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }
        if( !array_key_exists('username', $data) ) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        //@todo: UNIQUE constraint failed ... 500?
        $rowId = $this->db->insert(
                'users',
                array( 
                    'username' => $data['username'],
                    'realname' => $data['realname'] ?: $data['username'],
                    'password' => uniqid()
                )
            );
        
        return $this->getObject($rowId);
    }
    
    /**
     * @param string $userId
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateObject($userId, $data) {

        $allowedNames = [ 'username', 'realname', 'password', 'locations', 'functions' ];
        if( count(array_diff(array_keys($data), $allowedNames)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        $tid = $this->db->startTransaction();
        
        try {
            foreach($data as $key => $value) {
                switch($key) {
                    case 'username':
                    case 'realname':
                    case 'password':
                        $this->db->update(
                                'users', 
                                array( 'userid' => $userId ), 
                                array( $key => $value)
                            );
                        break;
                    case 'locations':
                    case 'functions':
                        $table = sprintf('users_to_%s', $key);
                        $this->db->delete(
                                $table,
                                array( 'userid' => $userId )
                            );
                        foreach(\Util\ArrayX::toArray($value) as $insert) {
                            $this->db->insert(
                                    $table,
                                    array( 
                                        'userid' => $userId,
                                        'id' => $insert 
                                    )
                                );
                        }
                        break;
                }
            }
        }
        catch (\Exception $e) {
            $this->db->rollbackTransaction($tid);
            throw $e;
        }
        
        $this->db->commitTransaction($tid);
    }
}