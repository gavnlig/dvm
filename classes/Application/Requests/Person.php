<?php

namespace Application\Requests;

abstract class Person extends \Application\Requests {

    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'personid' ];
    
    /**
     * @var string|null Current course id
     */
    protected $personId = null;
    
    /**
     * @var string|null Current course id
     */
    protected $activityId = null;
    
    /**
     * @var string|null Current note id
     */
    protected $noteId = null;
    
    /**
     * @var string|null Current course id
     */
    protected $addressId = null;
    
    /**
     * @var string|null Current course id
     */
    protected $emailId = null;
    
    /**
     * @var string|null Current course id
     */
    protected $phoneId = null;
    
    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ ];

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('personid', $options)) {
            $this->personId = $options['personid'];
        }
    }
    
    /**
     * Return a list of courseids from any activities for the current person
     * @return array List of course ids
     */
    protected function getActiveCourses() {
        return $this->db->query(
              'SELECT courseid FROM persons_activities WHERE personid = :id AND courseid IS NOT NULL AND active = 1',
            array(
                'id' => $this->personId
            )
        );
    }
    
    /**
     * @param type $all If true, non-activated objects will be returned, too
     * @param type $recursive If true, include child objects
     * @return array|null
     */
    protected function getPerson($all = false, $recursive = true) {
        $activeFilter = $all ? '' : ' AND persons.active = 1 ';
        $result = $this->db->query(
            sprintf('SELECT "contact" AS _type, persons.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby FROM persons LEFT JOIN users c ON c.id=persons.createdby LEFT JOIN users m ON m.id=persons.modifiedby WHERE persons.id = :id %s LIMIT 1', $activeFilter), 
            array(
                'id' => $this->personId
            )
        );
        
        $object = null;
        if (count($result)==1) {
            $object = [];
            $object['_type'] = 'person';
            $object['id'] = $this->personId;
            $object['contact'] = array_shift($result);
            
            if($recursive) {
                $object['contact']['emails'] = $this->getEmails();
                $object['contact']['phones'] = $this->getPhones();
                $object['contact']['addresses'] = $this->getAddresses();
                $object['activities'] = $this->getActivities();
            }
        }
        
        return $object ? $object : null;
    }
    
    /**
     * @return array
     */
    protected function getEmail($all = false) {
        $activeFilter = $all ? '' : ' AND persons_emails.active = 1 ';
        return array_shift($this->db->query(
            sprintf('SELECT "email" AS _type, persons_emails.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby FROM persons_emails  LEFT JOIN users c ON c.id=persons_emails.createdby LEFT JOIN users m ON m.id=persons_emails.modifiedby WHERE personid = :personid AND persons_emails.id = :emailid %s', $activeFilter),
            array(
                'personid' => $this->personId,
                'emailid' => $this->emailId
            )
        ));
    }
    
    /**
     * @return array
     */
    protected function getEmails($all = false) {
        $activeFilter = $all ? '' : ' AND persons_emails.active = 1 ';
        return $this->db->query(
            sprintf('SELECT "email" AS _type, persons_emails.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby FROM persons_emails  LEFT JOIN users c ON c.id=persons_emails.createdby LEFT JOIN users m ON m.id=persons_emails.modifiedby WHERE personid = :personid %s', $activeFilter),
            array(
                'personid' => $this->personId
            )
        );
    }
    
    /**
     * @return array
     */
    protected function getPhone($all = false) {
        $activeFilter = $all ? '' : ' AND persons_phones.active = 1 ';
        return array_shift($this->db->query(
            sprintf('SELECT "phone" AS _type, persons_phones.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby  FROM persons_phones  LEFT JOIN users c ON c.id=persons_phones.createdby LEFT JOIN users m ON m.id=persons_phones.modifiedby WHERE personid = :personid AND persons_phones.id = :phoneid %s', $activeFilter),
            array(
                'personid' => $this->personId,
                'phoneid' => $this->phoneId
            )
        ));
    }
    
    /**
     * @return array
     */
    protected function getPhones($all = false) {
        $activeFilter = $all ? '' : ' AND persons_phones.active = 1 ';
        return $this->db->query(
            sprintf('SELECT "phone" AS _type, persons_phones.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby  FROM persons_phones  LEFT JOIN users c ON c.id=persons_phones.createdby LEFT JOIN users m ON m.id=persons_phones.modifiedby WHERE personid = :personid %s', $activeFilter),
            array(
                'personid' => $this->personId
            )
        );
    }
    
    /**
     * @return array
     */
    protected function getAddress($all = false) {
        $activeFilter = $all ? '' : ' AND persons_addresses.active = 1 ';
        return array_shift($this->db->query(
            sprintf('SELECT "address" AS _type, persons_addresses.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby  FROM persons_addresses  LEFT JOIN users c ON c.id=persons_addresses.createdby LEFT JOIN users m ON m.id=persons_addresses.modifiedby WHERE persons_addresses.personid = :personid AND persons_addresses.id = :addressid %s', $activeFilter),
            array(
                'personid' => $this->personId,
                'addressid' => $this->addressId
            )
        ));
    }
    
    /**
     * @return array
     */
    protected function getAddresses($all = false) {
        $activeFilter = $all ? '' : ' AND persons_addresses.active = 1 ';
        return $this->db->query(
            sprintf('SELECT "address" AS _type, persons_addresses.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby  FROM persons_addresses  LEFT JOIN users c ON c.id=persons_addresses.createdby LEFT JOIN users m ON m.id=persons_addresses.modifiedby WHERE persons_addresses.personid = :personid %s', $activeFilter),
            array(
                'personid' => $this->personId
            )
        );
    }
    
    /**
     * @return array
     */
    protected function getActivities($all = false, $recursive = true) {
        $activeFilter = $all ? '' : ' AND persons_activities.active = 1 ';
        $objects = $this->db->query(
            sprintf('SELECT type as _type, persons_activities.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby  FROM persons_activities  LEFT JOIN users c ON c.id=persons_activities.createdby LEFT JOIN users m ON m.id=persons_activities.modifiedby WHERE personid = :personid %s', $activeFilter),
            array(
                'personid' => $this->personId
            )
        );

        if($recursive) {
            foreach($objects as &$obj) {
                $this->activityId = $obj['id'];
                $obj['notes'] = $this->getNotes();
                $this->activityId = null;
            }
        }

        return $objects;
    }
    
    /**
     * @return array
     */
    protected function getActivity($all = false, $recursive = true) {
        $activeFilter = $all ? '' : ' AND persons_activities.active = 1 ';
        $objects = array_shift($this->db->query(
            sprintf('SELECT type as _type, persons_activities.*, COALESCE(c.shortname, c.name) AS createdby, COALESCE(m.shortname, m.name) AS modifiedby  FROM persons_activities  LEFT JOIN users c ON c.id=persons_activities.createdby LEFT JOIN users m ON m.id=persons_activities.modifiedby WHERE personid = :personid AND persons_activities.id = :activityid %s', $activeFilter),
            array(
                'personid' => $this->personId,
                'activityid' => $this->activityId
            )
        ));

        if($objects && $recursive) {
            $objects['notes'] = $this->getNotes();
        }

        return $objects;
    }

    /**
     * @return array
     */
    protected function getNote($all = false) {
        $activeFilter = $all ? '' : ' AND persons_activities_notes.active = 1 ';
        return array_shift($this->db->query(
            sprintf('SELECT "note" as _type, persons_activities_notes.*, COALESCE(c.shortname, c.name) AS createdby,  COALESCE(m.shortname, m.name) AS modifiedby  FROM persons_activities_notes  LEFT JOIN users c ON c.id=persons_activities_notes.createdby LEFT JOIN users m ON m.id=persons_activities_notes.modifiedby WHERE personid = :personid AND activityid = :activityid AND persons_activities_notes.id = :noteid %s', $activeFilter),
            array(
                'personid' => $this->personId,
                'activityid' => $this->activityId,
                'noteid' => $this->noteId
            )
        ));
    }

    /**
     * @return array
     */
    protected function getNotes($all = false) {
        $activeFilter = $all ? '' : ' AND persons_activities_notes.active = 1 ';
        return $this->db->query(
            sprintf('SELECT "note" as _type, persons_activities_notes.*, COALESCE(c.shortname, c.name) AS createdby,  COALESCE(m.shortname, m.name) AS modifiedby  FROM persons_activities_notes  LEFT JOIN users c ON c.id=persons_activities_notes.createdby LEFT JOIN users m ON m.id=persons_activities_notes.modifiedby WHERE personid = :personid AND activityid = :activityid %s', $activeFilter),
            array(
                'personid' => $this->personId,
                'activityid' => $this->activityId
            )
        );
    }




    
    
    
    /**
     * 
     */
    protected function deleteEmail() {
        $this->db->delete(
                'persons_emails', 
                array( 'id' => $this->emailId, 'personid' => $this->personId )
            );
    }

    /**
     * 
     */
    protected function deletePhone() {
        $this->db->delete(
                'persons_phones', 
                array( 'id' => $this->phoneId, 'personid' => $this->personId )
            );
    }

    /**
     * 
     */
    protected function deleteAddress() {
        $this->db->delete(
                'persons_addresses', 
                array( 'id' => $this->addressId, 'personid' => $this->personId )
            );
    }
    





    
    
    
    
    /**
     * @param string $table name of table
     * @param array $filter
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateTable($table, $filter, $data) {
        if( count(array_diff(array_keys($data), $this->writableFields)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data: ' . implode(', ', array_keys($data)));
        }

        $tid = $this->db->startTransaction();
        
        try {
            $this->_updateTable($table, $filter, $data);
        }
        catch (\Exception $e) {
            $this->db->rollbackTransaction($tid);
            throw $e;
        }
        
        $this->db->commitTransaction($tid);
    }
    
    /**
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    private function _updateTable($table, $filter, $data) {
        $data['modifiedby'] = $this->getAuthenticatedUserId();
        $data['modified'] = date('Y-m-d H:i:s');
        
        foreach($data as $key => $value) {
            $this->db->update(
                    $table, 
                    $filter, 
                    array( $key => $value )
                );
        }
    }


    
    
    
    
    
    
    /**
     * @param array $data Data required to create new person item
     * @return array|null
     */
    protected function createPerson($data) {
        $this->cleanupPersons();
        $this->personId = $this->db->insert(
                'persons',
                [ 'active' => 0, 'createdby' => $this->getAuthenticatedUserId(), 'created' => date('Y-m-d H:i:s') ]
            );
        
        return $this->updatePerson($data);
    }

    /**
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updatePerson($data) {
        $this->updateTable(
                'persons', 
                [ 'id' => $this->personId ],
                $data
            );
        
        if (count($data)>0) {
            $this->activatePerson();
        }
        
        return $this->getPerson(true, false);
    }
    
    /**
     * Activate a person
     */
    protected function activatePerson() {
        $this->_updateTable(
                'persons', 
                [ 'id' => $this->personId ],
                [ 'active' => 1 ]
            );
    }



    
    
    






    
    /**
     * @param array $data Data required to create new person item
     * @return array|null
     */
    protected function createAddress($data) {
        $this->cleanupAddresses();
        $this->addressId = $this->db->insert(
                'persons_addresses',
                [ 'active' => 0, 'personid' => $this->personId, 'createdby' => $this->getAuthenticatedUserId(), 'created' => date('Y-m-d H:i:s') ]
            );
        
        return $this->updateAddress($data);
    }

    /**
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateAddress($data) {
        $this->updateTable(
                'persons_addresses', 
                [ 'id' => $this->addressId, 'personid' => $this->personId ],
                $data
            );
        
        if (count($data)>0) {
            $this->activateAddress();
            $this->activatePerson();
        }
        
        return $this->getAddress(true);
    }
    
    /**
     * Activate a person
     */
    protected function activateAddress() {
        $this->_updateTable(
                'persons_addresses', 
                [ 'id' => $this->addressId, 'personid' => $this->personId ],
                [ 'active' => 1 ]
            );
    }




    
    
    
    
    /**
     * @param array $data Data required to create new person item
     * @return array|null
     */
    protected function createEmail($data) {
        $this->cleanupEmails();
        $this->emailId = $this->db->insert(
                'persons_emails',
                [ 'active' => 0, 'personid' => $this->personId, 'createdby' => $this->getAuthenticatedUserId(), 'created' => date('Y-m-d H:i:s') ]
            );
        
        return $this->updateEmail($data);
    }

    /**
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateEmail($data) {
        $this->updateTable(
                'persons_emails', 
                [ 'id' => $this->emailId, 'personid' => $this->personId ],
                $data
            );
        
        if (count($data)>0) {
            $this->activateEmail();
            $this->activatePerson();
        }
        
        return $this->getEmail(true);
    }
    
    /**
     * Activate a person
     */
    protected function activateEmail() {
        $this->_updateTable(
                'persons_emails', 
                [ 'id' => $this->emailId, 'personid' => $this->personId ],
                [ 'active' => 1 ]
            );
    }
    
    
    
    
    
    

    
    
    
    
    /**
     * @param array $data Data required to create new person item
     * @return array|null
     */
    protected function createPhone($data) {
        $this->cleanupPhones();
        $this->phoneId = $this->db->insert(
                'persons_phones',
                [ 'active' => 0, 'personid' => $this->personId, 'createdby' => $this->getAuthenticatedUserId(), 'created' => date('Y-m-d H:i:s') ]
            );
        
        return $this->updatePhone($data);
    }

    /**
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updatePhone($data) {
        $this->updateTable(
                'persons_phones', 
                [ 'id' => $this->phoneId, 'personid' => $this->personId ],
                $data
            );
        
        if (count($data)>0) {
            $this->activatePhone();
            $this->activatePerson();
        }
        
        return $this->getPhone(true);
    }
    
    /**
     * Activate a person
     */
    protected function activatePhone() {
        $this->_updateTable(
                'persons_phones', 
                [ 'id' => $this->phoneId, 'personid' => $this->personId ],
                [ 'active' => 1 ]
            );
    }
    
    
    
    
    
    
    
    
    
    
    

    
    
    
    
    /**
     * @param array $data Data required to create new person item
     * @return array|null
     */
    protected function createActivity($data) {
        $this->cleanupActivities();
        if(!array_key_exists('courseid', $data) || $data['courseid'] === '') {
            throw new \Util\Http\Exceptions\BadRequest('missing data: courseid');
        }

        if(!array_key_exists('type', $data)) {
            throw new \Util\Http\Exceptions\BadRequest('missing data: type');
        }
        
        if(!in_array($data['type'], ['Application', 'Information', 'Training', 'Study'] )) {
            throw new \Util\Http\Exceptions\BadRequest('invalid data: type');
        }
        
        $this->activityId = $this->db->insert(
                'persons_activities',
                [ 'active' => 0, 'personid' => $this->personId, 'type' => $data['type'], 'courseid' => $data[courseid], 'createdby' => $this->getAuthenticatedUserId(), 'created' => date('Y-m-d H:i:s') ]
            );
        
        return $this->updateActivity($data);
    }

    /**
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateActivity($data) {
        if(array_key_exists('courseid', $data) && $data['courseid'] === '') {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data: courseid');
        }
        
        $this->updateTable(
                'persons_activities', 
                [ 'id' => $this->activityId, 'personid' => $this->personId ],
                $data
            );
        
        if (count($data)>0) {
            $this->activateActivity();
            $this->activatePerson();
        }
        
        return $this->getActivity(true, false);
    }
    
    /**
     * Activate a person
     */
    protected function activateActivity() {
        $this->_updateTable(
                'persons_activities', 
                [ 'id' => $this->activityId, 'personid' => $this->personId ],
                [ 'active' => 1 ]
            );
    }
    
    
    
    
    
    
    
    
    
    /**
     * @param array $data Data required to create new person item
     * @return array|null
     */
    protected function createNote($data) {
        $this->cleanupNotes();
        if(!array_key_exists('typeid', $data) || $data['typeid'] === '') {
            throw new \Util\Http\Exceptions\BadRequest('missing data: typeid');
        }
        
        $this->noteId = $this->db->insert(
                'persons_activities_notes',
                [ 'active' => 0, 'activityid' => $this->activityId, 'personid' => $this->personId, 'typeid' => $data['typeid'], 'createdby' => $this->getAuthenticatedUserId(), 'created' => date('Y-m-d H:i:s') ]
            );
        
        return $this->updateNote($data);
    }

    /**
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateNote($data) {
        $this->updateTable(
                'persons_activities_notes', 
                [ 'id' => $this->noteId, 'activityid' => $this->activityId, 'personid' => $this->personId ],
                $data
            );
        
        if (count($data)>0) {
            $this->activateNote();
            $this->activateActivity();
            $this->activatePerson();
        }
        
        return $this->getNote(true);
    }
    
    /**
     * Activate a person
     */
    protected function activateNote() {
        $this->_updateTable(
                'persons_activities_notes', 
                [ 'id' => $this->noteId, 'activityid' => $this->activityId, 'personid' => $this->personId ],
                [ 'active' => 1 ]
            );
    }
    
    
    
    
    
    
    protected function cleanupNotes() {
        $this->db->delete(
                'persons_activities_notes', 
                [ 'activityid' => $this->activityId, 'personid' => $this->personId, 'active' => 0, 'createdby' => $this->getAuthenticatedUserId() ]
            );
    }
    
    protected function cleanupActivities() {
        $this->db->delete(
                'persons_activities', 
                [ 'personid' => $this->personId, 'active' => 0, 'createdby' => $this->getAuthenticatedUserId() ]
            );
    }
    
    protected function cleanupPhones() {
        $this->db->delete(
                'persons_phones', 
                [ 'personid' => $this->personId, 'active' => 0, 'createdby' => $this->getAuthenticatedUserId() ]
            );
    }
    
    protected function cleanupEmails() {
        $this->db->delete(
                'persons_emails', 
                [ 'personid' => $this->personId, 'active' => 0, 'createdby' => $this->getAuthenticatedUserId() ]
            );
    }
    
    protected function cleanupAddresses() {
        $this->db->delete(
                'persons_addresses', 
                [ 'personid' => $this->personId, 'active' => 0, 'createdby' => $this->getAuthenticatedUserId() ]
            );
    }
    
    protected function cleanupPersons() {
        $this->db->delete(
                'persons', 
                [ 'active' => 0, 'createdby' => $this->getAuthenticatedUserId() ]
            );
    }
}