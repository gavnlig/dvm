<?php

namespace Application\Requests\Person;

class Post extends \Application\Requests\Person {

    /**
     * @var array List of field names allowed to modify
     */
    protected $writableFields = [ 'givenname', 'surename', 'birthname', 'gender', 'birth', 'nationality', 'birthplace' ];

    /**
     * Returns true, if the request is authorized.
     * @return boolean
     */
    protected function _isAuthorized() {
        /*
         * Grant access if current account has the CREATE right.
         */
        return $this->isPermittedUserFunction(\Application\Permissions::CREATE);
    }
    
    protected function _send() {
        if ($this->personId !== null) {
            throw new \Util\Http\Exceptions\MethodNotAllowed();
        }

        try {
            return $this->createPerson(
                    $this->getJsonPostData()
                );
        }
        catch (\Util\Db\ExecutionException $e) {
            throw new \Util\Http\Exceptions\Forbidden('Database denied request', null, $e);
        }
    }
}