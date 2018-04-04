<?php

namespace Application\Requests\Person\Phones;

class Post extends \Application\Requests\Person\Phones {
    
    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    protected function _isAuthorized() {
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
        if ($this->phoneId !== null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            return $this->createPhone(
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\ExecutionException $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
    }
}