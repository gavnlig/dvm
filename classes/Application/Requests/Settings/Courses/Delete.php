<?php

namespace Application\Requests\Settings\Courses;

class Delete extends \Application\Requests\Settings\Courses {

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
        return $this->_sendDelete('courseId', 'deleteCourse');
    }
}