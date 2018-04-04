<?php

namespace Application\Requests\Person\Activities;

class Patch extends \Application\Requests\Person\Activities {

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ 'locationid', 'start', 'end', 'statusid' ];

    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    protected function _isAuthorized() {
        /*
         * personId is required
         */
        if ($this->personId === null) {
            return false;
        }
        
        /*
         * Grant access if the courseId of the requested person
         * overlapps with the courses of the current account
         * - OR -
         * if the requested person does not have any course-ids yet!
         */
        $courses = $this->getActiveCourses();
        
        if (count($courses) === 0 ) {
            return true;
        }
        
        $courses = array_shift($courses);
        $same = array_intersect($this->getAuthorizedCourses(), $courses);
        return (count($same) > 0 );
    }
    
    /**
     * Returns the http response as string.
     * @return string|boolean|null
     */
    protected function _send() {
        if ($this->activityId === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            $this->updateActivity(
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\Exception $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
        
        //@todo http status for PATCH
        return array();
    }
}