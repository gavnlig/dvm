<?php

namespace Application\Requests\Settings\Courses;

class Post extends \Application\Requests\Settings\Courses {

    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    protected function _isAuthorized() {
        /*
         * Grant access if current account has the SETTINGS right.
         */
        return $this->isPermittedUserFunction(\Application\Permissions::SETTINGS);
    }
    
    protected function _send() {
        return $this->_sendPost('courseId', 'createCourse');
    }
}