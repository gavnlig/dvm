<?php

namespace Application\Requests\Settings;

abstract class Courses extends \Application\Requests\Settings {

    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'courseid' ];
    
    /**
     * @var string|null Current course id
     */
    protected $courseId = null;

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = ['name', 'shortname', 'type'];
    
    /**
     * @var array List of field names allowed to read
     */
    protected $readableFields = ['name', 'shortname', 'type'];
    
    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('courseid', $options)) {
            $this->courseId = $options['courseid'];
        }
    }

    /**
     * @return array
     */
    protected function getCourses() {
        return $this->db->query(
            'SELECT id, name FROM courses WHERE active = 1',
            array()
        );
    }

    /**
     * @param string $courseId
     * @return array|null
     */
    protected function getCourse($courseId) {
        
        $result = $this->db->query(
            'SELECT id, name, shortname, type FROM courses WHERE active = 1 AND id = :id', 
            array(
                'id' => $courseId
            )
        );
        $object = array_shift($result);
        unset($result);
        
        return $object
            ? array('id' => $object['id'],
                    'name' => $object['name'])
            : null;
    }
    
    /**
     * @param string $courseId
     */
    protected function deleteCourse($courseId) {
        $this->db->delete(
                'courses',
                array('id' => $courseId)
            );
    }
    
    /**
     * @param array $data Data required to create new course item
     * @return array|null
     * @throws \Util\Http\Exceptions\BadRequest
     */
    protected function createCourse($data) {
        if( count(array_diff(array_keys($data), $this->writableFields)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        //@todo: UNIQUE constraint failed ... 500?
        $rowId = $this->db->insert(
                'courses',
                [ 'active' => 0 ]
            );
        
        return [ 'id' => $rowId ];
    }
    
    /**
     * @param string $courseId
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateCourse($courseId, $data) {

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
            if(array_key_exists('name', $data)) {
                $this->db->update(
                        'courses', 
                        array( 'id' => $courseId ), 
                        array( 'name' => $data['name'])
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