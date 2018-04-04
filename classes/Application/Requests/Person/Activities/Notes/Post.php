<?php

namespace Application\Requests\Person\Activities\Notes;

class Post extends \Application\Requests\Person\Activities\Notes {

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ 'typeid', 'remind', 'info' ];
    
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
    
    /**
     * Returns the http response as string.
     * @return string|boolean|null
     */
    protected function _send() {
        if ($this->noteId !== null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            return $this->createNote(
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\ExecutionException $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
    }
}