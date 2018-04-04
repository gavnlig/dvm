<?php

namespace Application\Requests\Settings\Accounts;

class Get extends \Application\Requests\Settings\Accounts {
    
    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    protected function _isAuthorized() {
        /*
         * Grant READ access to the own account object.
         */
        if ($this->accountId === $this->getAuthenticatedUserId()) {
            return true;
        }
        
        /*
         * Grant access if current account has the SETTINGS right.
         */
        return $this->isPermittedUserFunction(\Application\Permissions::SETTINGS);
    }
    
    protected function _send() {
        return $this->_sendGet('accountId', 'getAccount', 'getAccounts');
    }
}