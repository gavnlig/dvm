<?php

namespace Application\Requests\Settings\Accounts;

class Patch extends \Application\Requests\Settings\Accounts {

    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    protected function _isAuthorized() {
        /*
         * Grant WRITE access to the PASSWORD field for the own account object.
         */
        if ($this->accountId === $this->getAuthenticatedUserId()) {
            $this->writableFields = [ 'password' ];
            return true;
        }
        
        /*
         * Grant access if current account has the SETTINGS right.
         */
        return $this->isPermittedUserFunction(\Application\Permissions::SETTINGS);
    }
    
    protected function _send() {
        return $this->_sendPatch('accountId', 'updateAccount');
    }
}