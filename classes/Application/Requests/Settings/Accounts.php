<?php

namespace Application\Requests\Settings;

abstract class Accounts extends \Application\Requests\Settings {

    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'userid' ];
    
    /**
     * @var string|null Current user id
     */
    protected $accountId = null;

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = ['name', 'realname', 'shortname', 'password', 'courses', 'functions'];
    
    /**
     * @var array List of field names allowed to read
     */
    protected $readableFields = ['name', 'realname', 'shortname', /* 'password', */ 'courses', 'functions'];
    
    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        /*
         * accounts/self is called during login,
         * so let's setup the database now
         */
        if(@$options['userid'] == 'Self' ) {
            $options['setup'] = true;
        }

        /*
         * go on with common behaivour
         */
        parent::__construct($request, $options);
        
        if(array_key_exists('userid', $options)) {
            if ( $options['userid'] == 'Self' ) {
                $options['userid'] = $this->getAuthenticatedUserId();
            }
            $this->accountId = $options['userid'];
        }
    }

    /**
     * @return array
     */
    protected function getAccounts() {
        return $this->db->query(
            'SELECT id, name, realname, shortname FROM users WHERE users.active = 1',
            array()
        );
        
    }

    /**
     * @param string $userId
     * @return array|null
     */
    protected function getAccount($userId) {
        
        $result = $this->db->query(
            'SELECT id, name, realname FROM users WHERE users.active = 1 AND users.id = :id LIMIT 1',
            array(
                'id' => $userId
            )
        );
        $object = array_shift($result);
        unset($result);
        
        $object['courses'] = array();
        $query = $this->db->query(
            'SELECT courses.id AS id FROM users_to_courses JOIN courses ON courses.id=users_to_courses.courseid WHERE users_to_courses.userid = :userid', 
            array(
                'userid' => $userId
            )
        );
        
        while($item = array_shift($query)) {
            $object['courses'][] = $item['id'];
        }
        
        $object['functions'] = array();
        $query = $this->db->query(
            'SELECT functions.id AS id FROM users_to_functions JOIN functions ON functions.id=users_to_functions.functionid WHERE users_to_functions.userid = :userid', 
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
    protected function deleteAccount($userId) {
        $tid = $this->db->startTransaction();
        
        try {
            $this->db->delete(
                    'users_to_functions',
                    array('userid' => $userId)
                );
            $this->db->delete(
                    'users_to_courses',
                    array('userid' => $userId)
                );
            $this->db->delete(
                    'users',
                    array('id' => $userId)
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
    protected function createAccount($data) {
        if( count(array_diff(array_keys($data), $this->writableFields)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        //@todo: UNIQUE constraint failed ... 500?
        $rowId = $this->db->insert(
                'users',
                [ 'active' => 0, 'password' => uniqid() ]
            );
        
        return [ 'id' => $rowId ];
    }
    
    /**
     * @param string $userId
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateAccount($userId, $data) {

        if( count(array_diff(array_keys($data), $this->writableFields)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        $tid = $this->db->startTransaction();
        
        try {
            /*
             * active this database ibject
             */
            $this->db->update(
                    'users', 
                    array( 'id' => $userId ), 
                    array( 'active' => 1 )
                );
            /*
             * set transmitted fields, one-by-one
             */
            foreach($data as $key => $value) {
                switch($key) {
                    case 'password':
                        /*
                         * crypt the password
                         * silently reset empty passwords 
                         */
                        $value = password_hash(
                                empty($value) ? uniqid() : $value,
                                PASSWORD_DEFAULT);
                    case 'name':
                    case 'realname':
                        $this->db->update(
                                'users', 
                                array( 'id' => $userId ), 
                                array( $key => $value)
                            );
                        break;
                    case 'courses':
                        $this->db->delete(
                                'users_to_courses',
                                array( 'userid' => $userId )
                            );
                        foreach(\Util\ArrayX::toArray($value) as $insert) {
                            $this->db->insert(
                                    'users_to_courses',
                                    array( 
                                        'userid' => $userId,
                                        'courseid' => $insert 
                                    )
                                );
                        }
                        break;
                    case 'functions':
                        $this->db->delete(
                                'users_to_functions',
                                array( 'userid' => $userId )
                            );
                        foreach(\Util\ArrayX::toArray($value) as $insert) {
                            $this->db->insert(
                                    'users_to_functions',
                                    array( 
                                        'userid' => $userId,
                                        'functionid' => $insert 
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