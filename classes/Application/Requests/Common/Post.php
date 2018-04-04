<?php

namespace Application\Requests\Common;

trait Post {

    /**
     * @return boolean
     * @throws \Util\Http\Exceptions\Forbidden
     * @throws \Util\Http\Exceptions\MethodNotAllowed
     */
    protected function _send() {
        if (!$this->permittedUserFunction($this->requiredPermission)) {
            throw new \Util\Http\Exceptions\Forbidden();
        }
        
        if ($this->objectId !== null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            return $this->createObject(
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\ExecutionException $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
    }
}