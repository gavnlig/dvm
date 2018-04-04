<?php

namespace Application\Requests\Settings;

abstract class Locations extends \Application\Requests\Settings {

    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'locationid' ];
    
    /**
     * @var string|null Current location id
     */
    protected $locationId = null;

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ 'name', 'shortname', 'street', 'zipcode', 'city' ];
    
    /**
     * @var array List of field names allowed to read
     */
    protected $readableFields = [ 'name', 'shortname', 'street', 'zipcode', 'city' ];

    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('locationid', $options)) {
            $this->locationId = $options['locationid'];
        }
    }

    /**
     * @return array
     */
    protected function getLocations() {
        return $this->db->query(
            'SELECT id, shortname FROM locations WHERE active = 1',
            array()
        );
    }

    /**
     * @param string $locationId
     * @return array|null
     */
    protected function getLocation($locationId) {
        $result = $this->db->query(
            'SELECT id, name, shortname, street, zipcode, city FROM locations WHERE id = :id LIMIT 1', 
            array(
                'id' => $locationId
            )
        );
        return array_shift($result);
    }
    
    /**
     * @param string $locationId
     */
    protected function deleteLocation($locationId) {
        $this->db->delete(
                'locations',
                array('id' => $locationId)
            );
    }
    
    /**
     * @param array $data Data required to create new location item
     * @return array|null
     * @throws \Util\Http\Exceptions\BadRequest
     */
    protected function createLocation($data) {
        if( count(array_diff(array_keys($data), $this->writableFields)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        //@todo: UNIQUE constraint failed ... 500?
        $rowId = $this->db->insert(
                'locations',
                [ 'active' => 0 ]
            );
        
        return [ 'id' => $rowId ];
    }
    
    /**
     * @param string $locationId
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateLocation($locationId, $data) {

        if( count(array_diff(array_keys($data), $this->writableFields)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        $tid = $this->db->startTransaction();
        
        try {
            /*
             * active this database ibject
             */
            $this->db->update(
                    'courses', 
                    array( 'id' => $courseId ), 
                    array( 'active' => 1 )
                );
            /*
             * set transmitted fields, one-by-one
             */
            foreach($data as $key => $value) {
                $this->db->update(
                        'locations', 
                        array( 'id' => $locationId ), 
                        array( $key => $value )
                    );
            }
            
        }
        catch (\Exception $e) {
            $this->db->rollbackTransaction($tid);
            throw $e;
        }
        
        $this->db->commitTransaction($tid);
    }
}