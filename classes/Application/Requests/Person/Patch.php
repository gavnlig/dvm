<?php

namespace Application\Requests\Person;

class Patch extends \Application\Requests\Person {

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ 'givenname', 'surename', 'birthname', 'gender', 'birth', 'nationality', 'birthplace' ];

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
    
    protected function _send() {
        if ($this->personId === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            return $this->updatePerson(
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\Exception $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
    }
}