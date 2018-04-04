<?php

namespace Application\Requests\Accounts;

class Get extends \Application\Requests\Accounts {

    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        $access = $this->permittedUserFunction($this->requiredPermission);
        $ownId = $this->getAuthenticatedUserId();
        
        if($this->objectId !== $ownId && !$access) {
            throw new \Util\Http\Exceptions\Forbidden();
        }
        
        $response = $this->objectId
            ? $this->getObject($this->objectId)
            : $this->getObjects();
        
        if ($response === null) {
            throw new \Util\Http\Exceptions\NotFound();
        }
        
        return $response;
    }
}