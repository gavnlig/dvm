<?php

namespace Application\Requests\Search\Any;

class Get extends \Application\Requests\Search\Any {
    
    /**
     * @return string
     * @throws \Util\Http\Exceptions\NotFound
     */    
    protected function _send() {
        if($this->searchTerm === null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }
        
        $response = $this->getObjects();
        
        if ($response === null) {
            throw new \Util\Http\Exceptions\NotFound();
        }
        
        return $response;
    }
}