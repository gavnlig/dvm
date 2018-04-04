<?php

namespace Application\Requests\Settings\Locations;

class Get extends \Application\Requests\Settings\Locations {
    
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
        return $this->_sendGet('locationId', 'getLocation', 'getLocations');
    }
}