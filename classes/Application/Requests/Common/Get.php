<?php

namespace Application\Requests\Common;

trait Get {

    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        $response = $this->objectId
            ? $this->getObject($this->objectId)
            : $this->getObjects();
        
        if ($response === null) {
            throw new \Util\Http\Exceptions\NotFound();
        }
        
        return $response;
    }
}