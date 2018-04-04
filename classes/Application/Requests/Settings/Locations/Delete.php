<?php

namespace Application\Requests\Settings\Locations;

class Delete extends \Application\Requests\Settings\Locations {

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
        return $this->_sendDelete('locationId', 'deleteLocation');
    }
}