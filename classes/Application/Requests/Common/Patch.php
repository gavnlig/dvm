<?php

namespace Application\Requests\Common;

trait Patch {

    /**
     * @return null
     * @throws \Util\Http\Exceptions\Forbidden
     * @throws \Util\Http\Exceptions\MethodNotAllowed
     */
    protected function _send() {
        if (!$this->permittedUserFunction($this->requiredPermission)) {
            throw new \Util\Http\Exceptions\Forbidden();
        }
        
        if ($this->objectId === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            $this->updateObject(
                    $this->objectId,
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\Exception $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
        
        //@todo http status for PATCH
        return array();
    }
}