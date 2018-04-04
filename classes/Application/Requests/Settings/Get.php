<?php

namespace Application\Requests\Settings;

class Get extends \Application\Requests\Settings {
    
    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    protected function _isAuthorized() {
        /*
         * Grant READ access to all authenticated users.
         */
        return $this->getAuthenticatedUserId() !== null;
    }
    
    protected function _send() {
        return $this->getSettings();
    }
}